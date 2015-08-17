<?php 
class Comment {

	const COLLECTION = "comments";

	//Options of the comment
	const COMMENT_ON_TREE = "tree";
	const COMMENT_ANONYMOUS = "anonymous";
	const ONE_COMMENT_ONLY = "oneCommentOnly";
	
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

	public static function getWhereSortLimit($params,$sort,$limit=1) {
	  	return PHDB::findAndSort( self::COLLECTION,$params,$sort,$limit);
	}
	
	public static function insert($comment, $userId) {
		$options = self::getCommentOptions($comment["contextId"], $comment["contextType"]);

		//TODO SBAR - add check
		$newComment = array(
			"contextId" => $comment["contextId"],
			"contextType" => $comment["contextType"],
			"parentId" => @$comment["parentCommentId"],
			"text" => $comment["content"],
			"created" => time(),
			"author" => $userId,
			"tags" => @$comment["tags"]
		);

		if (self::canUserComment($comment["contextId"], $comment["contextType"], $userId, $options)) {
			PHDB::insert(self::COLLECTION,$newComment);
		} else {
			return array("result"=>false, "msg"=>"The user can not comment on this discussion");
		}
		
		$newComment["author"] = self::getCommentAuthor($newComment, $options);
		$res = array("result"=>true, "msg"=>"The comment has been posted", "newComment" => $newComment, "id"=>$newComment["_id"]);
		
		//Increment comment count (can have multiple comment by user)
		$resAction = Action::addAction($userId , $comment["contextId"], $newComment["contextType"], Action::ACTION_COMMENT, false, true) ;
		if (! $resAction["result"]) {
			$res = array("result"=>false, "msg"=>"Something went really bad");
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
			$commentTree[$commentId] = $comment;

		}
		
		//3. Manage the oneCommentOnly option
		$canComment = self::canUserComment($contextId, $contextType, $userId, $options);

		return array("result"=>true, "msg"=>"The comment tree has been retrieved with success", 
							"options" => $options, "comments"=>$commentTree, "canComment"=>$canComment, 
							"nbComment"=>$nbComment);
	}

	private static function getSubComments($commentId, $options) {
		$comments = array();

		$where = array("parentId" => $commentId);
		$comments = PHDB::find(self::COLLECTION, $where);

		foreach ($comments as $commentId => $comment) {
			$subComments = self::getSubComments($commentId, $options);
			$comment["author"] = self::getCommentAuthor($comment, $options);

			$comment["replies"] = $subComments;
			$comments[$commentId] = $comment;
		}
		return $comments;
	}

	private static function getCommentAuthor($comment, $options) {
		$author = Person::getSimpleUserById($comment["author"]);
		
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
			if ($nbComments > 0) $canComment = false;
		}
		return $canComment;
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
			$res = $collection["commentOptions"];
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
		$res = array("result" => true, "msg" => "The comment options has been saved successfully");

		$where = array("_id"=>new MongoId($id));
		$set = array("commentOptions" => $options);

		//Update the collection
		$res = PHDB::update($type, $where, array('$set' => $set));

		return $res;
	}
}
