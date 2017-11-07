<?php

class Backup {
	const COLLECTION = "backups";
	const CONTROLLER = "backup";
	
	//TODO Translate
	public static $orderTypes = array(
	   
    );

	//From Post/Form name to database field name
	public static $dataBinding = array (
	    "type" => array("name" => "type"),
	    "name" => array("name" => "name"),
	    "object" => array("name" => "object"),
	    "description" => array("name" => "description"),
	    "parentId" => array("name" => "parentId"),
	    "parentType" => array("name" => "parentType"),
	    "object" => array("name" => "object"),
	    "tags" => array("name" => "tags"),
	    "modified" => array("name" => "modified"),
	    "updated" => array("name" => "updated"),
	    "creator" => array("name" => "creator"),
	    "created" => array("name" => "created"),
	    "totalPrice"=> array("name" => "totalPrice"),
	);

	

	/**
	 * get a Poi By Id
	 * @param String $id : is the mongoId of the poi
	 * @return poi
	 */
	public static function getById($id) { 
	  	$backup = PHDB::findOneById( self::COLLECTION ,$id );
	  	return $backup;
	}

	public static function getListBy($where){
		$backups = PHDB::findAndSort( self::COLLECTION , $where, array("created"=>-1));
	  	return $backups;
	}
	public static function insert($backup){
		
        try {
        	$valid = DataValidator::validate( self::CONTROLLER, json_decode (json_encode ($backup), true), null );
        } catch (CTKException $e) {
        	$valid = array("result"=>false, "msg" => $e->getMessage());
        }
        if( $valid["result"]) 
        {
        	$backup["created"] = new MongoDate(time());
        	if(!@$backup["parentId"]){
        		$backup["parentId"]=Yii::app()->session["userId"];
        		$backup["parentType"]=Person::COLLECTION;
        	}
			PHDB::insert(self::COLLECTION,$backup);
			return array("result"=>true, "msg"=>Yii::t("common","Your payment and reservations are well registred"), "backup"=>$backup);
		}else 
            return array( "result" => false, "error"=>"400",
                          "msg" => Yii::t("common","Something went really bad : ".$valid['msg']) );

	}
	/*
	* Increment a comment rating for an order for a specific product or sevrice
	*/
	/*public static function actionRating($params,$commentId){
		$allRating=Comment::buildCommentsTree($params["contextId"], $params["contextType"], Yii::app()->session["userId"], array("rating"));
		$sum=0;
		foreach ($allRating["comments"] as $key => $value) {
			$sum=$sum+$value["rating"];
		}
		if($allRating["nbComment"] != 0)
			$sum=$sum / $allRating["nbComment"] ;
		$average=round( $sum , 1);
		PHDB::update($params["contextType"],array("_id" => new MongoId($params["contextId"])),array('$set'=>array("averageRating"=>$average)));
		PHDB::update(self::COLLECTION,array("_id" => new MongoId($params["orderId"])),array('$set'=>array("comment"=>$commentId)));
	}*/
}
?>