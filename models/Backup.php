<?php

class Backup {
	const COLLECTION = "backups";
	const CONTROLLER = "backup";
	
	//TODO Translate
	public static $orderTypes = array(
	   
    );

	//From Post/Form name to database field name
	public static $dataBinding = array (
	    "id" => array("name" => "id"),
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
	    "currency"=> array("name" => "currency"),
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
		$backups = PHDB::findAndSort( self::COLLECTION , $where, array("updated"=>-1));
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
        	$backup["updated"] = new MongoDate(time());
        	if(!@$backup["parentId"]){
        		$backup["parentId"]=Yii::app()->session["userId"];
        		$backup["parentType"]=Person::COLLECTION;
        	}
			PHDB::insert(self::COLLECTION,$backup);
			if($backup["type"]==Circuit::COLLECTION)
				$msg="Your circuit is well registered";
			else
				$msg="Your cart is well registered";
			return array("result"=>true, "msg"=>Yii::t("common",$msg), "backup"=>$backup);
		}else 
            return array( "result" => false, "error"=>"400",
                          "msg" => Yii::t("common","Something went really bad : ".$valid['msg']) );

	}
	public static function update($params){
		//$backup=self::getById($id);
		try {
        	$valid = DataValidator::validate( self::CONTROLLER, json_decode (json_encode ($params), true), null );
        } catch (CTKException $e) {
        	$valid = array("result"=>false, "msg" => $e->getMessage());
        }
        if( $valid["result"]) 
        {
        	$set=array(
        		"updated"=> new MongoDate(time()),
	            "object"=>$params["object"]
        		);
        	if(@$params["totalPrice"])
        		$set["totalPrice"]=$params["totalPrice"];
        	$id=$params["id"];
			PHDB::update(self::COLLECTION,array("_id"=>new MongoId($id)),array('$set' => $set));
			return array("result"=>true, "msg"=>Yii::t("common","Your backup has been succesfuly updated"));
		}else
			return array( "result" => false, "error"=>"400",
                          "msg" => Yii::t("common","Something went really bad: ".$valid['msg']) );

	}
	public static function delete($id){
		$backup=self::getById($id);
		if(@Yii::app()->session["userId"] && Yii::app()->session["userId"]==$backup["parentId"]){
			PHDB::remove(self::COLLECTION,array("_id"=>new MongoId($id)));
			return array("result"=>true, "msg"=>Yii::t("common","Your backup has been deleted with success"));
		}else
			return array( "result" => false, "error"=>"400","msg" => Yii::t("common","Something went really bad") );

	}
}
?>