<?php 
class Need {

	const COLLECTION 		= "needs";
	
	/**
	 * get a need By Id
	 * @param String $id : is the mongoId of the action room
	 * @return array Document of the action room
	 */
	public static function getById($id) {
		return PHDB::findOne( self::COLLECTION,array("_id"=>new MongoId($id)));

	}

	public static function getWhereSortLimit($params,$sort,$limit=1) {
	  	return PHDB::findAndSort( self::COLLECTION,$params,$sort,$limit);
	}
	public static function insert($params){
		PHDB::insert(self::COLLECTION,$params);
		return array("result"=>true, "msg"=>"Votre besoin est communecté.","idNeed"=>$params["_id"]);
	}
}