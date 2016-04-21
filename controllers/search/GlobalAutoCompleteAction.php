<?php
class GlobalAutoCompleteAction extends CAction
{
    public function run($filter = null)
    {

  //   	$pathParams = Yii::app()->controller->module->viewPath.'/default/dir/';
		// echo file_get_contents($pathParams."simply.json");
		// die();

        $search = isset($_POST['name']) ? trim(urldecode($_POST['name'])) : null;
        $locality = isset($_POST['locality']) ? trim(urldecode($_POST['locality'])) : null;
        $searchType = isset($_POST['searchType']) ? $_POST['searchType'] : null;
        $searchTag = isset($_POST['searchTag']) ? $_POST['searchTag'] : null;
        $searchBy = isset($_POST['searchBy']) ? $_POST['searchBy'] : "INSEE";
        $indexMin = isset($_POST['indexMin']) ? $_POST['indexMin'] : 0;
        $indexMax = isset($_POST['indexMax']) ? $_POST['indexMax'] : 100;
        $country = isset($_POST['country']) ? $_POST['country'] : "";
        $sourceKey = isset($_POST['sourceKey']) ? $_POST['sourceKey'] : "";

        error_log("global search " . $search . " - searchBy : ". $searchBy. " & locality : ". $locality. " & country : ". $country." & sourceKey : ".$sourceKey);
	    
        if($search == "" && $locality == "" && $sourceKey == "") {
        	Rest::json(array());
			Yii::app()->end();
        }
        /***********************************  DEFINE GLOBAL QUERY   *****************************************/
        $query = array( "name" => new MongoRegex("/".$search."/i"));		

        /***********************************  TAGS   *****************************************/
        $tmpTags = array();
        if(strpos($search, "#") > -1){
        	$search = substr($search, 1, strlen($search));
        	// $query = array( "tags" => array('$in' => array(new MongoRegex("/".$search."/i")))) ; //new MongoRegex("/".$search."/i") )));
        	$tmpTags[] = new MongoRegex("/".$search."/i");
  		}
  		if($searchTag)foreach ($searchTag as $value) {
  			$tmpTags[] = new MongoRegex("/".$value."/i");
  		}
  		if(count($tmpTags)){
  			$query = array('$and' => array( $query , array("tags" => array('$in' => $tmpTags)))) ;
  		}
  		unset($tmpTags);

  		$query = array('$and' => array( $query , array("state" => array('$ne' => "uncomplete")) ));

  		/***********************************  SOURCEKEY   *****************************************/
         if($sourceKey != null && $sourceKey != ""){
        	$sourceKeyQuery = array( "source.key" => array('$in' => array(new MongoRegex("/".$sourceKey."/i")))) ; //new MongoRegex("/".$search."/i") )));
        	$query = array('$and' => array( $query ,$sourceKeyQuery));
  		}

  		

  		/***********************************  DEFINE LOCALITY QUERY   *****************************************/
        if($locality != null && $locality != ""){

        	//$type = $this->getTypeOfLocalisation($locality);
        	//if($searchBy == "INSEE") 
        	$type = $searchBy;

        	$queryLocality = array();
        	
        	if($type == "NAME"){ 
        		$queryLocality = array("address.addressLocality" => new MongoRegex("/".$locality."/i"));
        	}
        	if($type == "CODE_POSTAL_INSEE") {
        		$queryLocality = array("address.postalCode" => $locality );
        	}
        	if($type == "DEPARTEMENT") {
        		$queryLocality = array("address.postalCode" 
						=> new MongoRegex("/^".$locality."/i"));
        	}
        	if($type == "REGION") {
        		//#TODO GET REGION NAME | CITIES.DEP = myDep
        		$regionName = PHDB::findOne( City::COLLECTION, array("insee" => $locality), array("regionName", "dep"));
        		
				if(isset($regionName["regionName"])){ //quand la city a bien la donnée "regionName"
        			$regionName = $regionName["regionName"];
        			//#TODO GET ALL DEPARTMENT BY REGION
        			$deps = PHDB::find( City::COLLECTION, array("regionName" => $regionName), array("dep"));
        			$departements = array();
        			$inQuest = array();
        			foreach($deps as $index => $value){
        				if(!in_array($value["dep"], $departements)){
	        				$departements[] = $value["dep"];
	        				$inQuest[] = new MongoRegex("/^".$value["dep"]."/i");
				        	$queryLocality = array("address.postalCode" => array('$in' => $inQuest));
	        				
				        }
        			}
        			//$queryLocality = array('$or' => $orQuest);
        			//error_log("queryLocality : " . print_R($queryLocality, true));
        			
        		}else{ //quand la city communectée n'a pas la donnée "regionName", on prend son département à la place
        			$regionName = isset($regionName["dep"]) ? $regionName["dep"] : "";
        			$queryLocality = array("address.postalCode" 
						=> new MongoRegex("/^".$regionName."/i"));
        		}
        		

        		//$str = implode(",", $regionName);
        		error_log("regionName : ".$regionName );

        		//#TODO CREATE REQUEST CITIES.POSTALCODE IN (LIST_DEPARTMENT)" 
      //   		$queryLocality = array("address.postalCode" 
						// => new MongoRegex("/^".$locality."/i"));
        	}
        	if($type == "INSEE") {
        		$queryLocality = array("address.codeInsee" => $locality );
        	}
        	
        	$query = array('$and' => array($query, $queryLocality ) );
	    }



	    //$res = array();
	    $allRes = array();

        /***********************************  PERSONS   *****************************************/
        if(strcmp($filter, "sourceKey") != 0){

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
        	
        	array_push( $queryEvent[ '$and' ], array( "endDate" => array( '$gte' => new MongoDate( time() ) ) ) );
	  		$allEvents = PHDB::findAndSort( PHType::TYPE_EVENTS, $queryEvent, array("startDate" => 1), 100, array("name", "address", "startDate", "endDate", "shortDescription", "description"));
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
	  	function mySort($a, $b){
	  		if(isset($a['name']) && isset($b['name'])){
		    	return ( strtolower($b['name']) < strtolower($a['name']) );
			}else{
				return false;
			}
		}
	  	
	  	if(isset($allRes)) //si on a des resultat dans la liste
	  		if(!$this->typeWanted("events", $searchType)) //si on n'est pas en mode "event" (les event sont classé par date)
	  			usort($allRes, "mySort"); //on tri les éléments par ordre alphabetique sur le name

	  	if(isset($allCitiesRes)) usort($allCitiesRes, "mySort");

	  	//error_log("count : " . count($allRes));
	  	if(count($allRes) < $indexMax) 
	  		if(isset($allCitiesRes)) 
	  			$allRes = array_merge($allRes, $allCitiesRes);

	  	$limitRes = $filters = array();
	  	$index = 0;
	  	foreach ($allRes as $key => $value) {
	  		if($index < $indexMax && $index >= $indexMin){ 
	  			$limitRes[] = $value;
		  	}//else{ break; }

		  	//filter tag
		  	if(isset($value['tags']))foreach ($value['tags'] as $key => $value) {
		  		if(isset($filters['tags'][$value])){
		  			$filters['tags'][$value] +=1;
		  		}
		  		else{
		  			$filters['tags'][$value] = 1;
		  		}
		  		arsort($filters['tags']);
		  	}

		  	//filter type
	  		if(isset($value['type'])){
	  			if(isset($filters['types'][$value['type']])){
	  				$filters['types'][$value['type']] +=1;
	  			}
	  			else{
	  				$filters['types'][$value['type']] = 1;
	  			}
	  			arsort($filters['types']);
	  		}
		  	$index++;
	  	}

	  	// $res['res'] = $limitRes;
	  	// $res['filters'] = $filters;
	  	// Rest::json($res);
  		// Rest::json($res);
  		// Rest::json($filters);
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