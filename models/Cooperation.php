<?php 
class Cooperation {

	/*
		* GET ALL ROOM FOR ONE ELEMENT  	:	getCoopData/contextType/contextId/room/
		* GET ONE ROOM 						:	getCoopData/contextType/contextId/room/all/roomId 

		* GET ALL PROPOSAL FOR ONE ELEMENT  :	getCoopData/contextType/contextId/proposal/
		* GET ONE PROPOSAL 					:	getCoopData/contextType/contextId/proposal/all/proposalId 
	*/

	public static $iconCoop = array(
		"mine" => "user",
		"amendable" => "pencil",
		"todo" => "ticket",
		"tovote" => "gavel",
		"done" => "check",
		"closed" => "trash",
		"resolved" => "certificate",
		"disabled" => "times",
		);

	public static function getIconCoop($key){
		if(isset(self::$iconCoop[$key])) return self::$iconCoop[$key];
			else return "ban";
	}

	public static $colorCoop = array(
		"mine" => "blue",
		"amendable" => "purple",
		"todo" => "green-k",
		"progress" => "green-k",
		"startingsoon" => "green-k",
		"tovote" => "green-k",
		"done" => "red",
		"closed" => "red",
		"resolved" => "dark",
		"nodate" => "red",
		"disabled" => "orange",
		"late" => "orange",
		);

	public static function getColorCoop($key){
		if(isset(self::$colorCoop[$key])) return self::$colorCoop[$key];
			else return "dark";
	}

	public static $colorVoted = array(
		"up" => "green",
		"down" => "red",
		"white" => "white",
		"uncomplet" => "orange",

	);

	public static function getColorVoted($voted){
		if(isset(self::$colorVoted[$voted])) return self::$colorVoted[$voted];
		else return "dark";
	}

	public static function getCoopData($parentType, $parentId, $type, $status=null, $dataId=null){
		
		$res = array();

		self::updateStatusProposal($parentType, $parentId);

		if($type == Room::CONTROLLER){ 
			if(empty($dataId)){ //si pas d'id : prend toutes les rooms pour un element parent

				$status = empty($status) ? "open" : $status;
				$query = array( "parentType" => $parentType, "parentId" => $parentId, "status" => $status);
				$res["roomList"] = PHDB::findAndSort ( 
								Room::COLLECTION, $query, array("amendementDateEnd" => 1, "voteDateEnd" => 1));

				$res["allCount"] = self::getAllCount($parentType, $parentId);

			}else{ //si un d'id : prend récupère toutes les proposals & actions & resolutions de la room
				if(isset($dataId) && $dataId != "undefined")
				$res["room"] = Room::getById($dataId);

				$query = array( "idParentRoom" => $dataId, 
								"status" => array('$in'=>array('amendable', "tovote", "disabled", "todo", "adopted", "refused")));
				
				$res["proposalList"] = PHDB::findAndSort (Proposal::COLLECTION, $query, 
															array("status" => -1, "amendementDateEnd" => 1, "voteDateEnd" => 1));
				
				$res["actionList"] = PHDB::findAndSort (Action::COLLECTION, $query, 
															array("status" => -1, "voteDateEnd" => 1));

				$res["resolutionList"] = PHDB::findAndSort ("resolutions", $query, 
															array("status" => 1, "voteDateEnd" => -1));

			}
		}

		else if($type == Proposal::CONTROLLER){
			if(empty($dataId) && $parentType != News::CONTROLLER){ //si pas d'id : prend toutes les proposal pour un element parent
				$query = array( "parentType" => $parentType, "parentId" => $parentId);
				
				if(!empty($status)) {
					if($status == "mine"){
						$myId = @Yii::app()->session['userId'] ? Yii::app()->session['userId'] : false;
						if($myId != false){
							$query["creator"] = $myId;
						}
					}else{
						$query["status"] = $status;
					}
				}else{
					$query["status"] = array('$in'=>array('amendable', "tovote"));

					$res["roomList"] = PHDB::findAndSort ( Room::COLLECTION, 
						array( "parentType" => $parentType, "parentId" => $parentId), array());
				}
				
				$res["proposalList"] = PHDB::findAndSort ( 
								  		Proposal::COLLECTION, $query, 
								  		array("status" => -1, "amendementDateEnd" => 1, "voteDateEnd" => 1));

			}else{ //si un d'id : prend récupère toutes les proposals & actions & resolutions de la room
				if($parentType != News::CONTROLLER) //parentType == "news" => modération de la news
					$res["proposal"] = Proposal::getById($dataId);
				else{
					$res["proposal"] = PHDB::findOne( Proposal::COLLECTION , 
										array("parentType"=>News::CONTROLLER, "parentId"=>$parentId) );
					$res["news"] = News::getById($parentId);
				}
			}
		}

		else if($type == Action::CONTROLLER){
			if(empty($dataId)){ //si pas d'id : prend toutes les rooms pour un element parent
				$query = array( "parentType" => $parentType, "parentId" => $parentId);
				if($status == "mine"){
					$myId = @Yii::app()->session['userId'] ? Yii::app()->session['userId'] : false;
					if($myId != false){
						$query["creator"] = $myId;
					}
				}else{
					$query["status"] = $status;
				}
				$res["actionList"] = PHDB::findAndSort (Action::COLLECTION, $query, 
							  							array("dateEnd" => 1));
			}else{ //si un d'id : prend récupère toutes les proposals & actions & resolutions de la room
				$res["action"] = Action::getById($dataId);
			}

		}else if($type == Resolution::CONTROLLER){
			if(empty($dataId)){ //si pas d'id : prend toutes les Resolution pour un element parent
				$query = array( "parentType" => $parentType, "parentId" => $parentId);
				if($status == "mine"){
					$myId = @Yii::app()->session['userId'] ? Yii::app()->session['userId'] : false;
					if($myId != false){
						$query["creator"] = $myId;
					}
				}else{
					$query["status"] = $status;
				}
				$res["resolutionList"] = PHDB::findAndSort (Resolution::COLLECTION, $query, 
							  								array("status" => 1, "dateEnd" => -1));
			}else{ //si un d'id : prend récupère toutes les proposals & actions & resolutions de la room
				$res["resolution"] = Resolution::getById($dataId);
				$res["resolution"]["actions"] = PHDB::findAndSort (Action::COLLECTION, 
															array("idParentResolution" => $dataId), 
							  								array("status" => 1, "dateEnd" => -1));
			}
		}

		$res = self::checkRoleAccess($res);

		$res["post"]["type"] = $type;
		$res["post"]["status"] = $status;
		$res["post"]["parentId"] = $parentId;
		$res["post"]["parentType"] = $parentType;

		return $res;
	}

	public static function checkRoleAccess($res){
		$me = Element::getByTypeAndId("citoyens", Yii::app()->session['userId']);

		foreach (array( "proposal", 	"proposalList", 
						"action", 		"actionList", 
						"resolution", 	"resolutionList",
						"tovote", "amendable", "resolved", "closed", "disabled", "mine",
						"todo", "done", "disabled") as $list) {
			if(isset($res[$list])){
				//var_dump(@$res[$list]["_id"]);exit;
				$listCoop = !@$res[$list]["_id"] ? $res[$list] : array($res[$list]);
				foreach ($listCoop as $k => $coop) { //echo $list." - "; var_dump($listCoop);
					if($coop["parentType"] == "projects") $link = "projects";
					if($coop["parentType"] == "organizations") $link = "memberOf";
					$myRoles = @$me["links"][$link][@$coop["parentId"]]["roles"] ? 
							   @$me["links"][$link][@$coop["parentId"]]["roles"] : array();

					if(@$coop["idParentRoom"]){
						$roomId = $coop["idParentRoom"];
						//var_dump($myRoles);echo @$coop["parentId"]."-".@$coop["parentType"]."<br>";
						$parentRoom = Room::getById($roomId);
						$accessRoom = @$parentRoom ? Room::getAccessByRole($parentRoom, $myRoles) : ""; 
						//echo $accessRoom; exit;
						//echo $accessRoom."=";
						if($accessRoom == "lock"){
							unset($res[$list][$k]);
						}
					}
				}
			}
		}//exit;
		return $res;
	}

	public static function checkRoleAccessInNews($newsList){ //return $newsList;
		$me = Element::getByTypeAndId("citoyens", Yii::app()->session['userId']);
		foreach($newsList as $k => $news){
			if(@$news["object"] && @$news["object"]["type"] == "proposals"){
				$proposal = Proposal::getById(@$news["object"]["id"]);
				$parentRoom = Room::getById(@$proposal["idParentRoom"]);
				if(@$parentRoom["roles"]){
					if($proposal["parentType"] == "projects") 		$link = "projects";
					if($proposal["parentType"] == "organizations")  $link = "memberOf";
					$myRoles = @$me["links"][@$link][@$proposal["parentId"]]["roles"] ? 
							   @$me["links"][@$link][@$proposal["parentId"]]["roles"] : array();

					$accessRoom = @$parentRoom ? Room::getAccessByRole($parentRoom, $myRoles) : ""; 
					
					if($accessRoom == "lock"){
						unset($newsList[$k]);
					}
				}
			}
		}
		return $newsList;
		
	}



	public static function userHasVoted($userId, $obj){
		foreach ($obj as $keyVal=>$arr) {
			foreach ($arr as $keyId) {
				if($keyId == $userId) return (string)$keyVal;
			}
		}
		return false;
	}

	public static function getAllCount($parentType, $parentId){

		$myId = @Yii::app()->session['userId'] ? Yii::app()->session['userId'] : false;
		$allCount = array();

		//count proposals
		foreach (array("tovote", "amendable", "resolved", "closed", "disabled") as $status) {
			$query = array( "parentType" => $parentType, "parentId" => $parentId, "status" => $status);
			$allCount["proposals"][$status] = PHDB::find (Proposal::COLLECTION, $query, array());
			
			if($myId != false){
				$query = array( "parentType" => $parentType, "parentId" => $parentId, "creator" => $myId);
				$allCount["proposals"]["mine"] = PHDB::find (Proposal::COLLECTION, $query, array());
			}
		}

			//check roles proposals
			$allCount["proposals"] = self::checkRoleAccess($allCount["proposals"]);
			foreach (array("tovote", "amendable", "resolved", "closed", "disabled", "mine") as $status) {
				if(isset($allCount["proposals"][$status]))
					$allCount["proposals"][$status] = count($allCount["proposals"][$status]);
			}
		

		//count actions
		foreach (array("todo", "done", "disabled") as $status) {
			$query = array( "parentType" => $parentType, "parentId" => $parentId, "status" => $status);
			$allCount["actions"][$status] = PHDB::find (Action::COLLECTION, $query, array());

			if($myId != false){
				$query = array( "parentType" => $parentType, "parentId" => $parentId, "creator" => $myId);
				$allCount["actions"]["mine"] = PHDB::find (Action::COLLECTION, $query, array());
			}
		}

			//check roles actions
			$allCount["actions"] = self::checkRoleAccess($allCount["actions"]);
			foreach (array("todo", "done", "disabled", "mine") as $status) {
				//echo isset($allCount["proposals"][$status]) ? "true" : "false";
				if(isset($allCount["actions"][$status]))
					$allCount["actions"][$status] = count($allCount["actions"][$status]);
			}

		//var_dump($allCount); exit;

		return $allCount;

		/*$res["actionList"] = PHDB::findAndSort (Action::COLLECTION, $query, 
													array("status" => -1, "dateEnd" => 1));*/
	}

	

	public static function updateStatusProposal($parentType, $parentId){
		
		$query = array( "parentType" => $parentType, 
						"parentId" => $parentId, 
						"status" => array('$in'=>array("amendable", "tovote")));

		$proposalList = PHDB::findAndSort (Proposal::COLLECTION, $query, array());

		foreach ($proposalList as $key => $proposal) {
			//amendement TO tovote
			if(@$proposal["amendementDateEnd"] && @$proposal["amendementActivated"] == true && 
				$proposal["status"] == "amendable"){
				$amDateEnd = strtotime($proposal["amendementDateEnd"]);
				$today = time();

				if($amDateEnd < $today){
					$proposalList[$key]["status"] = "tovote";
					//Element::updateField(Proposal::COLLECTION, $key, "status", "tovote");
					PHDB::update(Proposal::COLLECTION,
						array("_id" => new MongoId($key)),
			            array('$set' => array("status"=> "tovote"))
			            );
					/* TODO : Add notification */
				}
			}

			//tovote TO closed
			if(@$proposal["voteDateEnd"] && $proposal["voteActivated"] == true && $proposal["status"] == "tovote"){
				$voteDateEnd = strtotime($proposal["voteDateEnd"]);
				$today = time(); 

				if($voteDateEnd < $today){
					//var_dump($voteDateEnd); var_dump($today);

					$resolution = Proposal::getById($key);
					$voteRes = Proposal::getAllVoteRes($resolution);
					//var_dump(@$voteRes); exit;


					$adopted = 	@$voteRes["up"] && 
								@$voteRes["up"]["percent"] && 
								$voteRes["up"]["percent"] > intval(@$resolution["majority"]);
					
					$resolution["status"] = $adopted ? "adopted" : "refused";

					if(@$resolution["answers"]) $resolution["status"] = "adopted";

					$resolutionExist = Resolution::getById($key);
					
					if(!$resolutionExist){ //} && $proposal["parentType"] == News::COLLECTION){
						PHDB::insert(Resolution::COLLECTION, $resolution);
						self::afterSave($resolution, Resolution::COLLECTION);
					}

					//var_dump($proposal); exit;
					$proposalList[$key]["idResolution"] = $proposal["_id"];
					$proposalList[$key]["status"] = "resolved";
					PHDB::update(Proposal::COLLECTION,
						array("_id" => new MongoId($key)),
			            array('$set' => array("status"=> "resolved", "idResolution" => $proposal["_id"]))
			            );

					//moderation news
					if($proposal["parentType"] == News::COLLECTION){
						if($resolution["status"]=="adopted"){
							//error_log("IS AN ABUSE ".$proposal["parentId"]);
							$res = PHDB::update ( 	News::COLLECTION , 
													array( "_id" => new MongoId($proposal["parentId"])), 
													array( '$set' => array("isAnAbuse"=>true)));
						}else{
							error_log("IS NOT AN ABUSE ".$proposal["parentId"]);
							$res = PHDB::update ( 	News::COLLECTION , 
													array( "_id" => new MongoId($proposal["parentId"])), 
													array( '$unset' => array("reportAbuse"=>null,
																			 "reportAbuseCount"=>null)));

						}
					}
				}
			}
		}
		return $proposalList;
	}


	public static function formatDateBeforeSaving($date) { 
		$date = DateTime::createFromFormat('d/m/Y H:s', $date); //var_dump($date); exit;
		$date = new MongoDate($date->getTimestamp());
		return $date;
	}


	public static function afterSave($params, $type){ error_log("self::afterSave : ".@$type);
		$id = (string)$params['_id'];
		$name = @$params["name"] ? $params["name"] : @$params["title"];
		//ActivityStream::saveActivityHistory(ActStr::VERB_CREATE, @$params["parentId"], @$params["parentType"], $type, $name);
		// Notification::constructNotification(ActStr::VERB_ADD, 
  //                   array("id" => Yii::app()->session["userId"],"name"=> Yii::app()->session["user"]["name"]), 
  //                   array(  "type"=>@$params['parentType'] ? $params['parentType'] : "",
  //                           "id"=> @$params['parentId'] ? $params['parentId'] : ""), 
  //                   array("id"=>$id,"type"=> $type), $type
  //               );
		$targetId = @$params["parentId"];
		$targetType = @$params["parentType"];
		$scopeType = ($targetType != Person::COLLECTION) ? "private" : "restricted";

		//si c'est une proposal sans room, qui n'est pas une modération (!= News::COLLECTION)
		//on parle d'un sondage

		$object = array("type" => $type,
						"id" => $id,
						"displayName" => $name);

		if(!isset($params["idParentRoom"]) && 
				$type == Proposal::COLLECTION && 
				@$params["parentType"] != News::COLLECTION)
			$object["isSurvey"] = true;

		$buildArray = array(
				"type" => ActivityStream::COLLECTION,
				"verb" => ActStr::VERB_PUBLISH,
				"target" => array("id" => $targetId,
								  "type"=> $targetType),
				"author" => Yii::app()->session["userId"],
				"object" => $object,
				"scope" => array("type"=>$scopeType),
			    "created" => new MongoDate(time()),
				"sharedBy" => array(array(	"id" => Yii::app()->session["userId"],
											"type"=> "citoyens",
											//"comment"=>@$comment,
											"updated" => new MongoDate(time()))),
			);

		if(!empty($params["address"]) ){
			$buildArray["scope"]["type"]="public";
			$address = null ;
			if( !empty( $params["address"] )){
	        	$localityId = $params["address"]["localityId"];
	        	$address = $params["address"];
	        }

	        if( isset( $params["geo"] ))
				$geo = $params["geo"];

			if(!@$localityId){

		        $author=Person::getSimpleUserById(Yii::app()->session["userId"]);
		        
		        if(@$author["address"] && @$author["address"]["localityId"]){
			        $localityId=$author["address"]["localityId"];
		        	$address=$author["address"];
		        	if(!@$geo)
		        		$geo = $author["geo"];
	        	}
			}

			$scope = array( "parentId"=>$localityId,
							"parentType"=>City::COLLECTION,
							"name"=>$address["addressLocality"],
							"geo" => $geo
						);
			if (!(empty($address["postalCode"]))) {
				$scope["postalCode"] = $address["postalCode"];
			}

			$scope = array_merge($scope, Zone::getLevelIdById($localityId, $address, City::COLLECTION) ) ;

			$buildArray["scope"]["localities"][] = $scope ;
		}

			//$params=ActivityStream::buildEntry($buildArray);
			$newsShared=ActivityStream::addEntry($buildArray);
	}

	static public function getCountNotif(){

		$userId = @Yii::app()->session['userId'];
		$me = Person::getById($userId);
		$memberOfOrga = (@$me["links"] && @$me["links"]["memberOf"]) ? $me["links"]["memberOf"] : [];
		$memberOfProject = (@$me["links"] && @$me["links"]["projects"]) ? $me["links"]["projects"] : [];
		$memberOf = array_merge($memberOfOrga, $memberOfProject);

		$res = array(); $count = 0;
		foreach ($memberOf as $id => $element) {
			$allElement = Element::getByTypeAndId($element["type"], $id);

			$amendable = Cooperation::getCoopData($element["type"], $id, "proposal", "amendable");
			$tovote = Cooperation::getCoopData($element["type"], $id, "proposal", "tovote");
			$actions = Cooperation::getCoopData($element["type"], $id, "action", "todo");
			$adopted = Cooperation::getCoopData($element["type"], $id, "resolution", "adopted");
			$refused = Cooperation::getCoopData($element["type"], $id, "resolution", "refused");

			foreach ($tovote["proposalList"] as $key => $proposal) { //var_dump($proposal); exit;
				$hasVote = @$proposal["votes"] ? Cooperation::userHasVoted($userId, $proposal["votes"]) : false;
				//echo $hasVote ? "true" : "false"; exit;
				if($hasVote) unset($tovote["proposalList"][$key]); 
			}

			foreach ($actions["actionList"] as $key => $action) { //var_dump($proposal); exit;
				$participate = @$action["links"] ? @$action["links"]["contributors"][Yii::app()->session['userId']] : false;
				if(!$participate && sizeof(@$action["links"]["contributors"])>0) 
					unset($actions["actionList"][$key]); 
			}


			foreach ($adopted["resolutionList"] as $key => $resolution) { //var_dump($proposal); exit;
				$dayDiff = Translate::dayDifference($resolution["created"], "timestamp");
				if($dayDiff > 7) unset($adopted["resolutionList"][$key]); 
			}

			foreach ($refused["resolutionList"] as $key => $resolution) { //var_dump($proposal); exit;
				$dayDiff = Translate::dayDifference($resolution["created"], "timestamp");
				if($dayDiff > 7) unset($refused["resolutionList"][$key]); 
			}

			$resolved["resolutionList"] = array_merge($adopted["resolutionList"], $refused["resolutionList"]);


			$count += count($amendable["proposalList"]) + count($tovote["proposalList"]) + 
					 count($actions["actionList"]) + count($resolved["resolutionList"]);
		}

		return $count;
	}


}
