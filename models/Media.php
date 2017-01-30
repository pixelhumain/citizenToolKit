<?php 
class Media {
	const COLLECTION = "media";
	//const CONTROLLER = "media";
	const ICON = "fa-rss";
	const COLOR = "#F9B21A";


	/**
	 * get an event By Id
	 * @param type $id : is the mongoId of the event
	 * @return type
	 */
	public static function getById($id) {
		$event = PHDB::findOne(self::COLLECTION,array("_id"=>new MongoId($id)));
	  	return $event;
	}

}

?>