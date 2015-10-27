<?php 
class Need {

	const COLLECTION 		= "needs";
	
	private static $dataBinding = array(
	    "name" => array("name" => "name", "rules" => array("required")),
		"type" => array("name" => "type"),
		"duration" => array("name" => "duration"),
		"description" => array("name" => "description"),
	    "startDate" => array("name" => "startDate"),
	    "endDate" => array("name" => "endDate"),
	    "quantity" => array("name" => "quantity"),
	    "benefits" => array("name" => "benefits"),
	    //"address" => array("name" => "address"),
	    //"postalCode" => array("name" => "address.postalCode"),
	    //"city" => array("name" => "address.codeInsee"),
	    //"addressCountry" => array("name" => "address.addressCountry"),
	    //"tags" => array("name" => "tags"),
	    //"avancement" => array("name" => "properties.avancement"),
	);

	private static function getCollectionFieldNameAndValidate($needFieldName, $needFieldValue, $needId) {
		return DataValidator::getCollectionFieldNameAndValidate(self::$dataBinding, $needFieldName, $needFieldValue, $needId);
	}
	/**
	 * get a need By Id
	 * @param String $id : is the mongoId of the action room
	 * @return array Document of the action room
	 */
	public static function getById($id) {
		$need = PHDB::findOne( self::COLLECTION,array("_id"=>new MongoId($id)));
		if (!empty($need["startDate"]) && !empty($need["endDate"])) {
			if (gettype($need["startDate"]) == "object" && gettype($need["endDate"]) == "object") {
				//Set TZ to UTC in order to be the same than Mongo
				date_default_timezone_set('UTC');
				$need["startDate"] = date('Y-m-d H:i:s', $need["startDate"]->sec);
				$need["endDate"] = date('Y-m-d H:i:s', $need["endDate"]->sec);	
			} else {
				//Manage old date with string on date project
				$now = time();
				$yesterday = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
				$yester2day = mktime(0, 0, 0, date("m")  , date("d")-2, date("Y"));
				$need["endDate"] = date('Y-m-d H:i:s', $yesterday);
				$need["startDate"] = date('Y-m-d H:i:s',$yester2day);;
			}
		}

	  	return $need;
	}

	public static function getWhereSortLimit($params,$sort,$limit=1) {
	  	return PHDB::findAndSort( self::COLLECTION,$params,$sort,$limit);
	}
	public static function insert($params){
		PHDB::insert(self::COLLECTION,$params);
		if($params["parentType"]==Project::COLLECTION){
			$parent = Project::getById($params["parentId"]);
		}
		//$parent = $class::getById($params["parentId"]);
		Notification::createdObjectAsParam(Person::COLLECTION,Yii::app()-> session["userId"],Need::COLLECTION, $params["_id"], $params["parentType"], $params["parentId"],null, null, $parent["address"]["codeInsee"]);

		return array("result"=>true, "msg"=>"Votre besoin est communecté.","idNeed"=>$params["_id"]);
	}
	
	public static function updateNeedField($needId, $needFieldName, $needFieldValue, $userId) {  
		
		$dataFieldName = self::getCollectionFieldNameAndValidate($needFieldName, $needFieldValue, $needId);
		//Specific case : 
		//Tags
		//if ($dataFieldName == "tags") {
		//	$projectFieldValue = Tags::filterAndSaveNewTags($projectFieldValue);
		//}

		//address
				//Start Date - End Date
		if ($dataFieldName == "startDate" || $dataFieldName == "endDate") {
			date_default_timezone_set('UTC');
			$dt = DateTime::createFromFormat('Y-m-d H:i', $needFieldValue);
			if (empty($dt)) {
				$dt = DateTime::createFromFormat('Y-m-d', $needFieldValue);
			}
			$newMongoDate = new MongoDate($dt->getTimestamp());
			$set = array($dataFieldName => $newMongoDate);	
		}
		else {
			$set = array($dataFieldName => $needFieldValue);	
		}

		//update the project
		PHDB::update( self::COLLECTION, array("_id" => new MongoId($needId)), 
		                          array('$set' => $set));
	                  
	    return array("result"=>true, "msg"=>"Votre besoin a été modifié avec succes", "id"=>$needId);
	}
}