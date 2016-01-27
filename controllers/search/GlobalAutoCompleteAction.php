<?php
class GlobalAutoCompleteAction extends CAction
{
    public function run($filter = null)
    {
        $search = trim(urldecode($_POST['name']));
        $locality = isset($_POST['locality']) ? trim(urldecode($_POST['locality'])) : null;
        
        /***********************************  DEFINE GLOBAL QUERY   *****************************************/
        $query = array( "name" => new MongoRegex("/".$search."/i"));
  		

        /***********************************  TAGS   *****************************************/
        if(strpos($search, "#") === 0){
        	$search = substr($search, 1, strlen($search));
        	$query = array( "tags" => array('$in' => array(new MongoRegex("/".$search."/i")))) ; //new MongoRegex("/".$search."/i") )));
  		}


  		/***********************************  DEFINE LOCALITY QUERY   *****************************************/
        if($locality != null && $locality != ""){

        	$type = $this->getTypeOfLocalisation($locality);
        	$queryLocality = array();
        	
        	if($type == "NAME"){ 
        		$queryLocality = array("address.addressLocality" => new MongoRegex("/".$locality."/i"));
        	}
        	if($type == "CODE_POSTAL_INSEE") {
        		$queryLocality = array("address.postalCode" => $locality );
        	}
        	if($type == "DEPARTEMENT") {
        		$queryLocality = array("address.postalCode" 
						=> new MongoRegex("/".$locality."/i"));
        	}
        	$query = array('$and' => array($query, $queryLocality) );
	    }
	  	
  	
	        $res = array();


        /***********************************  PERSONS   *****************************************/
        if(strcmp($filter, Person::COLLECTION) != 0){

        	$allCitoyen = PHDB::find ( Person::COLLECTION , $query, array("name", "address", "shortDescription", "description"));

	  		foreach ($allCitoyen as $key => $value) {
	  			$person = Person::getSimpleUserById($key);
	  			$person["type"] = "citoyen";
				$allCitoyen[$key] = $person;
	  		}

	  		$res["citoyen"] = $allCitoyen;

	  	}

	  	/***********************************  ORGANISATIONS   *****************************************/
        if(strcmp($filter, Organization::COLLECTION) != 0){

	  		$allOrganizations = PHDB::find ( Organization::COLLECTION ,$query ,array("name", "address", "shortDescription", "description"));
	  		foreach ($allOrganizations as $key => $value) {
	  			$orga = Organization::getSimpleOrganizationById($key);
				$orga["type"] = "organization";
				$allOrganizations[$key] = $orga;
	  		}

	  		$res["organization"] = $allOrganizations;
	  	}

	  	/***********************************  EVENT   *****************************************/
        if(strcmp($filter, Event::COLLECTION) != 0){
	  		$allEvents = PHDB::find(PHType::TYPE_EVENTS, $query, array("name", "address", "shortDescription", "description"));
	  		foreach ($allEvents as $key => $value) {
	  			$event = Event::getById($key);
				$event["type"] = "event";
				$allEvents[$key] = $event;
	  		}
	  		
	 
	  		$res["event"] = $allEvents;
	  	}

	  	/***********************************  PROJECTS   *****************************************/
        if(strcmp($filter, Project::COLLECTION) != 0){
	  		$allProject = PHDB::find(Project::COLLECTION, $query, array("name", "address", "shortDescription", "description"));
	  		foreach ($allProject as $key => $value) {
	  			$project = Project::getById($key);
				$project["type"] = "project";
				$allProject[$key] = $project;
	  		}
	  		$res["project"] = $allProject;
	  	}
	  	

	  	/***********************************  CITIES   *****************************************/
        if(strcmp($filter, City::COLLECTION) != 0){
	  		$query = array( "name" => new MongoRegex("/".self::wd_remove_accents($search)."/i"));
	  		
	  		/***********************************  DEFINE LOCALITY QUERY   *****************************************/
	        if($locality != null && $locality != ""){

	        	if($type == "NAME"){ 
	        		$query = array("name" => new MongoRegex("/".self::wd_remove_accents($locality)."/i"));
	        	}
	        	if($type == "CODE_POSTAL_INSEE") {
	        		$query = array("cp" => $locality );
	        	}
	        	if($type == "DEPARTEMENT") {
	        		$query = array("dep" => $locality );
	        	}
		    }

	  		$allCities = PHDB::find(City::COLLECTION, $query, array("name", "cp", "insee", "geo"));
	  		$allCitiesRes = array();
	  		$nbMaxCities = 20;
	  		$nbCities = 0;
	  		foreach ($allCities as $key => $value) {
	  			if($nbCities < $nbMaxCities){
		  			$city = City::getSimpleCityById($key);
		  			$city["type"] = "city";
					$allCitiesRes[$key] = $city;
				} $nbCities++;
	  		}
	  		$res["cities"] = $allCitiesRes;

	  		if(empty($res["cities"])){
	  			$query = array( "cp" => $search);
		  		$allCities = PHDB::find(City::COLLECTION, $query, array("name", "cp", "insee", "geo"));
		  		$nbCities = 0;
	  			foreach ($allCities as $key => $value) {
		  			if($nbCities < $nbMaxCities){
			  			$city = City::getSimpleCityById($key);
						$city["type"] = "city";
						$allCitiesRes[$key] = $city;
					} $nbCities++;
		  		}
		  		$res["cities"] = $allCitiesRes;  		
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

	private function getTypeOfLocalisation($locStr){
		//le cas des localisation intégralement numérique (code postal, insee, departement)
		if(intval($locStr) > 0){
			if(strlen($locStr) <= 3) return "DEPARTEMENT";
			if(strlen($locStr) == 4 || strlen($locStr) == 5) return "CODE_POSTAL_INSEE";
			return "UNDEFINED";
		}else{
			//le cas où le lieu est demandé en toute lettre
			return "NAME";
		}
	}
}