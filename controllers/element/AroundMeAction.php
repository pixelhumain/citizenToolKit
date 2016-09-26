<?php

class AroundMeAction extends CAction {
/**
* Around Me
* Find all element around my position (5km => 5000m)
*/
    public function run($type, $id) {
    	//trie les éléments dans l'ordre alphabetique par updated
	  	function mySortByUpdated($a, $b){ // error_log("sort : ");//.$a['name']);
	  		if(isset($a["updated"]) && isset($b["updated"])){
		   		return ( ($b["updated"]) > ($a["updated"]) );
		    } else{
				return false;
			}
		}

    	$controller = $this->getController();

    	if($type == Person::CONTROLLER){

    		$elementsMap = Person::getPersonMap($id);
    		$res = array('network' => $elementsMap);

    		$element = $elementsMap["person"];

    		$lat = $element["geo"]["latitude"] ? $element["geo"]["latitude"] : null;
    		$lng = $element["geo"]["longitude"] ? $element["geo"]["longitude"] : null;

    		if($lat!=null && $lng!=null){
    			$request = array("geoPosition" => array( '$exists' => true ),
								 "geoPosition"  => 
								  array('$near'  =>
									  	array(	'$geometry' =>
									  			array("type" 	    => "Point",
									  			   	  "coordinates" => array( floatval($lng),
									  			  						   	  floatval($lat) )
												  			 		),
								  		 		'$maxDistance' => 5000,
								  		 		'$minDistance' => 10
								  			 ),
							  	 		)
						   		);
				
				$orgas 	  =	PHDB::findAndSort(Organization::COLLECTION, $request, array("updated"), 125);
				$projects =	PHDB::findAndSort(Project::COLLECTION, 		$request, array("updated"), 125);
				$events   =	PHDB::findAndSort(Event::COLLECTION, 		$request, array("updated"), 125);
				//$persons  =	PHDB::findAndSort(Person::COLLECTION, 		$request, array("updated"), 125);

				foreach ($orgas 	as $key => $value) { $orgas[$key]["typeSig"] = "organization"; }
				foreach ($projects 	as $key => $value) { $projects[$key]["typeSig"] = "project"; }
				foreach ($events 	as $key => $value) { $events[$key]["typeSig"] = "event"; }
				//foreach ($persons 	as $key => $value) { $persons[$key]["typeSig"] = "citoyen"; }

				$all = array();
				$all = array_merge($all, $orgas);
				$all = array_merge($all, $projects);
				$all = array_merge($all, $events);
				//$all = array_merge($all, $persons);

	  			//$all = usort($all, "mySortByUpdated");
	  	

				$res["all"] = $all;
    		}
    	}

		$controller->renderPartial("/default/aroundMe", $res);
    }

    

}