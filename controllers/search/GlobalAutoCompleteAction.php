<?php
class GlobalAutoCompleteAction extends CAction
{
    public function run($filter = null)
    {
        $search = trim(urldecode($_POST['name']));
        
        $query = array( "name" => new MongoRegex("/".$search."/i"));
  		
        $res = array();


        if(strcmp($filter, Person::COLLECTION) != 0){

	  		$allCitoyen = PHDB::find ( Person::COLLECTION ,$query ,array("name", "address"));

	  		foreach ($allCitoyen as $key => $value) {
	  			$person = Person::getSimpleUserById($key);
	  			$person["type"] = "citoyen";
				$allCitoyen[$key] = $person;
	  		}

	  		$res["citoyen"] = $allCitoyen;

	  	}

	  	if(strcmp($filter, Organization::COLLECTION) != 0){

	  		$allOrganizations = PHDB::find ( Organization::COLLECTION ,$query ,array("name", "type", "address"));
	  		foreach ($allOrganizations as $key => $value) {
	  			$orga = Organization::getSimpleOrganizationById($key);
				$allOrganizations[$key] = $orga;
	  		}

	  		$res["organization"] = $allOrganizations;
	  	}

	  	if(strcmp($filter, Event::COLLECTION) != 0){
	  		$allEvents = PHDB::find(PHType::TYPE_EVENTS, $query, array("name", "address"));
	  		foreach ($allEvents as $key => $value) {
	  			$event = Event::getSimpleEventById($key);
				$allEvents[$key] = $event;
	  		}
	  		
	 
	  		$res["event"] = $allEvents;
	  	}

	  	if(strcmp($filter, Project::COLLECTION) != 0){
	  		$allProject = PHDB::find(Project::COLLECTION, $query, array("name", "address"));
	  		foreach ($allProject as $key => $value) {
	  			$project = Project::getSimpleProjectById($key);
				$allProject[$key] = $project;
	  		}
	  		$res["project"] = $allProject;
	  	}


	  	if(strcmp($filter, City::COLLECTION) != 0){
	  		$query = array( "name" => new MongoRegex("/".self::wd_remove_accents($search)."/i"));
	  		$allCities = PHDB::find(City::COLLECTION, $query, array("name", "cp", "insee", "geo"));
	  		foreach ($allCities as $key => $value) {
	  			$city = City::getSimpleCityById($key);
				$allCities[$key] = $city;
	  		}
	  		$res["cities"] = $allCities;

	  		if(empty($res["cities"])){
	  			$query = array( "cp" => $search);
		  		$allCities = PHDB::find(City::COLLECTION, $query, array("name", "cp", "insee", "geo"));
		  		foreach ($allCities as $key => $value) {
		  			$city = City::getSimpleCityById($key);
					$allCities[$key] = $city;
		  		}
		  		$res["cities"] = $allCities;  		
	  		}
	  	}

  		Rest::json($res);
		Yii::app()->end();
    }

    //supprime les accen (utilisé pour la recherche de ville pour améliorer les résultats)
    private function wd_remove_accents($str, $charset='utf-8')
	{
	    $str = htmlentities($str, ENT_NOQUOTES, $charset);
	    
	    $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
	    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
	    $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
	    
	    return $str;
	}
}