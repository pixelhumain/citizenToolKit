<?php

class GetCoopDataAction extends CAction {

	public function run() { 

		$parentType = @$_POST["parentType"];
		$parentId 	= @$_POST["parentId"];
		$type 		= @$_POST["type"];
		$status 	= @$_POST["status"];
		$dataId 	= @$_POST["dataId"];

		$controller=$this->getController();

		$page = "";
		if($type == "menucoop") {
			$res = array("element"=>array("_id"=>$parentId), 
										 "type"=>$parentType);
			echo $controller->renderPartial("menuCoop", $res, true);
			exit;
		}

		if(empty($dataId) || $type == Room::CONTROLLER) $page = "menuRoom";
		else {
			if($type == Proposal::CONTROLLER || $type == Proposal::COLLECTION){
				$page = "proposal"; $type = Proposal::CONTROLLER;
			} 
			if($type == Action::CONTROLLER || $type == Action::COLLECTION){
				$page = "action"; $type = Action::CONTROLLER;
			} 
			//if($type == Resolution::CONTROLLER) $page = "resolution";
		}

		if(empty($dataId) && $type == Room::CONTROLLER) $page = "roomList";
		
		$res = Cooperation::getCoopData($parentType, $parentId, $type, $status, $dataId);
		//var_dump($res);
		echo $controller->renderPartial($page, $res, true);
	}
}
