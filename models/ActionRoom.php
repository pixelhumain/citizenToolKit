<?php 
class ActionRoom {

	const COLLECTION = "actionRooms";
	
	/**
	 * get a action room By Id
	 * @param String $id : is the mongoId of the action room
	 * @return array Document of the action room
	 */
	public static function getById($id) {
	  	$actionRoom = PHDB::findOne( self::COLLECTION,array("_id"=>new MongoId($id)));
	  	return $actionRoom;
	}

}
