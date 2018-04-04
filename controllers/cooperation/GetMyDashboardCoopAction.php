<?php

class GetMyDashboardCoopAction extends CAction {

	public function run() { 

		if(!@Yii::app()->session['userId']) {
			return Rest::json(array("result"=>"you are not logged"));
			Yii::app()->end();
		}

		$userId = @Yii::app()->session['userId'];
		$me = Person::getById($userId);
		$memberOfOrga = (@$me["links"] && @$me["links"]["memberOf"]) ? $me["links"]["memberOf"] : [];
		$memberOfProject = (@$me["links"] && @$me["links"]["projects"]) ? $me["links"]["projects"] : [];

		$memberOfOrga = @$me["links"]["memberOf"];
		$memberOfProject = @$me["links"]["projects"];

		if(is_array($memberOfOrga) && is_array($memberOfProject))
			$memberOf = array_merge($memberOfOrga, $memberOfProject);
		else if(is_array($memberOfOrga)) $memberOf = $memberOfOrga;
		else if(is_array($memberOfProject)) $memberOf = $memberOfProject;

		$res = array();
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

				//var_dump($action["links"]["contributors"]); exit;
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

			if( !empty($amendable["proposalList"]) || !empty($tovote["proposalList"]) || 
				!empty($actions["actionList"]) || !empty($resolved["resolutionList"]))
				$res[$element["type"]][] = array("id" => $id,
												 "name" => @$allElement["name"],
												 "type" => @$element["type"],
												 "amendable"=>$amendable,
												 "tovote"=>$tovote,
												 "actions"=>$actions,
												 "resolved"=>$resolved,
												);
		}
		//return Rest::json($res);
		//Yii::app()->end();
		
		$json = "false"; //true; //@$_POST["json"];

		$controller=$this->getController();

		//var_dump($memberOf); exit;
		
		if(@$json == "false"){
			echo $controller->renderPartial("dashboard", $res, true);
		}else{
			return Rest::json($res);
			Yii::app()->end();
		}

	}
}
