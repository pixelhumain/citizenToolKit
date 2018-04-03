<?php

class SaveVoteAction extends CAction {

	public function run() { 

		$parentType = @$_POST["parentType"];
		$parentId 	= @$_POST["parentId"];
		$voteValue	= @$_POST["voteValue"];
		$idAmdt 	= @$_POST["idAmdt"];
		$json 		= @$_POST["json"];
		$moderation = @$_POST["moderation"];

		$controller=$this->getController();

		$myId = Yii::app()->session["userId"];
		if(!@$myId) exit;

		$proposal = PHDB::findOne(Proposal::COLLECTION, array("_id" => new MongoId($parentId)));
		
		//check if status is TOVOTE and if voteDateEnd is not past
		if(self::checkVoteAllowed($proposal, $parentType) == false){
			$params = Cooperation::getCoopData(null, null, "proposal", null, $parentId);
			$params["msgController"] = 
				Yii::t("cooperation", "You are not allowed to vote for this proposal. Current status : ").
				$proposal["status"];
			echo $controller->renderPartial("proposal", $params, true); exit;
		}
		

		$allVotes = @$proposal["votes"] ? $proposal["votes"] : array();
		if($parentType == "amendement")
			$allVotes = @$proposal["amendements"][$idAmdt]["votes"] ? $proposal["amendements"][$idAmdt]["votes"] : array();
		
		$hasVote = Cooperation::userHasVoted($myId, $allVotes);

		$root = $parentType != "amendement" ? "votes" : "amendements.".@$idAmdt.".votes";
		if($hasVote !== false){
			if($hasVote == $voteValue){
				$page = "proposal";
				$params = Cooperation::getCoopData(null, null, "proposal", null, $parentId);
				$params["msgController"] = Yii::t("cooperation", "You already voted the same way")." ".Yii::t("cooperation", $hasVote);

				if(@$json == "false"){
					echo $controller->renderPartial($page, $params, true);
					Yii::app()->end();
				}else{
					$params["result"] = false;
					$params["msg"] = $params["msgController"];
					return Rest::json($params);
					Yii::app()->end();
				}
			}else{
				$withoutMe = $allVotes[$hasVote];
				$pos = array_search($myId, $withoutMe);
				unset($withoutMe[$pos]);
							
				PHDB::update(Proposal::COLLECTION,
					array("_id" => new MongoId($parentId)),
		            array('$set' => array($root.".".$hasVote=> $withoutMe))
		        );

			}
		}

		$votes = isset($allVotes[$voteValue]) ? $allVotes[$voteValue] : array();
		$votes[] = $myId;

		PHDB::update(Proposal::COLLECTION,
			array("_id" => new MongoId($parentId)),
            array('$set' => array($root.".".$voteValue=> $votes))
        );
        
        //pas de notif pour la modération
        if($proposal["parentType"] != News::COLLECTION && isset($proposal["idParentRoom"]))
		Notification::constructNotification ( 	ActStr::VERB_VOTE, array("id" => Yii::app()->session["userId"],
												"name"=> Yii::app()->session["user"]["name"]), 
												array("type"=>$proposal["parentType"],"id"=>$proposal["parentId"]),
												array( "type"=>Proposal::COLLECTION,"id"=> $parentId ) );
		$page = "proposal";


		$params = Cooperation::getCoopData(null, null, "proposal", null, $parentId);

		if(@$moderation == "true") {
			$page = "moderation";
			$params["news"] = News::getById($proposal["parentId"]);
		}
		
		if(@$json == "false"){
			echo $controller->renderPartial($page, $params, true);
		}else{
			$params["result"] = true;
			$params["msg"] = "Element has been updated";
			return Rest::json($params);
			Yii::app()->end();
		}

	}


	//check if status is TOVOTE and if voteDateEnd is not past
	private static function checkVoteAllowed($proposal, $parentType){
		if($proposal["status"] == "amendable" && $parentType == "amendement") return true;		
		else if($proposal["status"] != "tovote") return false;
		else if(@$proposal["voteDateEnd"]){
				$voteDateEnd = strtotime($proposal["voteDateEnd"]);
				$today = time(); 
				if($voteDateEnd < $today) return false;
		}
		return true;
	}

}
