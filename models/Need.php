<?php 
class Need {

	const COLLECTION = "needs";
	const CONTROLLER = "need";
	const ICON = "fa-cubes";
	const COLOR = "#8C5AA1";
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
	public static function getSimpleNeedById($id) {
		$simpleNeed = array();
		$need = PHDB::findOneById( self::COLLECTION ,$id, array("id" => 1, "name" => 1, "description" => 1, "type" => 1, "startDate" => 1, "endDate" => 1) );
		//$need = PHDB::find( self::COLLECTION,array("_id"=>new MongoId($id)));
		if (!empty($need)) {
			$simpleNeed["_id"]=$need["_id"];
			$simpleNeed["name"]=@$need["name"];
			$simpleNeed["type"]=@$need["type"];
			$simpleNeed["description"]=@$need["description"];
			$simpleNeed["startDate"] = @$need["startDate"];
			$simpleNeed["endDate"] = @$need["endDate"];
		}

	  	return $simpleNeed;
	}
	public static function listNeeds($id, $type){
		$needs=array();
		if($type==Organization::COLLECTION){
			$parent=Organization::getById($id);
		}
		if($type==Project::COLLECTION){
			$parent=Project::getById($id);
		}
		if(@$parent["links"]["needs"]){
			foreach ($parent["links"]["needs"] as $key => $value){
				$need = self::getById($key);
           		$needs[$key] = $need;
			}
		}
		return $needs;
	}
	public static function getWhereSortLimit($params,$sort=array("created"=>-1),$limit=1) {
	  	return PHDB::findAndSort( self::COLLECTION,$params,$sort,$limit);
	}
	public static function getAndCheck($params){
	    $newNeed = array(
			"name" => $params['name'],
			'type' => $params['type'],
			"duration" => $params["duration"],
			"quantity" => $params["quantity"],
			"benefits" => $params["benefits"]
		);
		date_default_timezone_set('UTC');
		if(@$params["duration"] && $params["duration"]=="ponctuel"){
			$newNeed["startDate"] = new MongoDate(strtotime($params['startDate']));
			$newNeed["endDate"] = new MongoDate(strtotime($params['endDate']));
		}
		$newNeed = array_merge( $newNeed , array( 'public' => true,
								'created' => new MongoDate(time()),
						        'creator' => Yii::app()->session['userId'] ) );	
		return $newNeed;
	}					
	public static function insert($params, $context){
		$newNeed = self::getAndCheck($params);
		PHDB::insert(self::COLLECTION,$newNeed);
		if($context["parentType"]==Project::COLLECTION){
			$parent = Project::getById($context["parentId"]);
		}
		if($context["parentType"]==Organization::COLLECTION){
			$parent = Organization::getById($context["parentId"]);
		}
		Link::connect($newNeed["_id"],self::COLLECTION,$context["parentId"],$context["parentType"],Yii::app() -> session["userId"],$context["parentType"]);
		Link::connect($context["parentId"],$context["parentType"],$newNeed["_id"],self::COLLECTION,Yii::app() -> session["userId"],self::COLLECTION);
		//$parent = $class::getById($params["parentId"]);
		//Notification::createdObjectAsParam( Person::COLLECTION, Yii::app()->session['userId'],Event::COLLECTION, (String)$newEvent["_id"], $params["organizerType"], $params["organizerId"], $newEvent["geo"], array($newEvent["type"]),$newEvent["address"]);

		Notification::createdObjectAsParam(Person::COLLECTION,Yii::app()-> session["userId"], Need::COLLECTION, (String)$newNeed["_id"], $context["parentType"], $context["parentId"], $parent["geo"], array($newNeed["type"]), $parent["address"]);

		return array("result"=>true, "msg"=>"Votre besoin est communecté.","idNeed"=>$newNeed["_id"]);
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