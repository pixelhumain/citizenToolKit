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
		if(empty($dataId) || $type == Room::CONTROLLER) $page = "menuRoom";
		else {
			if($type == Proposal::CONTROLLER) $page = "proposal";
			if($type == Action::CONTROLLER) $page = "action";
			//if($type == Resolution::CONTROLLER) $page = "resolution";
		}

		$params = Cooperation::getCoopData($parentType, $parentId, $type, $status, $dataId);

		echo $controller->renderPartial($page, $params, true);
	}
}
