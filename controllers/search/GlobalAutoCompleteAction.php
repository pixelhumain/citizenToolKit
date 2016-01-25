<?php
class GlobalAutoCompleteAction extends CAction
{
    public function run($filter = null)
    {
        $search = trim(urldecode($_POST['name']));
        $locality = isset($_POST['locality']) ? trim(urldecode($_POST['locality'])) : null;
        
        /***********************************  DEFINE GLOBAL QUERY   *****************************************/
        $query = array( "name" => new MongoRegex("/".$search."/i"));
  		//error_log("start search width locality : " . $locality);
        

        /***********************************  TAGS   *****************************************/
        if(strpos($search, "#") === 0){
        	$search = substr($search, 1, strlen($search));
        	//error_log($search);
        	$query = array( "tags" => array('$in' => array(new MongoRegex("/".$search."/i")))) ; //new MongoRegex("/".$search."/i") )));
  		}


  		/***********************************  DEFINE LOCALITY QUERY   *****************************************/
        if($locality != null && $locality != ""){

        	//error_log("search use locality : " . $locality);
        	$type = $this->getTypeOfLocalisation($locality);
        	$queryLocality = array();
        	
        	if($type == "NAME"){ 
        		$queryLocality = array("address.addressLocality" => new MongoRegex("/".$locality."/i"));
        	}
        	if($type == "CODE_POSTAL_INSEE") {
        		// $queryLocality = array('$or' => 
        		// 			array("address.postalCode" => $locality, 
        		// 				  "address.codeInsee" => $locality) );
        		$queryLocality = array("address.postalCode" => $locality );
        	}
        	if($type == "DEPARTEMENT") {
        		
        		/*$zero = strlen($locality) > 2 ? "00" : "000";
        		$localityMax = intval($locality) + 1;
        		$locality = $locality . $zero;
        		$localityMax = $localityMax . $zero;
				*/
        		$queryLocality = array("address.postalCode" 
						=> new MongoRegex("/".$locality."/i"));
        	}
        	$query = array('$and' => array($query, $queryLocality) );
	    }
	  	
	  	if($search != null && $search != ""){
		  	
	        $res = array();


	        /***********************************  PERSONS   *****************************************/
	        if(strcmp($filter, Person::COLLECTION) != 0){

	        	$allCitoyen = PHDB::find ( Person::COLLECTION , $query, array("name", "address"));

		  		foreach ($allCitoyen as $key => $value) {
		  			$person = Person::getSimpleUserById($key);
		  			$person["type"] = "citoyen";
					$allCitoyen[$key] = $person;
		  		}

		  		$res["citoyen"] = $allCitoyen;

		  	}

		  	/***********************************  ORGANISATIONS   *****************************************/
	        if(strcmp($filter, Organization::COLLECTION) != 0){

		  		$allOrganizations = PHDB::find ( Organization::COLLECTION ,$query ,array("name", "type", "address"));
		  		foreach ($allOrganizations as $key => $value) {
		  			$orga = Organization::getSimpleOrganizationById($key);
					$allOrganizations[$key] = $orga;
		  		}

		  		$res["organization"] = $allOrganizations;
		  	}

		  	/***********************************  EVENT   *****************************************/
	        if(strcmp($filter, Event::COLLECTION) != 0){
		  		$allEvents = PHDB::find(PHType::TYPE_EVENTS, $query, array("name", "address"));
		  		foreach ($allEvents as $key => $value) {
		  			$event = Event::getSimpleEventById($key);
					$allEvents[$key] = $event;
		  		}
		  		
		 
		  		$res["event"] = $allEvents;
		  	}

		  	/***********************************  PROJECTS   *****************************************/
	        if(strcmp($filter, Project::COLLECTION) != 0){
		  		$allProject = PHDB::find(Project::COLLECTION, $query, array("name", "address"));
		  		foreach ($allProject as $key => $value) {
		  			$project = Project::getSimpleProjectById($key);
					$allProject[$key] = $project;
		  		}
		  		$res["project"] = $allProject;
		  	}

		}

	  	/***********************************  CITIES   *****************************************/
        if(strcmp($filter, City::COLLECTION) != 0){
	  		$query = array( "name" => new MongoRegex("/".self::wd_remove_accents($search)."/i"));
	  		//$query = array('$and' => array($query, $queryLocality) );

	  		/***********************************  DEFINE LOCALITY QUERY   *****************************************/
	        if($locality != null && $locality != ""){

	        	//error_log("search use locality for Cities : " . $locality . " - Type : " . $type);
	        	//$queryLocality = array();
	        	
	        	if($type == "NAME"){ 
	        		$query = array("name" => new MongoRegex("/".self::wd_remove_accents($locality)."/i"));
	        	}
	        	if($type == "CODE_POSTAL_INSEE") {
	        		// $queryLocality = array('$or' => 
	        		// 			array("address.postalCode" => $locality, 
	        		// 				  "address.codeInsee" => $locality) );
	        		$query = array("cp" => $locality );
	        	}
	        	if($type == "DEPARTEMENT") {
	        		
	        		////error_log($locality . " | localityMax : " . $localityMax);
	      //   		$query = array("cp" 
							// => array('$lt' => intval($localityMax),
							// 		 '$gt' => intval($locality) 
							// 		 ));
	        		$query = array("dep" => $locality );
	        	}
	        	//$query = array('$and' => array($query, $queryLocality) );
		    }

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