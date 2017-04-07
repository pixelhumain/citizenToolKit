<?php

class GetDataDetailAction extends CAction {
/**
* Dashboard Organization
*/
    public function run($type, $id, $dataName) { 
    	//$controller=$this->getController();

    	$contextMap = array();
		$element = Element::getByTypeAndId($type, $id);

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