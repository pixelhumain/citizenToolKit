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

	public static $colorChartMultipleChoice = 
					array ("#68db87", "#68cbdb", "#e4cf58", 
						   "#e47158", "#b793d4", "#f59580", 
						   "#f5c680", "#80f586", "#8f80f5", "#f580ba" );


	public static $dataBinding = array (
		
		"title" 				=> array("name" => "title"),
		"shortDescription" 		=> array("name" => "shortDescription"),
		"description" 			=> array("name" => "description", 			"rules" => array("required")),
		
		"answers"				=> array("name" => "answers"),

		"arguments" 			=> array("name" => "arguments"),
		"tags" 					=> array("name" => "tags"),
	    "urls" 					=> array("name" => "urls"),
	    "medias" 				=> array("name" => "medias"),
	    
		// true / false
		"amendementActivated" 	=> array("name" => "amendementActivated", 	"rules" => array("required")),
		"amendementDateEnd" 	=> array("name" => "amendementDateEnd"),
		"durationAmendement" 	=> array("name" => "durationAmendement"),
		
		"address" => array("name" => "address", "rules" => array("addressValid")),
	    "addresses" => array("name" => "addresses"),
	    
	    "geo" => array("name" => "geo", "rules" => array("geoValid")),
	    "geoPosition" => array("name" => "geoPosition", "rules" => array("geoPositionValid")),
	    
		// true / false
		"voteActivated" 		=> array("name" => "voteActivated", 		"rules" => array("required")),
		"voteDateEnd" 			=> array("name" => "voteDateEnd"),
		
		// Amendable / ToVote / Closed / Archived
		"status" 				=> array("name" => "status", 				"rules" => array("required")), 
		
		// true / false
		"voteAnonymous" 		=> array("name" => "voteActivated", 		"rules" => array("required")),
		// true / false
		"voteCanChange" 		=> array("name" => "voteActivated", 		"rules" => array("required")),
		

		// 50%  / 75% / 90%
		"majority" 				=> array("name" => "majority", 				"rules" => array("required")),

		// true / false
		//"canModify" 			=> array("name" => "canModify", 			"rules" => array("required")), 
		"viewCount" 			=> array("name" => "viewCount"),

		//"idUserAuthor" 			=> array("name" => "idUserAuthor", 			"rules" => array("required")),
		"idParentRoom" 			=> array("name" => "idParentRoom", 			"rules" => array("required")),
		"parentId"              => array("name" => "parentId",              "rules" => array("required")),
        "parentType"            => array("name" => "parentType",            "rules" => array("required")),
       
       	"amendements"           => array("name" => "amendements"),
        
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
		$proposal = PHDB::findOneById( self::COLLECTION , $id );
		$proposal["type"] = self::COLLECTION;
		$proposal["voteRes"] = self::getAllVoteRes($proposal);

		$proposal["hasVote"] = @$proposal["votes"] ? Cooperation::userHasVoted(
													@Yii::app()->session['userId'], $proposal["votes"]) : false; 
		if(@$proposal["parentType"] &&  @$proposal["parentType"] != null &&
		   @$proposal["parentId"] 	&&  @$proposal["parentId"] != null){
			$proposal["auth"] = Authorisation::canParticipate(@Yii::app()->session['userId'], 
															  @$proposal["parentType"], @$proposal["parentId"]);
		}
		return $proposal;
	}

	public static function getSimpleSpecById($id, $where=null, $fields=null){
		if(empty($fields))
			$fields = array("_id", "name");
		$where["_id"] = new MongoId($id) ;
		$survey = PHDB::findOne(self::COLLECTION, $where ,$fields);
		return @$survey;
	}

	public static function getAllVoteRes($proposal){
		$voteRes = array("up"=> array("bg-color"=> "green-k",
 										"voteValue"=>"up"),
 						"down"=> array("bg-color"=> "red",
 										"voteValue"=>"down"),
 						"white"=> array("bg-color"=> "white",
 										"voteValue"=>"white"),
 						"uncomplet"=> array("bg-color"=> "orange",
 										"voteValue"=>"uncomplet"),

 		);

 		$votes = @$proposal["votes"] ? $proposal["votes"] : array();

 		
 		if(@$proposal["answers"]){
 			$voteRes = $proposal["answers"];
 			//$votes = array();
 			foreach ($voteRes as $key => $value) {
 				$voteRes[$key] = array( "bg-color"=> "vote-".$key,
 									 	"bg-color-val"=> self::getColorChart($key),
 									 	"voteValue"=>$value);
 				if(!@$votes[$key]) $votes[$key] = array();
 			}
 		}else{
 			if(!@$votes["up"]) $votes["up"] = array();
	 		if(!@$votes["down"]) $votes["down"] = array();
	 		if(!@$votes["white"]) $votes["white"] = array();
	 		if(!@$votes["uncomplet"]) $votes["uncomplet"] = array();
 		}
 		//$voteRes = array("up"=>array(), );

 		$totalVotant = 0;
 		foreach ($votes as $key => $value) {
 			$voteRes[$key]["votant"] = count($votes[$key]);
 			$totalVotant+=count($votes[$key]);
 		} //echo $totalVotant; exit;
 		foreach ($votes as $key => $value) {
 			$voteRes[$key]["percent"] = $totalVotant > 0 ? round($voteRes[$key]["votant"] * 100 / $totalVotant, 2) : 0;
 		}

 		//var_dump($voteRes); echo "<br><br>"; var_dump($votes); exit;
 		
 		return $voteRes;
	}
	public static function getTotalVoters($proposal){
		if(!@$proposal["votes"]) return 0;
		$totalVotant = 0;
		foreach ($proposal["votes"] as $key => $value) {
 			$totalVotant+=count($value);
 		}
 		return $totalVotant;
	}

	public static function createModeration($parentType, $parentId){
        error_log("createModeration ? " . $parentType . " - ". $parentId);
		$dateWeek  = mktime(date("H")+1, date("i"), date("s"), date("m"), date("d")+2, date("Y"));
		$voteDateEnd = date("Y-m-d H:i:s", $dateWeek ); 
		$moderation = array(
			"title" => "Moderation",
		    "description" => "",
		    "amendementActivated" => "false",
		    "amendementDateEnd" => "",
		    "voteActivated" => "true",
		    "voteDateEnd" => $voteDateEnd,
		    "majority" => "75",
		    "voteAnonymous" => "true",
		    "voteCanChange" => "true",
		    "status" => "tovote",
		    "parentId" => $parentId,
		    "parentType" => $parentType,
		    "modified" => new MongoDate(time()),
		    "updated" => new MongoDate(time()),
		    "created" => time()
		);

        PHDB::insert(self::COLLECTION, $moderation );
        error_log("moderation created");
	}

	private static function getColorChart($key){
		return @self::$colorChartMultipleChoice[$key] ? self::$colorChartMultipleChoice[$key] : "#c6c6c6";
	}
}

?>