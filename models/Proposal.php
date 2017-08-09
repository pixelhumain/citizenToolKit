<?php

class Proposal
{
	const COLLECTION = "proposals";
	const PARENT_COLLECTION = "rooms";
	const CONTROLLER = "proposal";

  	const STATUS_AMENDABLE 	= "amendable";
  	const STATUS_TOVOTE 	= "tovote";
  	const STATUS_CLOSED 	= "closed";
	const STATUS_ARCHIVED 	= "archived";

	public static $dataBinding = array (
		
		"title" 				=> array("name" => "title", 				"rules" => array("required")),
		"shortDescription" 		=> array("name" => "shortDescription"),
		"description" 			=> array("name" => "description", 			"rules" => array("required")),
		
		// true / false
		"amendementActivated" 	=> array("name" => "amendementActivated", 	"rules" => array("required")),
		"amendementEndDate" 	=> array("name" => "amendementEndDate"),
		
		// true / false
		"voteActivated" 		=> array("name" => "voteActivated", 		"rules" => array("required")),
		"endDateVote" 			=> array("name" => "endDateVote"),
		"durationAmendement" 	=> array("name" => "durationAmendement"),
		
		// Amendable / ToVote / Closed / Archived
		"status" 				=> array("name" => "status", 				"rules" => array("required")), 
		
		// 50%  / 75% / 90%
		"majority" 				=> array("name" => "majority"),

		// true / false
		"canModify" 			=> array("name" => "canModify", 			"rules" => array("required")), 
		"viewCount" 			=> array("name" => "viewCount"),

		"idUserAuthor" 			=> array("name" => "idUserAuthor", 			"rules" => array("required")),
		"idParentRoom" 			=> array("name" => "idParentRoom", 			"rules" => array("required")),
	);

	public static function getDataBinding() {
	  	return self::$dataBinding;
	}

	public static function getById($id) {
		$survey = PHDB::findOneById( self::COLLECTION , $id );
		return $survey;
	}

}

?>