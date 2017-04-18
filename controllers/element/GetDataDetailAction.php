<?php

class GetDataDetailAction extends CAction {
/**
* Dashboard Organization
*/
    public function run($type, $id, $dataName) { 
    	//$controller=$this->getController();

    	$contextMap = array();
		$element = Element::getByTypeAndId($type, $id);

		if($dataName == "follows" || $dataName == "followers" || 
			$dataName == "members" || $dataName == "attendees" ||
			$dataName == "contributors" ){
			if(isset($element["links"][$dataName])){
				foreach ($element["links"][$dataName] as $keyLink => $value){
					$link = Element::getByTypeAndId($value["type"], $keyLink);
					$link["type"] = $value["type"];
					if(!empty($value["isInviting"]))
						$link["isInviting"] = $value["isInviting"];
	           		$contextMap[$keyLink] = $link;
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
			if(isset($element["links"]["projects"]))
			foreach ($element["links"]["projects"] as $keyProj => $valueProj) {
				$project = Project::getPublicData($keyProj);
				$project["type"] = "projects";
				$project["typeSig"] = Project::COLLECTION;
           		$contextMap[$keyProj] = $project;
			}
		}
		if($dataName == "organizations"){
			if(isset($element["links"]["memberOf"]))
			foreach ($element["links"]["memberOf"] as $keyOrga => $valueOrga) {
				$orga = Organization::getPublicData($keyOrga);
				$orga["typeOrga"] = $orga["type"];
				$orga["type"] = "organization";
				$orga["typeSig"] = Organization::COLLECTION;
           		$contextMap[$keyOrga] = $orga;
			}
		}

		if($dataName == "classified"){
			$contextMap = Classified::getClassifiedByCreator($id);
		}


		if($dataName == "poi"){
			$contextMap = Poi::getPoiByIdAndTypeOfParent($id, $type);
		}


		if($dataName == "collections"){
			if(@$element["collections"]){
				$collections = $element["collections"];
				foreach ($collections as $col => $value) {
					$collections[$col] = Collection::get($id, null, $col);
				}
				$contextMap = $collections;
			}
		}





		if($dataName == "liveNow"){

			//EVENTS-------------------------------------------------------------------------------
			$query = array("startDate" => array( '$gte' => new MongoDate( time() ) ));
			$query = Search::searchLocality($_POST, $query);

			$events = PHDB::findAndSortAndLimitAndIndex( Event::COLLECTION,
							$query,
							array("startDate"=>1), 5);
			foreach ($events as $key => $value) {
				$events[$key]["type"] = "events";
				$events[$key]["typeSig"] = "events";
				if(@$value["startDate"]) {
					//var_dump(@$value["startDate"]);
					$events[$key]["updatedLbl"] = Translate::pastTime(@$value["startDate"]->sec,"timestamp");
		  		}
		  	}
		  	$contextMap = array_merge($contextMap, $events);
			

			//CLASSIFIED-------------------------------------------------------------------------------
			$query = array();
			$query = Search::searchLocality($_POST, $query);
			
			$classified = PHDB::findAndSortAndLimitAndIndex( Classified::COLLECTION, $query,
							array("updated"=>-1), 5);

			foreach ($classified as $key => $value) {
				$classified[$key]["type"] = "classified";
				$classified[$key]["typeSig"] = "classified";
				if(@$value["updated"]) {
					$classified[$key]["updatedLbl"] = Translate::pastTime(@$value["updated"],"timestamp");
		  		}
		  	}
		  	$contextMap = array_merge($contextMap, $classified);
			
		  	//POI-------------------------------------------------------------------------------
			$query = array();
			$query = Search::searchLocality($_POST, $query);
			$pois = PHDB::findAndSortAndLimitAndIndex( Poi::COLLECTION, $query,
							array("updated"=>-1), 5);

			foreach ($pois as $key => $value) {
				$pois[$key]["type"] = "poi";
				$pois[$key]["typeSig"] = "poi";
				if(@$value["updated"]) {
					$pois[$key]["updatedLbl"] = Translate::pastTime(@$value["updated"],"timestamp");
		  		}
		  	}
		  	$contextMap = array_merge($contextMap, $pois);
			
			echo $this->getController()->renderPartial($_POST['tpl'], 
				array("result"=>$contextMap, "scope"=>@$_POST['searchLocalityDEPARTEMENT'][0]));
			Yii::app()->end();
		}



		return Rest::json($contextMap);
		Yii::app()->end();


		
	}
}



?>