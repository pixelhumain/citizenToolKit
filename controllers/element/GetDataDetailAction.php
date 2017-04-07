<?php

class GetDataDetailAction extends CAction {
/**
* Dashboard Organization
*/
    public function run($type, $id, $dataName) { 
    	//$controller=$this->getController();

    	$contextMap = array();
		$element = Element::getByTypeAndId($type, $id);

		if($dataName == "follows"){
			if(isset($element["links"]["follows"])){
				foreach ($element["links"]["follows"] as $keyFollow => $value){
					//$need = Need::getSimpleNeedById($keyFollow);
					$follow = Element::getByTypeAndId($value["type"], $keyFollow);
					$follow["type"] = $value["type"];
	           		$contextMap[$keyFollow] = $follow;
				}
			}
		}

		if($dataName == "followers"){
			if(isset($element["links"]["followers"])){
				foreach ($element["links"]["followers"] as $keyFollow => $value){
					//$need = Need::getSimpleNeedById($keyFollow);
					$follow = Element::getByTypeAndId($value["type"], $keyFollow);
					$follow["type"] = $value["type"];
	           		$contextMap[$keyFollow] = $follow;
				}
			}
		}

		if($dataName == "links"){
			$links=@$element["links"];
			$contextMap = Element::getAllLinks($links,$type, $id);
		}

		if($dataName == "events"){ //var_dump($element["links"]); exit;
			if(isset($element["links"]["events"])){
				foreach ($element["links"]["events"] as $keyEv => $valueEv) {
					 $event = Event::getSimpleEventById($keyEv);
					 //var_dump($event); exit;
					 if(!empty($event)){
					 	$event["typeEvent"] = @$event["type"];
						$event["type"] = "events";
						$event["typeSig"] = Event::COLLECTION;
						$contextMap[$keyEv] = $event;
					 }
				}
			}
		}

		if($dataName == "projects"){
			foreach ($element["links"]["projects"] as $keyProj => $valueProj) {
				$project = Project::getPublicData($keyProj);
				$project["type"] = "projects";
				$project["typeSig"] = Project::COLLECTION;
           		$contextMap[$keyProj] = $project;
			}
		}

		if($dataName == "needs"){
			if(isset($element["links"]["needs"])){
				foreach ($element["links"]["needs"] as $keyNeed => $value){
					$need = Need::getSimpleNeedById($keyNeed);
	           		$contextMap[$keyNeed] = $need;
				}
			}
		}


		if($dataName == "poi"){
			$contextMap = Poi::getPoiByIdAndTypeOfParent($id, $type);

		}

		return Rest::json($contextMap);
		Yii::app()->end();


		
	}
}



?>