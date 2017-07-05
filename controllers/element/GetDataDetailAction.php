<?php

class GetDataDetailAction extends CAction {
/**
* Dashboard Organization
*/
    public function run($type, $id, $dataName) { 
    	//$controller=$this->getController();

    	$contextMap = array();
		$element = @$type != "0" ? Element::getByTypeAndId(@$type, @$id) : null;

		if($dataName == "follows" || $dataName == "followers" || 
			$dataName == "members" || $dataName == "attendees" ||
			$dataName == "contributors" || $dataName =="guests"){
			$connector=$dataName;
			if($dataName=="guests"){
				$connector=Element::$connectTypes[@$type];
			}
			if(isset($element["links"][$connector])){
				foreach ($element["links"][$connector] as $keyLink => $value){
					try {
						$link = Element::getByTypeAndId($value["type"], $keyLink);
					} catch (CTKException $e) {
						error_log("The element ".$id."/".$type." has a broken link : ".$keyLink."/".$value["type"]);
						continue;
					}
					if($dataName=="guests" && @$value["isInviting"]){
						$link["type"] = $value["type"];
						$link["isInviting"] = $value["isInviting"];
						$contextMap[$keyLink] = $link;
					}else if($dataName!="guests" && !@$value["isInviting"]){
						$link = Element::getByTypeAndId($value["type"], $keyLink);
						if($value["type"]==Person::COLLECTION){
							$link["statusLink"]=[];
							if(@$value[Link::TO_BE_VALIDATED])
								$link["statusLink"][Link::TO_BE_VALIDATED]=true;
							if(@$value[Link::IS_ADMIN])
								$link["statusLink"][Link::IS_ADMIN]=true;
							if(@$value[Link::IS_ADMIN_PENDING])
								$link["statusLink"][Link::IS_ADMIN_PENDING]=true;
						}
						$link["type"] = $value["type"];
						$contextMap[$keyLink] = $link;
					}
				}
			}
		}


		if($dataName == "links"){
			$links=@$element["links"];
			$contextMap = Element::getAllLinks($links,$type, $id);
		}

		if($dataName == "events"){ //var_dump($element["links"]); exit;
			if(isset($element["links"]["events"])){

				foreach (array_reverse($element["links"]["events"]) as $keyEv => $valueEv) {
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
			if(isset($element["links"]["subEvents"])){
				foreach (array_reverse($element["links"]["subEvents"]) as $keyEv => $valueEv) {
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
			$contextMap = Element::getByIdAndTypeOfParent(Classified::COLLECTION, $id, $type, array("updated"=>-1));
		}


		if($dataName == "poi"){
			$contextMap = Poi::getPoiByIdAndTypeOfParent($id, $type, array("updated"=>-1));
			foreach ($contextMap as $key => $value) {
				$contextMap[$key]["typePoi"] = @$value["type"];
			}
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
			$post = $_POST; 
			if( empty($_POST["searchLocalityCITYKEY"]) && 
				(empty($_POST["searchLocalityDEPARTEMENT"]) || $_POST["searchLocalityDEPARTEMENT"][0] == "" || 
				 $_POST["searchLocalityDEPARTEMENT"][0] == "undefined") && 
				isset($element["address"])){
				$city = City::getDepAndRegionByInsee($element["address"]["codeInsee"]);
				$post["searchLocalityDEPARTEMENT"] = array($city["depName"]);
			}

			//EVENTS-------------------------------------------------------------------------------
			$query = array("startDate" => array( '$gte' => new MongoDate( time() ) ));

			if(@$type!="0" || !empty($post["searchLocalityCITYKEY"]))
			$query = Search::searchLocality($post, $query);

			$events = PHDB::findAndSortAndLimitAndIndex( Event::COLLECTION,
							$query,
							array("startDate"=>1), 10);

			foreach ($events as $key => $value) {
				$events[$key]["type"] = "events";
				$events[$key]["typeSig"] = "events";
				if(@$value["startDate"]) {
					$events[$key]["updatedLbl"] = Translate::pastTime(@$value["startDate"]->sec,"timestamp");
		  		}
		  	}
		  	$contextMap = array_merge($contextMap, $events);
			

			//CLASSIFIED-------------------------------------------------------------------------------
			$query = array();
			if(@$type!="0" || !empty($post["searchLocalityCITYKEY"]))
				$query = Search::searchLocality($post, $query);

			$classified = PHDB::findAndSortAndLimitAndIndex( Classified::COLLECTION, $query,
							array("updated"=>-1), 10);

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
			if(@$type!="0" || !empty($post["searchLocalityCITYKEY"]))
				$query = Search::searchLocality($post, $query);
			
			$pois = PHDB::findAndSortAndLimitAndIndex( Poi::COLLECTION, $query,
							array("updated"=>-1), 10);

			foreach ($pois as $key => $value) {
				$pois[$key]["type"] = "poi";
				$pois[$key]["typeSig"] = "poi";
				if(@$value["updated"]) {
					$pois[$key]["updatedLbl"] = Translate::pastTime(@$value["updated"],"timestamp");
		  		}
		  	}
		  	$contextMap = array_merge($contextMap, $pois);
			
			if(@$post["tpl"]=="json")
				return Rest::json($contextMap);
			else
				echo $this->getController()->renderPartial($post['tpl'], array("result"=>$contextMap, 
																			"element" => $element,
																			"type"=>$type, 
																			"id"=>$id, 
																			"scope"=>@$post['searchLocalityDEPARTEMENT'][0], 
																			"open"=> (@$type=="0"))); //open : for home page (when no user connected)
			Yii::app()->end();
		}



		return Rest::json($contextMap);
		Yii::app()->end();


		
	}
}



?>