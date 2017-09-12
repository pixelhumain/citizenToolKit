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

		$res = Cooperation::getCoopData($parentType, $parentId, $type, $status, $dataId);

		if(empty($dataId) || $type == Room::CONTROLLER) {
			$page = "menuRoom";
			$res["parentType"] = @$_POST["parentType"];
			$res["parentId"] 	= @$_POST["parentId"];
		}
		else {
			if($type == Proposal::CONTROLLER || $type == Proposal::COLLECTION){
				$page = "proposal"; 
				$type = Proposal::CONTROLLER;
			} 
			if($type == Action::CONTROLLER || $type == Action::COLLECTION){
				$page = "action"; 
				$type = Action::CONTROLLER;

				$res["contributors"] = array();
		        $res["countStrongLinks"] = 0;
		        if(@$res["action"]["links"]["contributors"])
		        {
		            $res["countStrongLinks"]=count($res["action"]["links"]["contributors"]);
		            foreach ($res["action"]["links"]["contributors"] as $uid => $e) 
		            {
		            	var_dump($uid);
		                $citoyen = Person::getPublicData($uid);
		                if(!empty($citoyen)){
		                    $citoyen["type"]=Person::COLLECTION;
		                    $profil = Document::getLastImageByKey($uid, Person::COLLECTION, Document::IMG_PROFIL);
		                    if($profil !="")
		                        $citoyen["imagePath"] = $profil;
		                    array_push( $res["contributors"] , $citoyen);
		                }
		            }
		        }
			} 
			if($type == Resolution::CONTROLLER || $type == Resolution::COLLECTION){
				$page = "resolution"; 
				$type = Resolution::CONTROLLER;
			} 
			//if($type == Resolution::CONTROLLER) $page = "resolution";
		}

		
		//var_dump($res);exit;

		if(empty($dataId) && $type == Room::CONTROLLER) {
			$page = "roomList";
			$res["parentType"] = @$_POST["parentType"];
			$res["parentId"] 	= @$_POST["parentId"];
		}
		
		echo $controller->renderPartial($page, $res, true);
	}
}
