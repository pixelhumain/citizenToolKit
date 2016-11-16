<?php

class AroundMeAction extends CAction {
/**
* Around Me
* Find all element around my position (5km => 5000m)
*/
    public function run($type, $id, $radius=5000, $manual=false, $json=false) {

    	$controller = $this->getController();

    	//trie les éléments dans l'ordre alphabetique par updated
	  	function mySortByUpdated($a, $b){ 
	  		if(isset($a["updated"]) && isset($b["updated"])){
		   		return ( ($b["updated"]) > ($a["updated"]) );
		    } else{
				return false;
			}
		}

		//Yii::app()->theme =

		$res = array();
		$elementsMap = array();

		$spec = Element::getElementSpecsByType($type);
		$collection = isset($spec["collection"]) ? $spec["collection"] : $type;
		if($collection == "") {
			$res["result"] = false;
        	$res["msg"] = "Collection not found : ".$type;
        	Rest::json($res);
        	return;
		}

		$element = Element::getElementById($id, $collection);

		$res["lat"] = @$element["geo"]["latitude"]  ? $element["geo"]["latitude"]  : null;
    	$res["lng"] = @$element["geo"]["longitude"] ? $element["geo"]["longitude"] : null;

    	

		$elementsMap = Element::getAllLinks(null, $type, $id);

		$element["typeSig"] = $collection;
		$res["parentName"] = @$element["name"];
		$res["parent"] = @$element;

		$all = array();

		if($res["lat"] != null && $res["lng"] != null) {			
			$all = $this->loadElements($element, $radius, $type, $id);
			while(sizeOf($all) < 1 && $radius > 0 && !$manual){ 
				$all = $this->loadElements($elementsMap, $radius, $type, $id);
				if(sizeOf($all) < 1) $radius = $this->getNextRadius($radius);
			}
		}
		
    	$res["all"] = $all;
    	$res["radius"] = $radius;
    	$res["type"] = $type;
    	$res["id"] = $id;

		$parentType = Element::getElementSpecsByType($type);

		if($json){
			$res["result"] = true;
            Rest::json($res);
        }
        else {
           $controller->renderPartial("/default/aroundMe", $res);
        }
    }

    private function getNextRadius($radius){
    	$radiusSteps = array(2000, 5000, 10000, 25000, 50000, 0);
		foreach ($radiusSteps as $key => $value) {
			if($value == $radius) return isset($radiusSteps[$key+1]) ? $radiusSteps[$key+1] : $radius;
		}
		return 0;
    }

    private function loadElements($element, $radius, $type, $id){
    	//error_log("startSearch with : ".$radius);
    	//if($type == Person::CONTROLLER){

    		//$elementsMap = Person::getPersonMap($id);
    		$res = array(); //'network' => $elementsMap);

    		$lat = @$element["geo"]["latitude"] ? $element["geo"]["latitude"] : null;
    		$lng = @$element["geo"]["longitude"] ? $element["geo"]["longitude"] : null;

    		if($lat!=null && $lng!=null){
    			$request = array("geoPosition" => array( '$exists' => true ),
								 "geoPosition"  => 
								  array('$near'  =>
									  	array(	'$geometry' =>
									  			array("type" 	    => "Point",
									  			   	  "coordinates" => array( floatval($lng),
									  			  						   	  floatval($lat) )
												  			 		),
								  		 		'$maxDistance' => intval($radius),
								  		 		'$minDistance' => 10
								  			 ),
							  	 		)
						   		);
				
				$orgas 	  =	PHDB::findAndSort(Organization::COLLECTION, $request, array("updated"), 125);
				$projects =	PHDB::findAndSort(Project::COLLECTION, 		$request, array("updated"), 125);
				$events   =	PHDB::findAndSort(Event::COLLECTION, 		$request, array("updated"), 125);
				$persons  =	PHDB::findAndSort(Person::COLLECTION, 		$request, array("updated"), 125);

				foreach ($orgas 	as $key => $value) { $orgas[$key]["type"] = "organization"; $orgas[$key]["typeSig"] = "organizations"; }
				foreach ($projects 	as $key => $value) { $projects[$key]["type"] = "project"; 	$projects[$key]["typeSig"] = "projects"; }
				foreach ($events 	as $key => $value) { $events[$key]["type"] = "event"; 		$events[$key]["typeSig"] = "events"; }
				foreach ($persons 	as $key => $value) { $persons[$key]["type"] = "citoyen"; 	$persons[$key]["typeSig"] = "citoyens"; }

				$all = array();
				$all = array_merge($all, $orgas);
				$all = array_merge($all, $projects);
				$all = array_merge($all, $events);
				$all = array_merge($all, $persons);

				foreach ($all as $keyS => $value) {
					if(@$all[$keyS]["endDate"]) $all[$keyS]["endDate"] =  date("Y-m-d H:i:s", $all[$keyS]["endDate"]->sec);
					if(@$all[$keyS]["startDate"]) $all[$keyS]["startDate"] =  date("Y-m-d H:i:s", $all[$keyS]["startDate"]->sec);
					if(@$all[$keyS]["updated"]){
					 	$all[$keyS]["updatedLbl"] = Translate::pastTime($all[$keyS]["updated"],"timestamp");
					 	$all[$keyS]["updated"] =  date("Y-m-d H:i:s", $all[$keyS]["updated"]);
					 	
					}
				}
				

				
				usort($all, "mySortByUpdated");
				/*
				if(sizeOf($all)>0){
					$allSorted = array($all[0]);
					foreach ($all as $keyS => $value) {
						if(isset($allSorted[0])){
							if($value["updated"] > @$allSorted[0]["updated"]){
								$allSorted = array_merge($allSorted, array($keyS=>$value));
							}
						}else{
							
						}
					}
				}*/
				//$all = array_merge($all, $persons);
	  			//$all = usort($all, "mySortByUpdated");
	  			//$all = $allSorted;
				return $all;
    		}
    	//}
    	return null;
    }
     private function mySortByUpdated($a, $b){ // error_log("sort : ");//.$a['name']);
  		if(isset($a["updated"]) && isset($b["updated"])){
	   		return ( strtolower($b["updated"]) > strtolower($a["updated"]) );
	    } else{
			return false;
		}
	}

}