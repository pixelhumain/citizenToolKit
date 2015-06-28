<?php 
class Comment {

	const COLLECTION = "comments";
	
	/**
	 * get a comment By Id
	 * @param String $id : is the string representation of the mongoId of the comment
	 * @return array Collection of the discuss
	 */
	public static function getById($id) {
	  	$comment = PHDB::findOne( self::COLLECTION,array("_id"=>new MongoId($id)));
	  	return $comment;
	}

	public static function getWhere($params) {
	  	return PHDB::findAndSort( self::COLLECTION,$params);
	}

	public static function getWhereSortLimit($params,$sort,$limit=1) {
	  	return PHDB::findAndSort( self::COLLECTION,$params,$sort,$limit);
	}
	

	public static function buildCommentsTree($parentId, $parentType) {
		$commentTree = array( 
			"558cfe5d2339f285060041aa" => array(
				"_id" => new MongoId("558cfe5d2339f285060041aa"),
				"text" => "Bla bla bla",
			    "author" => array(
					    	"id" => "5577e2efa1aa14f08f0041ca",
					    	"name" => "Sylvain Barbot",
					    	"imgProfil" => ""),
			    "created" => 1435303517,
			    "tags" => array( 
			        "Culture"
			    ),
			    "replies" => array(
			    	"558cfe5d2339f285060042bb" => array(
			    		"_id" => new MongoId("558cfe5d2339f285060042bb"),
			    		"text" => "Arrête avec tes généralités !",
					    "author" => array(
					    	"id" => "5577e2efa1aa14f08f0041ca",
					    	"name" => "Sylvain Barbot",
					    	"imgProfil" => ""),
					    "created" => 1435303517,
					    "replies" => array()
					),
					"558cfe5d2339f285060042cc" => array(
						"_id" => new MongoId("558cfe5d2339f285060041cc"),
			    		"text" => "Tu n'as pas mieux à dire franchement !",
					    "author" => array(
					    	"id" => "5577e2efa1aa14f08f0041ca",
					    	"name" => "Sylvain Barbot",
					    	"imgProfil" => ""),
					    "created" => 1435303517,
					    "replies" => array(
					    	"558cfe5d2339f285060042bb" => array(
					    		"_id" => new MongoId("558cfe5d2339f285060042bb"),
					    		"text" => "Arrête avec tes généralités !",
							    "author" => array(
							    	"id" => "5577e2efa1aa14f08f0041ca",
							    	"name" => "Sylvain Barbot",
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
			    "text" => "Oui mais tu as pensais aux pauvres chauffeurs de taxi qui ont payé une licence à 200K€ ?",
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
		);
		return $commentTree;
	}
}
