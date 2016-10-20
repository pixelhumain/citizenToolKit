<?php

class AroundMeAction extends CAction {
/**
* Around Me
* Find all element around my position (5km => 5000m)
*/
    public function run($type, $id, $radius=5000, $manual=false, $json=false) {
    	//trie les éléments dans l'ordre alphabetique par updated
	  	function mySortByUpdated($a, $b){ 
	  		if(isset($a["updated"]) && isset($b["updated"])){
		   		return ( ($b["updated"]) > ($a["updated"]) );
		    } else{
				return false;
			}
		}
		$res = array();
		$elementsMap = array();
		if($type == Person::CONTROLLER){
			$elementsMap = Person::getPersonMap($id);
			//var_dump($elementsMap["person"]); exit;
			if(!isset($elementsMap["person"]["geo"])) {
				$res["result"] = false;
            	$res["msg"] = "Vous n'êtes pas géolocalisé";
            	Rest::json($res);
            	return;
			}

			$res["lat"] = @$elementsMap["person"]["geo"]["latitude"] ? $elementsMap["person"]["geo"]["latitude"] : null;
    		$res["lng"] = @$elementsMap["person"]["geo"]["longitude"] ? $elementsMap["person"]["geo"]["longitude"] : null;
    	}	
		$all = $this->loadElements($elementsMap, $radius, $type, $id);
		while(sizeOf($all) < 1 && $radius > 0 && !$manual){ 
			$all = $this->loadElements($elementsMap, $radius, $type, $id);
			if(sizeOf($all) < 1) $radius = $this->getNextRadius($radius);
		}
	

		$controller = $this->getController();

    	$res["all"] = $all;
    	$res["radius"] = $radius;
    	$res["type"] = $type;
		$res["id"] = $id;

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
			if($value == $radius) return @$radiusSteps[$key+1] ? $radiusSteps[$key+1] : $radius;
		}
		return 0;
    }

    private function loadElements($elementsMap, $radius, $type, $id){
    	//error_log("startSearch with : ".$radius);
    	//if($type == Person::CONTROLLER){

    		//$elementsMap = Person::getPersonMap($id);
    		$res = array('network' => $elementsMap);

    		$element = $elementsMap["person"];

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
				//$persons  =	PHDB::findAndSort(Person::COLLECTION, 		$request, array("updated"), 125);

				foreach ($orgas 	as $key => $value) { $orgas[$key]["type"] = "organization"; $orgas[$key]["typeSig"] = "organization"; }
				foreach ($projects 	as $key => $value) { $projects[$key]["type"] = "project"; $projects[$key]["typeSig"] = "project"; }
				foreach ($events 	as $key => $value) { $events[$key]["type"] = "event"; $events[$key]["typeSig"] = "event"; }
				//foreach ($persons 	as $key => $value) { $persons[$key]["typeSig"] = "citoyen"; }

				$all = array();
				$all = array_merge($all, $orgas);
				$all = array_merge($all, $projects);
				$all = array_merge($all, $events);

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