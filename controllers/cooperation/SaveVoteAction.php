<?php

class SaveVoteAction extends CAction {

	public function run() { 

		$parentType = @$_POST["parentType"];
		$parentId 	= @$_POST["parentId"];
		$voteValue	= @$_POST["voteValue"];
		$idAmdt 	= @$_POST["idAmdt"];

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
		if($hasVote != false){
			if($hasVote == $voteValue){
				$page = "proposal";
				$params = Cooperation::getCoopData(null, null, "proposal", null, $parentId);
				$params["msgController"] = Yii::t("cooperation", "You already voted the same way")." ".Yii::t("cooperation", $hasVote);
				echo $controller->renderPartial($page, $params, true);
				exit;
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

		//var_dump($allVotes[$voteValue]); exit;

		//$page = "";
		PHDB::update(Proposal::COLLECTION,
			array("_id" => new MongoId($parentId)),
            array('$set' => array($root.".".$voteValue=> $votes))
        );

		$page = "proposal";
		$params = Cooperation::getCoopData(null, null, "proposal", null, $parentId);

		echo $controller->renderPartial($page, $params, true);
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
