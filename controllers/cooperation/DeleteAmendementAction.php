<?php

class DeleteAmendementAction extends CAction {

	public function run() { 

		$numAm 		= @$_POST["numAm"];
		$idProposal = @$_POST["idProposal"];
		$json = @$_POST["json"];
		
		$controller=$this->getController();

		$myId = Yii::app()->session["userId"];
		if(!@$myId) exit;

		$proposal = PHDB::findOne(Proposal::COLLECTION, array("_id" => new MongoId($idProposal)));
		
		//check if status is TOVOTE and if voteDateEnd is not past
		if($proposal["status"] == "amendable" && @$proposal["amendements"]){
			/*$amWithout = array();
			foreach ($proposal["amendements"] as $key => $am) {
				if($key != $numAm) $amWithout[$key] = $am;
			}*/

			//var_dump($proposal["amendements"]); 

			if($myId == $proposal["amendements"][$numAm]["idUserAuthor"])
				unset($proposal["amendements"][$numAm]);
			else{
				$params["result"] = false;
				$params["msg"] = "Error : your are not the author of this amendement";
				return Rest::json($params);
				Yii::app()->end(); exit;
			}
			//var_dump($proposal["amendements"]); exit;

			PHDB::update(Proposal::COLLECTION,
					array("_id" => new MongoId($idProposal)),
		            array('$set' => array("amendements"=> $proposal["amendements"]))
		        );

			$params["result"] = true;
			$params["msg"] = "Element has been updated";
			
		}else{
			$params["result"] = false;
			$params["msg"] = "Error : this proposal is not amendable.";
		}
		

		$page = "proposal";
		$params = Cooperation::getCoopData($proposal["parentType"], $proposal["parentId"], "proposal", null, $idProposal);

		if(@$json == "false"){
			echo $controller->renderPartial($page, $params, true);
		}else{
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
