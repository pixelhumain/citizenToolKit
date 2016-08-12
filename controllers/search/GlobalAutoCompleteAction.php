<?php
class GlobalAutoCompleteAction extends CAction
{
    public function run($filter = null)
    {
        $search = trim(urldecode($_POST['name']));
        $locality = isset($_POST['locality']) ? trim(urldecode($_POST['locality'])) : null;
        $searchType = isset($_POST['searchType']) ? $_POST['searchType'] : null;
        $searchTag = isset($_POST['searchTag']) ? $_POST['searchTag'] : null;
        $searchBy = isset($_POST['searchBy']) ? $_POST['searchBy'] : "INSEE";
        $indexMin = isset($_POST['indexMin']) ? $_POST['indexMin'] : 0;
        $indexMax = isset($_POST['indexMax']) ? $_POST['indexMax'] : 30;
        $country = isset($_POST['country']) ? $_POST['country'] : "";

        error_log("global search " . $search . " - searchBy : ". $searchBy. " & locality : ". $locality. " & country : ". $country);
	    
   //      if($search == "" && $locality == "") {
   //      	Rest::json(array());
			// Yii::app()->end();
   //      }

        /***********************************  DEFINE GLOBAL QUERY   *****************************************/
        $query = array();
        
       // if(isset($search) && $search != "")
        $query = array( "name" => new MongoRegex("/".$search."/i"));
  		

        /***********************************  TAGS   *****************************************/
        $tmpTags = array();
        if(strpos($search, "#") > -1){
        	$search = substr($search, 1, strlen($search)); 
        	$query = array( "tags" => array('$in' => array(new MongoRegex("/".$search."/i")))) ; 
        	$tmpTags[] = new MongoRegex("/".$search."/i");
  		}
  		if(!empty($searchTag))
  			foreach ($searchTag as $value) { 
  				if($value != "")
	  			$tmpTags[] = new MongoRegex("/".$value."/i");
	  		}
  		if(count($tmpTags)){
  			$query = array('$and' => array( $query , array("tags" => array('$in' => $tmpTags)))) ;
  		}
  		unset($tmpTags);
  		$query = array('$and' => array( $query , array("state" => array('$ne' => "uncomplete")) ));


  		/***********************************  DEFINE LOCALITY QUERY   ***************************************/
  		$localityReferences['NAME'] = "";
  		$localityReferences['CODE_POSTAL_INSEE'] = "address.postalCode";
  		$localityReferences['DEPARTEMENT'] = "address.postalCode";
  		$localityReferences['REGION'] = ""; //Spécifique
  		$localityReferences['INSEE'] = "address.codeInsee";

  		foreach ($localityReferences as $key => $value) 
  		{
  			if(isset($_POST["searchLocality".$key]) 
  				&& is_array($_POST["searchLocality".$key])
  				&& count($_POST["searchLocality".$key])>0)
  			{
  				foreach ($_POST["searchLocality".$key] as $localityRef) 
  				{
  					if(isset($localityRef) && $localityRef != ""){
	  					//error_log("locality :  ".$localityRef. " - " .$key);
	  					//OneRegion
	  					if($key == "REGION") 
	  					{ 
		        			$deps = PHDB::find( City::COLLECTION, array("regionName" => $localityRef), array("dep"));
		        			$departements = array();
		        			$inQuest = array();
		        			if(is_array($deps))foreach($deps as $index => $value)
		        			{
		        				if(!in_array($value["dep"], $departements))
		        				{
			        				$departements[] = $value["dep"];
			        				$inQuest[] = new MongoRegex("/^".$value["dep"]."/i");
						        	$queryLocality = array("address.postalCode" => array('$in' => $inQuest));
						        }
		        			}		        		
		        		}elseif($key == "DEPARTEMENT") {
		        			$dep = PHDB::findOne( City::COLLECTION, array("depName" => $localityRef), array("dep"));	
		        			$queryLocality = array($value => new MongoRegex("/^".$dep["dep"]."/i"));
						}//OneLocality
			        	elseif($key == "NAME"){
			        		//value.country + "_" + value.insee + "-" + value.postalCodes[0].postalCode; 
			        		error_log("NAME " .$localityRef );
			        		$city = City::getByUnikey($localityRef);
			        		$queryLocality = array(
			        				"address.addressCountry" => new MongoRegex("/".$city["country"]."/i"),
			        				"address.codeInsee" => new MongoRegex("/".$city["insee"]."/i"),
			        				"address.postalCode" => new MongoRegex("/".$city["cp"]."/i"),
			        		);
		  				}
		  				else{
			        		$queryLocality = array($value => new MongoRegex("/".$localityRef."/i"));
		  				}

	  					//Consolidate Queries
	  					if(isset($allQueryLocality) && isset($queryLocality)){
	  						$allQueryLocality = array('$or' => array( $allQueryLocality ,$queryLocality));
	  					}else if(isset($queryLocality)){
	  						$allQueryLocality = $queryLocality;
	  					}
	  					unset($queryLocality);
	  				}
  				}
  			}
  		}
  		if(isset($allQueryLocality) && is_array($allQueryLocality))
  			$query = array('$and' => array($query, $allQueryLocality));
  		
	    //$res = array();
	    $allRes = array();
	    //var_dump($query); return;
        /***********************************  PERSONS   *****************************************/
        if(strcmp($filter, Person::COLLECTION) != 0 && $this->typeWanted("persons", $searchType)){

        	$allCitoyen = PHDB::findAndSort ( Person::COLLECTION , $query, 
	  										  array("name" => 1), 30, 
	  										  array("name", "address", "shortDescription", "description"));

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
        	$queryDisabled = array("disabled" => array('$exists' => false));
        	$queryOrganization = array('$and' => array($query, $queryDisabled));
	  		$allOrganizations = PHDB::findAndSort ( Organization::COLLECTION ,$queryOrganization, 
	  												array("updated" => 1, "name" => 1), 30, 
	  												array("name", "address", "shortDescription", "description"));
	  		foreach ($allOrganizations as $key => $value) {
	  			$orga = Organization::getSimpleOrganizationById($key);
	  			$followers = Organization::getFollowersByOrganizationId($key);
	  			if(@$followers[Yii::app()->session["userId"]]){
		  			$orga["isFollowed"] = true;
	  			}
				$orga["type"] = "organization";
				$orga["typeSig"] = "organizations";
				$allOrganizations[$key] = $orga;
	  		}

	  		//$res["organization"] = $allOrganizations;
	  		$allRes = array_merge($allRes, $allOrganizations);
	  	}

	  	/***********************************  EVENT   *****************************************/
        if(strcmp($filter, Event::COLLECTION) != 0 && $this->typeWanted("events", $searchType)){
        	
        	$queryEvent = $query;
        	if( !isset( $queryEvent['$and'] ) ) 
        		$queryEvent['$and'] = array();
        	//error_log("searching for events");
        	array_push( $queryEvent[ '$and' ], array( "endDate" => array( '$gte' => new MongoDate( time() ) ) ) );
        	//var_dump($queryEvent); return;
	  		$allEvents = PHDB::findAndSort( PHType::TYPE_EVENTS, $queryEvent, 
	  										array("startDate" => 1), 30, 
	  										array("name", "address", "startDate", "endDate", "shortDescription", "description"));
	  		foreach ($allEvents as $key => $value) {
	  			$event = Event::getById($key);
				$event["type"] = "event";
				$event["typeSig"] = "events";
				$allEvents[$key] = $event;
				error_log("event fount : ".$event["name"]);
	  		}
	  		
	  		//$res["event"] = $allEvents;
	  		$allRes = array_merge($allRes, $allEvents);
	  	}

	  	/***********************************  PROJECTS   *****************************************/
        if(strcmp($filter, Project::COLLECTION) != 0 && $this->typeWanted("projects", $searchType)){
	  		$allProject = PHDB::findAndSort(Project::COLLECTION, $query, 
	  												array("updated" => 1, "name" => 1), 30, 
	  												array("name", "address", "shortDescription", "description"));
	  		foreach ($allProject as $key => $value) {
	  			$project = Project::getById($key);
	  			if(@$project["links"]["followers"][Yii::app()->session["userId"]]){
		  			$orga["isFollowed"] = true;
	  			}
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
	        	if($locality == null || $locality == ""){
		    		$locality = $search;
		    	}
		    	$type = $this->getTypeOfLocalisation($locality);
		    	if($searchBy == "INSEE") $type = $searchBy;
	        	error_log("type " . $type);
	    		if($type == "NAME"){ 
	        		$query = array('$or' => array( array( "name" => new MongoRegex("/".self::wd_remove_accents($locality)."/i")),
	        									   array( "alternateName" => new MongoRegex("/".self::wd_remove_accents($locality)."/i")),
	        									   array("postalCodes.name" => array('$in' => array(new MongoRegex("/".self::wd_remove_accents($locality)."/i"))))
	        					));
	        		//error_log("search city with : " . self::wd_remove_accents($locality));
	        	}
	        	if($type == "CODE_POSTAL_INSEE") {
	        		$query = array("postalCodes.postalCode" => array('$in' => array($locality)));
	        	}
	        	if($type == "DEPARTEMENT") {
	        		$query = array("dep" => $locality );
	        	}
	        	if($type == "INSEE") {
	        		$query = array("insee" => $locality );
	        	}
			    //}

			    if($country != ""){
			    	$query["country"] = $country;
			    }

	  		//$allCities = PHDB::find(City::COLLECTION, $query, array("name", "alternateName", "cp", "insee", "regionName", "country", "geo", "geoShape","postalCodes"));
	  		$allCities = PHDB::find(City::COLLECTION, $query);
	  		$allCitiesRes = array();
	  		$nbMaxCities = 20;
	  		$nbCities = 0;
	  		foreach($allCities as $data){
		  		$countPostalCodeByInsee = count($data["postalCodes"]);
		  		foreach ($data["postalCodes"] as $val){
			  		if($nbCities < $nbMaxCities){
			  		$newCity = array();
			  		//$regionName = 
			  		$newCity = array(
			  						"_id"=>$data["_id"],
			  						"insee" => $data["insee"], 
			  						"regionName" => isset($data["regionName"]) ? $data["regionName"] : "", 
			  						"country" => $data["country"],
			  						"geoShape" => isset($data["geoShape"]) ? $data["geoShape"] : "",
			  						"cp" => $val["postalCode"],
			  						"geo" => $val["geo"],
			  						"geoPosition" => $val["geoPosition"],
			  						"name" => ucwords(strtolower($val["name"])),
			  						"alternateName" => ucwords(strtolower($val["name"])),
			  						"type"=>"city",
			  						"typeSig" => "city");
			  		if($countPostalCodeByInsee > 1){
			  			$newCity["countCpByInsee"] = $countPostalCodeByInsee;
			  			$newCity["cityInsee"] = ucwords(strtolower($data["alternateName"]));
			  		}
			  		$allCitiesRes[]=$newCity;
			  		} $nbCities++;
		  		}
	  		}
	  		/*$allCitiesRes = array();
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
	  		}*/
	  		//$res["cities"] = $allCitiesRes;

	  		if(empty($allCitiesRes)){
	  			$query = array( "cp" => $search);
		  		$allCities = PHDB::find(City::COLLECTION, $query, array("name", "cp", "insee", "geo", "geoShape"));
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
	  	function mySortByName($a, $b){ // error_log("sort : ");//.$a['name']);
	  		if(isset($a["_id"]) && isset($b["_id"])){
		   		return ( strtolower($b["_id"]) < strtolower($a["_id"]) );
		    } else{
				return false;
			}
		}
	  	
	  	//trie les éléments dans l'ordre alphabetique par name
	  	function mySortByUpdated($a, $b){ // error_log("sort : ");//.$a['name']);
	  		if(isset($a["updated"]) && isset($b["updated"])){
		   		return ( strtolower($b["updated"]) < strtolower($a["updated"]) );
		    } else{
				return false;
			}
		}
	  	
	  	// foreach ($allRes as $key => $value) {
	  	// 	if(!isset($value["updated"])){ 
	  	// 		//error_log(strtotime("-1 month"));
	  	// 		$allRes[$key]["updated"] = strtotime("-1 month");
		  // 	}else error_log("updated : ".$value["updated"]);
	  	// }

	  	// if(isset($allRes)) //si on a des resultat dans la liste
	  	// 	if(!$this->typeWanted("events", $searchType) || count($searchType)>1) //si on n'est pas en mode "calendar" (les event sont classés par date)
	  	// 		usort($allRes, "mySortByName"); //on tri les éléments par ordre alphabetique sur le name

	  	if(isset($allRes)) usort($allRes, "mySortByName");

	  	if(isset($allCitiesRes)) usort($allCitiesRes, "mySortByName");

	  	//error_log("count : " . count($allRes));
	  	if(count($allRes) < $indexMax) 
	  		if(isset($allCitiesRes)) 
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