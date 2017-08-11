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
		"tags" 					=> array("name" => "tags"),
	    "urls" 					=> array("name" => "urls"),
	    
		// true / false
		"amendementActivated" 	=> array("name" => "amendementActivated", 	"rules" => array("required")),
		"amendementDateEnd" 	=> array("name" => "amendementDateEnd"),
		"durationAmendement" 	=> array("name" => "durationAmendement"),
		
		// true / false
		"voteActivated" 		=> array("name" => "voteActivated", 		"rules" => array("required")),
		"voteDateEnd" 			=> array("name" => "voteDateEnd"),
		
		// Amendable / ToVote / Closed / Archived
		"status" 				=> array("name" => "status", 				"rules" => array("required")), 
		
		// 50%  / 75% / 90%
		"majority" 				=> array("name" => "majority"),

		// true / false
		"canModify" 			=> array("name" => "canModify", 			"rules" => array("required")), 
		"viewCount" 			=> array("name" => "viewCount"),

		//"idUserAuthor" 			=> array("name" => "idUserAuthor", 			"rules" => array("required")),
		"idParentRoom" 			=> array("name" => "idParentRoom", 			"rules" => array("required")),
		"parentId"              => array("name" => "parentId",              "rules" => array("required")),
        "parentType"            => array("name" => "parentType",            "rules" => array("required")),
        

	    "modified" => array("name" => "modified"),
	    "updated" => array("name" => "updated"),
	    "creator" => array("name" => "creator"),
	    "created" => array("name" => "created"),

	    //"medias" => array("name" => "medias"),
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