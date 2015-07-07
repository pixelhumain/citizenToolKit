<?php 
class ActionRoom {

	const COLLECTION 		= "actionRooms";
	const TYPE_SURVEY 		= "survey";
	const TYPE_DISCUSS 		= "discuss";
	const TYPE_BRAINSTORM 	= "brainstorm";
	
	/**
	 * get a action room By Id
	 * @param String $id : is the mongoId of the action room
	 * @return array Document of the action room
	 */
	public static function getById($id) {
	  	$actionRoom = PHDB::findOne( self::COLLECTION,array("_id"=>new MongoId($id)));
	  	return $actionRoom;
	}

	public static function getWhereSortLimit($params,$sort,$limit=1) {
	  	return PHDB::findAndSort( self::COLLECTION,$params,$sort,$limit);
	}
}
