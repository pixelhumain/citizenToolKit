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

		$res["post"]["type"] = $type;
		$res["post"]["status"] = $status;
		$res["post"]["parentId"] = $parentId;
		$res["post"]["parentType"] = $parentType;

		return $res;
	}

	public static function userHasVoted($userId, $obj){
		foreach ($obj as $keyVal=>$arr) {
			foreach ($arr as $keyId) {
				if($keyId == $userId) return $keyVal;
			}
		}
		return false;
	}

	public static function getAllCount($parentType, $parentId){

		$myId = @Yii::app()->session['userId'] ? Yii::app()->session['userId'] : false;
		$allCount = array();
		foreach (array("tovote", "amendable", "resolved", "closed", "disabled") as $status) {
			$query = array( "parentType" => $parentType, "parentId" => $parentId, "status" => $status);
			$allCount["proposals"][$status] = PHDB::count (Proposal::COLLECTION, $query, array());
			
			if($myId != false){
				$query = array( "parentType" => $parentType, "parentId" => $parentId, "creator" => $myId);
				$allCount["proposals"]["mine"] = PHDB::count (Proposal::COLLECTION, $query, array());
			}
		}

		foreach (array("todo", "done", "disabled") as $status) {
			$query = array( "parentType" => $parentType, "parentId" => $parentId, "status" => $status);
			$allCount["actions"][$status] = PHDB::count (Action::COLLECTION, $query, array());

			if($myId != false){
				$query = array( "parentType" => $parentType, "parentId" => $parentId, "creator" => $myId);
				$allCount["actions"]["mine"] = PHDB::count (Action::COLLECTION, $query, array());
			}
		}


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
					$resolutionExist = Resolution::getById($key);
					
					if(!$resolutionExist && $proposal["parentType"] == News::COLLECTION){
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
							error_log("IS AN ABUSE ".$proposal["parentId"]);
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
		Notification::constructNotification(ActStr::VERB_ADD, 
                    array("id" => Yii::app()->session["userId"],"name"=> Yii::app()->session["user"]["name"]), 
                    array(  "type"=>@$params['parentType'] ? $params['parentType'] : "",
                            "id"=> @$params['parentId'] ? $params['parentId'] : ""), 
                    array("id"=>$id,"type"=> $type), $type
                );
		$targetId = @$params["parentId"];
		$targetType = @$params["parentType"];

		$object = array("type" => $type,
						"id" => $id,
						"displayName" => $name);

		$buildArray = array(
				"type" => ActivityStream::COLLECTION,
				"verb" => ActStr::VERB_CREATE,
				"target" => array("id" => $targetId,
								  "type"=> $targetType),
				"author" => Yii::app()->session["userId"],
				"object" => $object,
				"scope" => array("type"=>"private"),
			    "created" => new MongoDate(time()),
				"sharedBy" => array(array(	"id" => Yii::app()->session["userId"],
											"type"=> "citoyens",
											//"comment"=>@$comment,
											"updated" => new MongoDate(time()))),
			);

			//$params=ActivityStream::buildEntry($buildArray);
			$newsShared=ActivityStream::addEntry($buildArray);
	}


	public static function openModerationOnNews($newsId){
		PHDB::insert(Proposal::COLLECTION, array(

		));
	}
}
