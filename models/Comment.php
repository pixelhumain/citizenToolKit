<?php 
class Comment {

	const COLLECTION = "comments";

	//Options of the comment
	const COMMENT_ON_TREE = "tree";
	const COMMENT_ANONYMOUS = "anonymous";
	const ONE_COMMENT_ONLY = "oneCommentOnly";

	//Comment status
	const STATUS_POSTED 		 = "posted";
	const STATUS_DECLARED_ABUSED = "declaredAbused";
	const STATUS_DELETED 		 = "deleted";
	const STATUS_ACCEPTED 		 = "accepted";
	
	//From Post/Form name to database field name
	private static $dataBinding = array(
	    "content" => array("name" => "text", "rules" => array("required")),
	    "author" => array("name" => "author"),
	    "tags" => array("name" => "tags"),
	    "contextId" => array("name" => "contextId", "rules" => array("required")),
	    "contextType" => array("name" => "contextType", "rules" => array("required")),
	    "parentId" => array("name" => "parentId")
	);

	private static function getCollectionFieldNameAndValidate($commentFieldName, $commentFieldValue, $commentId) {
		return DataValidator::getCollectionFieldNameAndValidate(self::$commentBinding, $commentFieldName, $commentFieldValue);
	}

	private static $defaultDiscussOptions = array( 	
							self::COMMENT_ON_TREE => true,
							self::COMMENT_ANONYMOUS => false,
							self::ONE_COMMENT_ONLY => false); 

	/**
	 * get a comment By Id
	 * @param String $id : is the string representation of the mongoId of the comment
	 * @return array Collection of the discuss
	 */
	public static function getById($id) {
	  	$comment = PHDB::findOne( self::COLLECTION,array("_id"=>new MongoId($id)));
	  	
	  	if (isset($comment)) {
	  		$comment["author"] = Person::getSimpleUserById($comment["author"]);
	  	}

	  	return $comment;
	}

	public static function getWhere($params) {
	  	return PHDB::findAndSort( self::COLLECTION,$params);
	}

	public static function countFrom($from,$type,$id) {
		$res = 0;
		if( @$from && @$type && @$id ){
			$params = array(
				"contextType"=>$type,
				"contextId"=>$id,
				"created" => array( '$gt' => intval( $from ) )
			);
		  	$res = PHDB::count( self::COLLECTION,$params);
		} 
		return $res;
	}

	public static function getWhereSortLimit($params,$sort,$limit=1) {
	  	return PHDB::findAndSort( self::COLLECTION,$params,$sort,$limit);
	}
	
	public static function insert($comment, $userId) {
		$options = self::getCommentOptions($comment["contextId"], $comment["contextType"]);

		$content = trim(@$comment["content"]);
		if (empty($content))
			return array("result"=>false, "msg"=> Yii::t("comment","Please add content to your comment !"));

		//TODO SBAR - add check
		$newComment = array(
			"contextId" => $comment["contextId"],
			"contextType" => $comment["contextType"],
			"parentId" => @$comment["parentCommentId"],
			"text" => $content,
			"created" => time(),
			"author" => $userId,
			"tags" => @$comment["tags"],
			"status" => self::STATUS_POSTED 
		);

		if (self::canUserComment($comment["contextId"], $comment["contextType"], $userId, $options)) {
			PHDB::insert(self::COLLECTION,$newComment);
		} else {
			return array("result"=>false, "msg"=> Yii::t("comment","Error calling the serveur : contact your administrator."));
		}
		
		$newComment["author"] = self::getCommentAuthor($newComment, $options);
		$res = array("result"=>true, "time"=>time(), "msg"=>Yii::t("comment","The comment has been posted"), "newComment" => $newComment, "id"=>$newComment["_id"]);
		
		/*$notificationContexts = array(News::COLLECTION, ActionRoom::COLLECTION_ACTIONS, Survey::COLLECTION);
		if( in_array( $comment["contextType"] , $notificationContexts) ){
			Notification::actionOnPerson ( ActStr::VERB_COMMENT, ActStr::ICON_COMMENT, "", array("type"=>$comment["contextType"],"id"=> $comment["contextId"]));
		}*/
		$objectNotif=null;
		$typeAction=$comment["contextType"];
		if(@$comment["parentCommentId"]){
			$objectNotif = array("id"=> $comment["parentCommentId"], "type" => Comment::COLLECTION);
			$typeAction=Comment::COLLECTION;
		}
		Notification::constructNotification(ActStr::VERB_COMMENT, array("id" => Yii::app()->session["userId"],"name"=> Yii::app()->session["user"]["name"]), array("type"=>$comment["contextType"],"id"=> $comment["contextId"],"name"=>@$options["name"]), $objectNotif, $typeAction);
		//Increment comment count (can have multiple comment by user)
		$resAction = Action::addAction($userId , $comment["contextId"], $comment["contextType"], Action::ACTION_COMMENT, false, true) ;
		if (! $resAction["result"]) {
			$res = array("result"=>false, "msg"=> Yii::t("comment","Something went really bad"));
		}

		return $res;
	}

	/**
	 * Build a comment tree link to a context
	 * @param String $contextId The context object of the comment. 
	 * @param String $contextType The context object type. Can be anything 
	 * @return array of comment organize in tree
	 */
	public static function buildCommentsTree($contextId, $contextType, $userId) {

		$res = array();
		$commentTree = array();
		
		$options = self::getCommentOptions($contextId, $contextType);

		//1. Retrieve all comments of that context that are, root of the comment tree (parentId = "" or empty)
		$whereContext = array(
					"contextId" => $contextId, 
					"contextType" => $contextType);
		$nbComment = PHDB::count(self::COLLECTION, $whereContext);
		
		$whereRoot = $whereContext;
		$whereRoot['$or'] = array(
								array("parentId" => ""), 
								array("parentId" => array('$exists' => false))
							);
		$sort = array("created" => -1);
		$commentsRoot = PHDB::findAndSort(self::COLLECTION, $whereRoot,$sort);
		
		foreach ($commentsRoot as $commentId => $comment) {
			//Get SubComment if option "tree" is set to true
			if (@$options[self::COMMENT_ON_TREE] == true) {
				//2. Get all the children of the comments recurslivly
				$subComments = self::getSubComments($commentId, $options);
				$comment["replies"] = $subComments;
			} else {
				$comment["replies"] = array();
			}
			$comment["author"] = self::getCommentAuthor($comment, $options);
			
			//Manage comment declared as abused
			if (@$comment["status"] == self::STATUS_DELETED || @$comment["status"] == self::STATUS_DECLARED_ABUSED) {
				$comment["text"] = Yii::t("comment","This comment has been deleted because of his content.");
			}
			$commentTree[$commentId] = $comment;
		}
		
		//3. Manage the oneCommentOnly option
		$canComment = self::canUserComment($contextId, $contextType, $userId, $options);

		//4. Get community selected comments
		$communitySelectedComments = self::getCommunitySelectedComments($contextId, $contextType, $options);
		//5. Abused comments
		$abusedComments = self::getAbusedComments($contextId, $contextType, $options);
		
		return array("result"=>true, "msg"=>"The comment tree has been retrieved with success", 
						"options" => $options, "canComment"=>$canComment, "nbComment"=>$nbComment,
						"comments"=>$commentTree, "communitySelectedComments" => $communitySelectedComments, "abusedComments" => $abusedComments,
					);
	}

	private static function getSubComments($commentId, $options) {
		$comments = array();

		$where = array("parentId" => $commentId);
		$comments = PHDB::find(self::COLLECTION, $where);

		foreach ($comments as $commentId => $comment) {
			$subComments = self::getSubComments($commentId, $options);
			$comment["author"] = self::getCommentAuthor($comment, $options);
			if (@$comment["status"] == self::STATUS_DELETED) {
				$comment["text"] = Yii::t("comment","This comment has been deleted because of his content.");
			}

			$comment["replies"] = $subComments;
			$comments[$commentId] = $comment;
		}
		return $comments;
	}

	private static function getCommentAuthor($comment, $options) {
		$author = Person::getMinimalUserById($comment["author"]);
		
		//If anonymous option is set the author of the comment will not displayed
		if (@$options[self::COMMENT_ANONYMOUS] == true) {
			$author = array(
				"name" => "Anonymous",
				"address" => array("addressLocality" => @$author["address"]["addressLocality"]));
		}

		return $author;
	}

	private static function canUserComment($contextId, $contextType, $userId, $options) {
		$canComment = true;
		if (@$options[self::ONE_COMMENT_ONLY] == true) {
			$where = array(
					"contextId" => $contextId, 
					"contextType" => $contextType,
					"author" => $userId);
			$nbComments = PHDB::count(self::COLLECTION, $where);
			if ($nbComments > 0) 
				$canComment = false;
		}

		return $canComment;
	}

	/**
	 * Retrieve the best comments of a discussion using the average of vote up
	 * @param String The context id the discussion is liked to
	 * @param String The context type the discussion is liked to
	 * @return array of comments
	 */
	public static function getCommunitySelectedComments($contextId, $contextType, $options) {
		$res = array();
		//1. Retrieve average number of like on the comment tree
		$c = Yii::app()->mongodb->selectCollection(self::COLLECTION);
		$result = $c->aggregate( array(
						array('$match' => array(
							"contextId" => $contextId, "contextType" => "$contextType" )),
						array('$group' => array(
							'_id' => array("contextId" => '$contextId', 'contextType' => '$contextType' ),
							'avgVoteUp' => array('$avg' => '$voteUpCount')))
						));

		if (@$result["ok"]) {
			$avgVoteUp = @$result["result"][0]["avgVoteUp"];
		} else {
			throw new CTKException(Yii::t("comment","Something went wrong retrieving the average vote up !"));
		}
		
		$whereContext = array(
					"contextId" => $contextId, 
					"contextType" => $contextType,
					"voteUpCount" => array('$gte' => $avgVoteUp));
		$sort = array("voteUpCount" => -1);
		$comments = PHDB::findAndSort(Comment::COLLECTION, $whereContext, $sort);
		
		foreach ($comments as $commentId => $comment) {
			$comment["author"] = self::getCommentAuthor($comment, $options);
			$comment["replies"] = array();
			$res[$commentId] = $comment;
		}
		return $res;
	}

	/**
	 * Retrieve the comments declared as abused of a discussion 
	 * @param String The context id the discussion is liked to
	 * @param String The context type the discussion is liked to
	 * @return float the ave
	 */
	public static function getAbusedComments($contextId, $contextType, $options) {
		$res = array();
		//1. Retrieve the comments with at least one abuse
		$whereContext = array(
					"contextId" => $contextId, 
					"contextType" => $contextType,
					"status" => self::STATUS_DECLARED_ABUSED,
					"reportAbuseCount" => array('$gte' => 0));
		$sort = array("created" => -1);
		$comments = PHDB::findAndSort(Comment::COLLECTION, $whereContext, $sort);
		
		foreach ($comments as $commentId => $comment) {
			$comment["author"] = self::getCommentAuthor($comment, $options);
			$comment["replies"] = array();
			$res[$commentId] = $comment;
		}
		return $res;
	}

	/**
	 * Retrieve comment options from a collection type and an id
	 * @param String $id The id of the collection
	 * @param String $type A collection (type) from where to retrieve the comment options
	 * @return array of comment options
	 */
	public static function getCommentOptions($id, $type) {
		$res = self::$defaultDiscussOptions;
		
		$collection = PHDB::findOneById( $type ,$id, array("commentOptions" => 1));

		if (@$collection["commentOptions"]) {
			$res = $collection;
		}
		
		return $res;
	}

	/**
	 * Save the comment options inside a collection
	 * @param String $id the id of the collection
	 * @param String $type the type of the collection
	 * @param String $options array of options to save
	 * @return array of result (result/msg)
	 */
	public static function saveCommentOptions($id, $type, $options) {
		$res = array("result" => true, "msg" => Yii::t("comment","The comment options has been saved successfully"));

		$where = array("_id"=>new MongoId($id));
		$set = array("commentOptions" => $options);

		//Update the collection
		$res = PHDB::update($type, $where, array('$set' => $set));

		return $res;
	}


	//------------------------------------------------------------//
	//---------------------- Abuse Process -----------------------//
	//------------------------------------------------------------//

	public static function reportAbuse($userId, $commentId, $reason) {
        $action = Action::ACTION_REPORT_ABUSE;
        $collection = Comment::COLLECTION;
        $user = Person::getById($userId);
        $comment = ($commentId) ? PHDB::findOne ($collection, array("_id" => new MongoId($commentId) )) : null;

        if($user && $comment) {
            //check user hasn't allready done the action
            if( !isset($comment[$action])) {
                $dbMethod = '$set';

                // "actions": { "groups": { "538c5918f6b95c800400083f": { "voted": "voteUp" }, "538cb7f5f6b95c80040018b1": { "voted": "voteUp" } } } }
                $map[ Action::NODE_ACTIONS.".".$collection.".".(string)$commentId.".".$action ] = $action ;
                //update the user table 
                //adds or removes an action
                PHDB::update ( Person::COLLECTION , array( "_id" => $user["_id"]), 
                                                    array( $dbMethod => $map));
                
                //push unique user Ids into action node list
                $dbMethod = '$set';

                //increment according to specifications
                $inc = 1;
                
                PHDB::update ($collection, array("_id" => new MongoId($commentId)), 
                                           array( $dbMethod => array( 
                                           				$action.".".$userId => $reason,
                                           				'status' => self::STATUS_DECLARED_ABUSED),
                                                  '$inc'=>array( $action."Count" => $inc)));
                Action::addActionHistory( $userId , $commentId, $collection, $action);
                
                $res = array( "result"          => true,  
                              "userActionSaved" => true,
                              "user"            => PHDB::findOne ( Person::COLLECTION , array("_id" => new MongoId( $userId ) ),array("actions")),
                              "element"         => PHDB::findOne ($collection,array("_id" => new MongoId($commentId) ),array( $action)),
                              "msg"             => "Ok !"
                               );
            } else 
                $res = array( "result" => true,  "userAllreadyDidAction" => true );
        }
        else{
        	$res = array( "result" => false,  "msg" => "An error occured" );
        }
        return $res;     
    }

    public static function changeStatus($userId, $commentId, $action) {
		$comment = self::getById($commentId);
    	if (Authorisation::canEditItem($userId, $comment["contextType"], $comment["contextId"])) {
    		PHDB::update(self::COLLECTION, array("_id" => new MongoId($commentId)),
    					array('$set' => array("status" => $action)));
    	}
    	
    	$res = array( "result"          => true,  
                       "userActionSaved" => true,
                       "user"            => PHDB::findOne ( Person::COLLECTION , array("_id" => new MongoId($userId)),array("actions")),
                       "element"         => PHDB::findOne (self::COLLECTION, array("_id" => new MongoId($commentId)), array( $action)),
                       "msg"             => "Ok !"
               );
    	return $res;
    }


    /**
	 * update a comment in database
	 * @param String $commentId : 
	 * @param string $name fields to update
	 * @param String $value : new value of the field
	 * @return array of result (result => boolean, msg => string)
	 */
	public static function updateField($commentId, $name, $value, $userId){
		$set = array($name => $value);	
		//update the project
		PHDB::update( self::COLLECTION, array("_id" => new MongoId($commentId)), 
		                          array('$set' => $set));
	                  
	    return array("result"=>true, "msg"=>Yii::t("common","Comment well updated"), "id"=>$commentId);
	}
	/**
	 * delete a comment in database
	 * @param String $id : id to delete
	*/
	public static function delete($id) {
		$comment=self::getById($id);
		if($comment["author"]["id"]==Yii::app()->session["userId"]){
			Action::addAction(Yii::app()->session["userId"] , $comment["contextId"], $comment["contextType"], Action::ACTION_COMMENT, true, false) ;
			PHDB::remove(self::COLLECTION,array("_id"=>new MongoId($id)));
			return array("result"=>true, "msg"=>Yii::t("common","you are not the author of the comment"),"comment"=>$comment);
		} else
			return array("result"=>false, "msg"=>Yii::t("common","you are not the author of the comment"));
	}
	public static function getCommentsToModerate($whereAdditional = null, $limit = 0) {


		$where = array( 
			"reportAbuse"=> array('$exists'=>1)
			,"moderate.isAnAbuse" => array('$exists'=>0)
			//One news has to be moderated X times
			,"reportAbuseCount" => array('$lt' => 5)
			//One moderator can't moderate 2 times a news
			,"moderate.".Yii::app()->session["userId"] => array('$exists'=>0)
		);
        if(count($whereAdditional)){
        	$where = array_merge($where,$whereAdditional);
        }
        return PHDB::findAndSort(self::COLLECTION, $where, array("date" =>1), $limit);
	}


}
