<?php
class SimplyAutoCompleteAction extends CAction
{
    public function run($filter = null){

  		if($_POST["search"] == null && $_POST["locality"] == null && $_POST["sourceKey"] == null) {
        	Rest::json(array());
			Yii::app()->end();
        }else{
        	Search::networkAutoComplete($_POST, $filter);
        }

     /***********************************  DEFINE LOCALITY QUERY   ***************************************/
  		$localityReferences['NAME'] = "address.addressLocality";
  		$localityReferences['CODE_POSTAL_INSEE'] = "address.postalCode";
  		$localityReferences['DEPARTEMENT'] = "address.postalCode";
  		$localityReferences['REGION'] = ""; //Spécifique
  		$localityReferences['INSEE'] = "address.codeInsee";

  		foreach ($localityReferences as $key => $value) {
  			if(isset($_POST["searchLocality".$key]) && is_array($_POST["searchLocality".$key])){
  				foreach ($_POST["searchLocality".$key] as $locality) {

  					//OneRegion

  					if($key == "REGION") {
  						if($locality == "La Réunion")
  							$locality = "Réunion" ;
	        			$dep = PHDB::findOne( City::COLLECTION, array("level3Name" => $locality), array("level3"));
	        			if(!empty($dep))
	        				$queryLocality = array("address.level3" => $dep["level3"]);
	        		}else if($key == "DEPARTEMENT") {
	        			$dep = PHDB::findOne( City::COLLECTION, array("level4Name" => $locality), array("level4"));
	        			if(!empty($dep))
		        			$queryLocality = array("address.level4" => $dep["level4"]);
		        	}//OneLocality
		        	else{
	  					$queryLocality = array($value => new MongoRegex("/".$locality."/i"));
	  				}

  					//Consolidate Queries
  					if(!empty($queryLocality)) {
	  					if(isset($allQueryLocality)){
	  						$allQueryLocality = array('$or' => array( $allQueryLocality ,$queryLocality));
	  					}else{
	  						$allQueryLocality = $queryLocality;
	  					}
	  				}
	  				unset($queryLocality);

  				}
  			}
  		}
  		if(isset($allQueryLocality) && is_array($allQueryLocality))
  			$query = array('$and' => array($query, $allQueryLocality));
	    $allRes = array();
        /***********************************  PERSONS   *****************************************/
       if(strcmp($filter, Person::COLLECTION) != 0 && $this->typeWanted("citoyen", $searchType)){

        	$allCitoyen = PHDB::find ( Person::COLLECTION , $query, array("name", "address", "shortDescription", "description"));

	  		foreach ($allCitoyen as $key => $value) {
	  			$person = Person::getSimpleUserById($key, $value);
	  			$person["type"] = "citoyen";
				$person["typeSig"] = "citoyens";
				$allCitoyen[$key] = $person;
	  		}

	  		//$res["citoyen"] = $allCitoyen;
	  		$allRes = array_merge($allRes, $allCitoyen);

	  	}

	  	/***********************************  ORGANISATIONS   *****************************************/
        if(strcmp($filter, Organization::COLLECTION) != 0 && $this->typeWanted("organizations", $searchType)){
        	// if(in_array("NGO", $searchType))
        	// 	$query = array('$and' => array($query, array("type" => "NGO")));
        	
	  		$allOrganizations = PHDB::find ( Organization::COLLECTION ,$query ,array("id" => 1, "name" => 1, "type" => 1, "email" => 1, "url" => 1, "shortDescription" => 1, "description" => 1, "address" => 1, "pending" => 1, "tags" => 1, "geo" => 1, "updated" => 1, "profilImageUrl" => 1, "profilThumbImageUrl" => 1, "profilMarkerImageUrl" => 1,"profilMediumImageUrl" => 1, "addresses"=>1, "telephone"=>1, "slug"=>1));
	  		foreach ($allOrganizations as $key => $value) {
	  			$orga = Organization::getSimpleOrganizationById($key, $value);

	  			$allOrganizations[$key] = $orga;
				$allOrganizations[$key]["type"] = "organizations";
				$allOrganizations[$key]["typeSig"] = "organizations";
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

	  		$allCities = PHDB::find(City::COLLECTION, $query, array("name", "alternateName", "cp", "insee", "regionName", "country", "geo", "geoShape","postalCodes"));
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
	  		//Limit <pagination
	  		if($index < $indexMax && $index >= $indexMin){
	  			$limitRes[] = $value;
		  	}

		  	//filter tag
		  	if(isset($value['tags']))foreach ($value['tags'] as $keyTag => $valueTag) {
		  		if(isset($filters['tags'][$valueTag])){
		  			$filters['tags'][$valueTag] +=1;
		  		}
		  		else{
		  			$filters['tags'][$valueTag] = 1;
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

	  		//filter sourcekey
		  	if(isset($value['source']['key'])){
		  		if(is_array($value['source']['key'])){
			  		foreach ($value['source']['key'] as $keySource => $valueSource) {
				  		if(isset($filters['sourceKey'][$valueSource])){
				  			$filters['sourceKey'][$valueSource] +=1;
				  		}
				  		else{
				  			$filters['sourceKey'][$valueSource] = 1;
				  		}
				  	}
				}
				else{
					if(isset($filters['sourceKey'][$value['source']['key']])){
			  			$filters['sourceKey'][$value['source']['key']] += 1;
			  		}
			  		else{
			  			$filters['sourceKey'][$value['source']['key']] = 1;
			  		}
				}
		  		arsort($filters['sourceKey']);
		  	}
		  	$index++;
	  	}

	  	$res['res'] = $limitRes;
	  	$res['filters'] = $filters;
	  	Rest::json($res);
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
