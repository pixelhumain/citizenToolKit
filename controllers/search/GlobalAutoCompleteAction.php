<?php
class GlobalAutoCompleteAction extends CAction
{
    public function run($filter = null)
    {
        $search = trim(urldecode($_POST['name']));
        $locality = isset($_POST['locality']) ? trim(urldecode($_POST['locality'])) : null;
        $searchType = isset($_POST['searchType']) ? $_POST['searchType'] : null;
        $indexMin = isset($_POST['indexMin']) ? $_POST['indexMin'] : 0;
        $indexMax = isset($_POST['indexMax']) ? $_POST['indexMax'] : 15;

        if($search == "" && $locality == "") {
        	Rest::json(array());
			Yii::app()->end();
        }
       
        /***********************************  DEFINE GLOBAL QUERY   *****************************************/
        $query = array( "name" => new MongoRegex("/".$search."/i"));
  		

        /***********************************  TAGS   *****************************************/
        if(strpos($search, "#") > -1){
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

	    //$res = array();
	    $allRes = array();

        /***********************************  PERSONS   *****************************************/
        if(strcmp($filter, Person::COLLECTION) != 0 && $this->typeWanted("persons", $searchType)){

        	$allCitoyen = PHDB::find ( Person::COLLECTION , $query, array("name", "address", "shortDescription", "description"));

	  		foreach ($allCitoyen as $key => $value) {
	  			$person = Person::getSimpleUserById($key);
	  			$person["type"] = "citoyen";
				$person["typeSig"] = "citoyens";
				$allCitoyen[$key] = $person;
	  		}

	  		//$res["citoyen"] = $allCitoyen;
	  		$allRes = array_merge($allRes, $allCitoyen);

	  	}

	  	/***********************************  ORGANISATIONS   *****************************************/
        if(strcmp($filter, Organization::COLLECTION) != 0 && $this->typeWanted("organizations", $searchType)){

	  		$allOrganizations = PHDB::find ( Organization::COLLECTION ,$query ,array("name", "address", "shortDescription", "description"));
	  		foreach ($allOrganizations as $key => $value) {
	  			$orga = Organization::getSimpleOrganizationById($key);
				$orga["type"] = "organization";
				$orga["typeSig"] = "organizations";
				$allOrganizations[$key] = $orga;
	  		}

	  		//$res["organization"] = $allOrganizations;
	  		$allRes = array_merge($allRes, $allOrganizations);
	  	}

	  	/***********************************  EVENT   *****************************************/
        if(strcmp($filter, Event::COLLECTION) != 0 && $this->typeWanted("events", $searchType)){
        	if( isset( $query['$and'] ) ) 
        		array_push( $query[ '$and' ], array( "endDate" => array( '$gte' => new MongoDate( time()) ) ) );
	  		$allEvents = PHDB::find( PHType::TYPE_EVENTS, $query, array("name", "address", "shortDescription", "description"));
	  		foreach ($allEvents as $key => $value) {
	  			$event = Event::getById($key);
				$event["type"] = "event";
				$event["typeSig"] = "events";
				$allEvents[$key] = $event;
	  		}
	  		
	  		//$res["event"] = $allEvents;
	  		$allRes = array_merge($allRes, $allEvents);
	  	}

	  	/***********************************  PROJECTS   *****************************************/
        if(strcmp($filter, Project::COLLECTION) != 0 && $this->typeWanted("projects", $searchType)){
	  		$allProject = PHDB::find(Project::COLLECTION, $query, array("name", "address", "shortDescription", "description"));
	  		foreach ($allProject as $key => $value) {
	  			$project = Project::getById($key);
				$project["type"] = "project";
				$project["typeSig"] = "projects";
				$allProject[$key] = $project;
	  		}
	  		//$res["project"] = $allProject;
	  		$allRes = array_merge($allRes, $allProject);
	  	}

	  	/***********************************  CITIES   *****************************************/
        if(strcmp($filter, City::COLLECTION) != 0 && $this->typeWanted("cities", $searchType)){
	  		$query = array( "name" => new MongoRegex("/".self::wd_remove_accents($search)."/i"));//array('$text' => array('$search' => $search));//
	  		
	  		/***********************************  DEFINE LOCALITY QUERY   *****************************************/
	        if($locality != null && $locality != ""){

	        	if($type == "NAME"){ 
	        		$query = array('$or' => array( array( "name" => new MongoRegex("/".self::wd_remove_accents($locality)."/i")),
	        									   array( "alternateName" => new MongoRegex("/".self::wd_remove_accents($locality)."/i"))));
	        		//error_log("search city with : " . self::wd_remove_accents($locality));
	        	}
	        	if($type == "CODE_POSTAL_INSEE") {
	        		$query = array("cp" => $locality );
	        	}
	        	if($type == "DEPARTEMENT") {
	        		$query = array("dep" => $locality );
	        	}
		    }

	  		$allCities = PHDB::find(City::COLLECTION, $query, array("name", "alternateName", "cp", "insee", "geo"));
	  		$allCitiesRes = array();
	  		$nbMaxCities = 20;
	  		$nbCities = 0;
	  		foreach ($allCities as $key => $value) {
	  			if($nbCities < $nbMaxCities){
		  			$city = $value; // City::getSimpleCityById($key);
		  			$city["type"] = "city";
					$city["typeSig"] = "city";
					//error_log("found city : " . $value["alternateName"]." ".$locality);
					if($value["alternateName"] == strtoupper($locality))  $city["name"] = ucwords(strtolower($value["alternateName"])) ;
					else $city["name"] = ucwords(strtolower($value["name"])) ;
				$allCitiesRes[$key] = $city;
				} $nbCities++;
	  		}
	  		//$res["cities"] = $allCitiesRes;

	  		if(empty($allCitiesRes)){
	  			$query = array( "cp" => $search);
		  		$allCities = PHDB::find(City::COLLECTION, $query, array("name", "cp", "insee", "geo"));
		  		$nbCities = 0;
	  			foreach ($allCities as $key => $value) {
		  			if($nbCities < $nbMaxCities){
			  			$city = City::getSimpleCityById($key);
						$city["type"] = "city";
						$city["typeSig"] = "city";
						$allCitiesRes[$key] = $city;
					} $nbCities++;
		  		}
		  		//$res["cities"] = $allCitiesRes;  		
	  		}

	  		

	  	}

	  	//trie les éléments dans l'ordre alphabetique par name
	  	function mySort($a, $b){
		    return $b['name'] < $a['name'];
		}
	  	usort($allRes, "mySort");

	  	//error_log("count : " . count($allRes));
	  	if(count($allRes) < $indexMax) 
	  		$allRes = array_merge($allRes, $allCitiesRes);

	  	$limitRes = array();
	  	$index = 0;
	  	foreach ($allRes as $key => $value) {
	  		if($index < $indexMax && $index >= $indexMin){ $limitRes[] = $value;
		  	}//else{ break; }
		  	$index++;
	  	}

	  	
  		//Rest::json($res);
		Rest::json($limitRes);
		Yii::app()->end();
    }

    //supprime les accents (utilisé pour la recherche de ville pour améliorer les résultats)
    private function wd_remove_accents($str, $charset='utf-8')
	{
		return $str;
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

	private function typeWanted($type, $searchType){
		if($searchType == null) return true;
		return in_array($type, $searchType);
	}
}