<?php

class GetCoopDataAction extends CAction {

	public function run() { 

		$parentType = @$_POST["parentType"];
		$parentId 	= @$_POST["parentId"];
		$type 		= @$_POST["type"];
		$status 	= @$_POST["status"];
		$dataId 	= @$_POST["dataId"];

		$json 	= @$_POST["json"];

		$controller=$this->getController();

		/*if($type == Room::CONTROLLER && ($dataId == "undefined" || $dataId == "")){
			echo "nop"; return "";
			Yii::app()->end();
		}*/

		$auth = Authorisation::canParticipate(Yii::app()->session['userId'], $parentType, $parentId);
		$openData = Authorisation::isOpenData(Yii::app()->session['userId'], $parentType, $parentId);


		$page = "";
		if($type == "menucoop") {
			$res = array("element"=>array("_id"=>$parentId), 
						 "type"=>$parentType,
						 "parentType" => $parentType,
						 "parentId" => $parentId,);
			
			//block l'accès si le user n'est pas autorisé à participer, et que l'element n'est pas openData
			if(!$auth && !$openData)
				$res["access"] = "deny";

			echo $controller->renderPartial("menuCoop", $res, true);
			exit;
		}

		//block l'accès si le user n'est pas autorisé à participer, et que l'element n'est pas openData
		if(!$auth && !$openData && $parentType != "news") {
			$res = array("access" => "deny");
		}else{
			$res = Cooperation::getCoopData($parentType, $parentId, $type, $status, $dataId);
		}

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

		        if(!empty($res["action"]["parentIdSurvey"]))
		        	$res["action"]["parentSurvey"] = Form::getByIdMongo($res["action"]["parentIdSurvey"]);
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

		if(empty($dataId) && $parentType == News::CONTROLLER) {
			$page = "moderation";
			//$res["proposal"] = @$res["proposalList"][0];
			//unset($res["proposalList"]);
		}

		//var_dump($res); exit;
		
		if(@$json == "false"){
			echo $controller->renderPartial($page, $res, true);
		}else{
			return Rest::json($res);
			Yii::app()->end();
		}

	}
}
