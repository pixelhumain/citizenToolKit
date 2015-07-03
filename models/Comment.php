<?php 
class Comment {

	const COLLECTION = "comments";
	
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

		PHDB::insert(self::COLLECTION,$newComment);
		
		$newComment["author"] = Person::getSimpleUserById($newComment["author"]);
		$res = array("result"=>true, "msg"=>"The comment has been posted", "newComment" => $newComment, "id"=>$newComment["_id"]);
		
		//Increment comment count
		$resAction = Action::addAction($userId , $comment["contextId"], $newComment["contextType"], Action::ACTION_COMMENT) ;
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
	public static function buildCommentsTree($contextId, $contextType) {
		
		$commentTree = array();
		//1. Retrieve all comments of that context that are, root of the comment tree (parentId = "" or empty)
		$where = array(
					"contextId" => $contextId, 
					"contextType" => $contextType, 
					'$or' => array(
								array("parentId" => ""), 
								array("parentId" => array('$exists' => false))
							)
					);
		$sort = array("created" => -1);
		$commentsRoot = PHDB::findAndSort(self::COLLECTION, $where,$sort);

		foreach ($commentsRoot as $commentId => $comment) {
			//2. Get all the children of the comments recurslivly
			$subComments = self::getSubComments($commentId);
			$comment["author"] = Person::getSimpleUserById($comment["author"]);
			$comment["replies"] = $subComments;
			$commentTree[$commentId] = $comment;
		}
	
		return $commentTree;
	}

	private static function getSubComments($commentId) {
		$comments = array();

		$where = array("parentId" => $commentId);
		$comments = PHDB::find(self::COLLECTION, $where);

		foreach ($comments as $commentId => $comment) {
			$subComments = self::getSubComments($commentId);
			$comment["author"] = Person::getSimpleUserById($comment["author"]);
			$comment["replies"] = $subComments;
			$comments[$commentId] = $comment;
		}
		return $comments;
	}

	/*$commentTree = array( 
			"558cfe5d2339f285060041aa" => array(
				"_id" => new MongoId("558cfe5d2339f285060041aa"),
				"text" => "Génial ! On peut voir ça où ?",
			    "author" => array(
					    	"id" => "5577e2efa1aa14f08f0041ca",
					    	"name" => "Robert Johnson",
					    	"imgProfil" => ""),
			    "created" => 1435303517,
			    "tags" => array( 
			        "Culture"
			    ),
			    "replies" => array(
			    	"558cfe5d2339f285060042bb" => array(
			    		"_id" => new MongoId("558cfe5d2339f285060042bb"),
			    		"text" => "Viens au local de l'association quand tu veux !",
					    "author" => array(
					    	"id" => "5577e2efa1aa14f08f0041ca",
					    	"name" => "Travis Gabriel",
					    	"imgProfil" => ""),
					    "created" => 1435303517,
					    "replies" => array()
					),
					"558cfe5d2339f285060042cc" => array(
						"_id" => new MongoId("558cfe5d2339f285060041cc"),
			    		"text" => "C'est tjs à Trois Bassins ?",
					    "author" => array(
					    	"id" => "5577e2efa1aa14f08f0041ca",
					    	"name" => "Robert Johnson",
					    	"imgProfil" => ""),
					    "created" => 1435303517,
					    "replies" => array(
					    	"558cfe5d2339f285060042bb" => array(
					    		"_id" => new MongoId("558cfe5d2339f285060042dd"),
					    		"text" => "Oui c'est ça",
							    "author" => array(
							    	"id" => "5577e2efa1aa14f08f0041ca",
							    	"name" => "Travis Gabriel",
							    	"imgProfil" => ""),
							    "created" => 1435303517,
							    "replies" => array()
							)
						)
					)
			    )
			),
			"558cfe5d2339f285060041dd" => array(
				"_id" => new MongoId("558cfe5d2339f285060041dd"),
			    "text" => "C'est basé sur quelle technologie ?",
			    "author" => array(
					    	"id" => "5577e2efa1aa14f08f0041ca",
					    	"name" => "Sylvain Barbot",
					    	"imgProfil" => ""),
			    "created" => 1435303517,
			    "tags" => array( 
			        "Culture"
			    ),
			    "replies" => array()
			)
		);*/
}
