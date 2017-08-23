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
		
		$allVotes = @$proposal["votes"] ? $proposal["votes"] : array();
		if($parentType == "amendement")
			$allVotes = @$proposal["amendements"][$idAmdt]["votes"] ? $proposal["amendements"][$idAmdt]["votes"] : array();
		
		$hasVote = Cooperation::userHasVoted($myId, $allVotes);

		$root = $parentType != "amendement" ? "votes" : "amendements.".@$idAmdt.".votes";
		//var_dump($allVotes);
		//echo $hasVote == false ? "false" : $hasVote; exit;
		if($hasVote != false){
			if($hasVote == $voteValue){
				echo "You already voted the same way"; exit;
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

		$votes = @$proposal["votes"][$voteValue] ? $proposal["votes"][$voteValue] : array();
		$votes[] = $myId;

		//$page = "";
		PHDB::update(Proposal::COLLECTION,
			array("_id" => new MongoId($parentId)),
            array('$set' => array($root.".".$voteValue=> $votes))
        );

		$page = "proposal";
		$params = Cooperation::getCoopData(null, null, "proposal", null, $parentId);

		echo $controller->renderPartial($page, $params, true);
	}
}
