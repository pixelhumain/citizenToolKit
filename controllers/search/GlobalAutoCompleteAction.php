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
        $latest = isset($_POST['latest']) ? $_POST['latest'] : null;

        $indexStep = $indexMax - $indexMin;
        
        //error_log("global search " . $search . " - searchBy : ". $searchBy. " & locality : ". $locality. " & country : ". $country);
	    
   //      if($search == "" && $locality == "") {
   //      	Rest::json(array());
			// Yii::app()->end();
   //      }

        /***********************************  DEFINE GLOBAL QUERY   *****************************************/
        $query = array();
        
       // if(isset($search) && $search != "")
        $searchRegExp = self::accentToRegex($search);
        $query = array( "name" => new MongoRegex("/.*{$searchRegExp}.*/i"));

        
        /***********************************  TAGS   *****************************************/
        $tmpTags = array();
        if(strpos($search, "#") > -1){
        	$search = substr($search, 1, strlen($search)); 
        	$query = array( "tags" => array('$in' => array(new MongoRegex("/^".$search."$/i")))) ; 
        	$tmpTags[] = new MongoRegex("/^".$search."$/i");
  		}
  		if(!empty($searchTag))
  			foreach ($searchTag as $value) { 
  				if($value != "")
	  				$tmpTags[] = new MongoRegex("/^".$value."$/i");
	  		}
  		if(count($tmpTags)){
  			$query = array('$and' => array( $query , array("tags" => array('$in' => $tmpTags)))) ;
  		}
  		unset($tmpTags);
  		$query = array('$and' => array( $query , array("state" => array('$ne' => "uncomplete")) ));
  		
  		if($latest)
  			$query = array('$and' => array($query, array("updated"=>array('$exists'=>1))));

  		/***********************************  DEFINE LOCALITY QUERY   ***************************************/
  		$localityReferences['CITYKEY'] = "";
  		$localityReferences['CODE_POSTAL'] = "address.postalCode";
  		$localityReferences['DEPARTEMENT'] = "address.postalCode";
  		$localityReferences['REGION'] = ""; //Spécifique

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
	  					if($key == "CITYKEY"){
			        		//value.country + "_" + value.insee + "-" + value.postalCodes[0].postalCode; 
			        		//error_log("CITYKEY " .$localityRef );
			        		$city = City::getByUnikey($localityRef);
			        		$queryLocality = array(
			        				"address.addressCountry" => $city["country"],
			        				"address.codeInsee" => $city["insee"],
			        				//"address.postalCode" => new MongoRegex("/".$city["cp"]."/i"),
			        		);
			        		if (! empty($city["cp"])) {
			        			$queryLocality["address.postalCode"] = $city["cp"];	
			        		}
		  				}
		  				elseif($key == "CODE_POSTAL"){
			        		$queryLocality = array($value => new MongoRegex("/".$localityRef."/i"));
		  				}
		  				elseif($key == "DEPARTEMENT") {
		        			$dep = PHDB::findOne( City::COLLECTION, array("depName" => $localityRef), array("dep"));	
		        			$queryLocality = array($value => new MongoRegex("/^".$dep["dep"]."/i"));
						}
			        	elseif($key == "REGION") 
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

        	$allCitoyen = PHDB::findAndSortAndLimitAndIndex ( Person::COLLECTION , $query, 
	  										  array("updated" => -1), $indexStep, $indexMin);

	  		foreach ($allCitoyen as $key => $value) {
	  			$person = Person::getSimpleUserById($key,$value);
	  			$person["type"] = "citoyen";
				$person["typeSig"] = "citoyens";
				$allCitoyen[$key] = $person;
	  		}

	  		//$res["citoyen"] = $allCitoyen;
	  		$allRes = array_merge($allRes, $allCitoyen);

	  	}

	  	/***********************************  ORGANISATIONS   *****************************************/
        if(strcmp($filter, Organization::COLLECTION) != 0 && $this->typeWanted(Organization::COLLECTION, $searchType)){
        	$queryOrganization = $query;
        	if( !isset( $queryOrganization['$and'] ) ) 
        		$queryOrganization['$and'] = array();
        	array_push( $queryOrganization[ '$and' ], array( "disabled" => array('$exists' => false) ) );
	  		$allOrganizations = PHDB::findAndSortAndLimitAndIndex ( Organization::COLLECTION ,$queryOrganization, 
	  												array("updated" => -1), $indexStep, $indexMin);
	  		foreach ($allOrganizations as $key => $value) 
	  		{
	  			if(!empty($value)){
		  			$orga = Organization::getSimpleOrganizationById($key,$value);
		  			if( @$value["links"]["followers"][Yii::app()->session["userId"]] )
			  			$orga["isFollowed"] = true;
					$orga["type"] = "organization";
					$orga["typeSig"] = Organization::COLLECTION;
					$allOrganizations[$key] = $orga;
				}
	  		}

	  		//$res["organization"] = $allOrganizations;
	  		$allRes = array_merge($allRes, $allOrganizations);
	  	}

	  	date_default_timezone_set('UTC');
				
	  	/***********************************  EVENT   *****************************************/
        if(strcmp($filter, Event::COLLECTION) != 0 && $this->typeWanted(Event::COLLECTION, $searchType)){
        	
        	$queryEvent = $query;

        	if( !isset( $queryEvent['$and'] ) ) 
        		$queryEvent['$and'] = array();
        	
        	array_push( $queryEvent[ '$and' ], array( "endDate" => array( '$gte' => new MongoDate( time() ) ) ) );
        	
        	$allEvents = PHDB::findAndSortAndLimitAndIndex( PHType::TYPE_EVENTS, $queryEvent, 
	  										array("startDate" => 1), $indexStep, $indexMin);
	  		foreach ($allEvents as $key => $value) {
	  			$allEvents[$key]["type"] = "event";
				$allEvents[$key]["typeSig"] = Event::COLLECTION;
				if(@$value["links"]["attendees"][Yii::app()->session["userId"]]){
		  			$allEvents[$key]["isFollowed"] = true;
	  			}
				if(@$allEvents[$key]["startDate"])
				$allEvents[$key]["startDate"] = date('Y-m-d H:i:s', $allEvents[$key]["startDate"]->sec);
				if(@$allEvents[$key]["endDate"])
				$allEvents[$key]["endDate"] = date('Y-m-d H:i:s', $allEvents[$key]["endDate"]->sec);
	  		}
	  		
	  		$allRes = array_merge($allRes, $allEvents);
	  	}
	  	error_log("recherche - indexMin : ".$indexMin." - "." indexMax : ".$indexMax);
	  	/***********************************  PROJECTS   *****************************************/
        if(strcmp($filter, Project::COLLECTION) != 0 && $this->typeWanted(Project::COLLECTION, $searchType)){
        	$allProject = PHDB::findAndSortAndLimitAndIndex(Project::COLLECTION, $query, 
	  												array("updated" => -1), $indexStep, $indexMin);
	  		foreach ($allProject as $key => $value) {
	  			if(@$project["links"]["followers"][Yii::app()->session["userId"]]){
		  			$allProject[$key]["isFollowed"] = true;
	  			}
				$allProject[$key]["type"] = "project";
				$allProject[$key]["typeSig"] = Project::COLLECTION;
				
				if(@$allProject[$key]["startDate"])
					$allProject[$key]["startDate"] = date('Y-m-d H:i:s', @$allProject[$key]["startDate"]->sec);
				if(@$allProject[$key]["endDate"])
					$allProject[$key]["endDate"] = date('Y-m-d H:i:s', @$allProject[$key]["endDate"]->sec);
	  		}
	  		//$res["project"] = $allProject;
	  		$allRes = array_merge($allRes, $allProject);
	  		error_log(sizeof($allProject));
	  	}

	  	/***********************************  DDA   *****************************************/
        if(strcmp($filter, ActionRoom::COLLECTION) != 0 && $this->typeWanted(ActionRoom::COLLECTION, $searchType))
        {
        	$queryDiscuss = $query;
        	if( !isset( $queryDiscuss['$and'] ) ) 
        		$queryDiscuss['$and'] = array();
        	array_push( $queryDiscuss[ '$and' ], array( "type" => ActionRoom::TYPE_DISCUSS ) );
        	$allFound = PHDB::findAndSort(ActionRoom::COLLECTION, $queryDiscuss, array("updated" => -1), $indexMax);
	  		foreach ($allFound as $key => $value) {
				$allFound[$key]["type"] = $value["type"];
				$allFound[$key]["typeSig"] = ActionRoom::COLLECTION;
	  		}
	  		$allRes = array_merge($allRes, $allFound);
	  		

        	$allFound = PHDB::findAndSort(ActionRoom::COLLECTION_ACTIONS, $query, array("updated" => -1), $indexMax);
	  		foreach ($allFound as $key => $value) {
				$allFound[$key]["type"] = $value["type"];
				$allFound[$key]["typeSig"] = ActionRoom::COLLECTION_ACTIONS;
	  		}
	  		$allRes = array_merge($allRes, $allFound);

        	$allFound = PHDB::findAndSort(Survey::COLLECTION, $query, array("updated" => -1), $indexMax);
	  		foreach ($allFound as $key => $value) {
				$allFound[$key]["type"] = $value["type"];
				$allFound[$key]["typeSig"] = Survey::CONTROLLER;
	  		}
	  		//$res["project"] = $allProject;
	  		$allRes = array_merge($allRes, $allFound);
	  	}
		    
	  	/***********************************  CITIES   *****************************************/
        if(strcmp($filter, City::COLLECTION) != 0 && $this->typeWanted(City::COLLECTION, $searchType)){
	  		$query = array( "name" => new MongoRegex("/".self::wd_remove_accents($search)."/i"));//array('$text' => array('$search' => $search));//
	  		
	  		/***********************************  DEFINE LOCALITY QUERY   *****************************************/
	        	if($locality == null || $locality == "")
		    		$locality = $search;
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
	  		if(isset($a["_id"]) && isset($b["name"])){
		   		return ( strtolower($b["name"]) < strtolower($a["name"]) );
		    } else{
				return false;
			}
		}
	  	
	  	//trie les éléments dans l'ordre alphabetique par updated
	  	function mySortByUpdated($a, $b){ // error_log("sort : ");//.$a['name']);
	  		if(isset($a["updated"]) && isset($b["updated"])){
		   		return ( strtolower($b["updated"]) > strtolower($a["updated"]) );
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

	  	
	  	/*if(isset($allRes)) {
	  		if($latest)
	  			usort($allRes, "mySortByUpdated");
	  		else
	  			usort($allRes, "mySortByName");
	  	}*/


	  	if(isset($allCitiesRes)) usort($allCitiesRes, "mySortByName");

	  	//error_log("count : " . count($allRes));
	  	if(count($allRes) < $indexMax) 
	  		if(isset($allCitiesRes)) 
	  			$allRes = array_merge($allRes, $allCitiesRes);

	  	$limitRes = $allRes;
	  	/*
	  	$limitRes = array();
	  	$index = 0;
	  	foreach ($allRes as $key => $value) {
	  		if($index < $indexMax && $index >= $indexMin){ $limitRes[] = $value;
		  	}//else{ break; }
		  	$index++;
	  	}
		*/

  		//Rest::json($res);
  		if(@$_POST['tpl'])
  			echo $this->getController()->renderPartial($_POST['tpl'], array("result"=>$limitRes));
  		else
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


	/**
	* Returns a string with accent to REGEX expression to find any combinations
	* in accent insentive way
	*
	* @param string $text The text.
	* @return string The REGEX text.
	*/

	static public function accentToRegex($text)
	{

		$from = str_split(utf8_decode('ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËẼÌÍÎÏĨÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëẽìíîïĩðñòóôõöøùúûüýÿ'));
		$to   = str_split(strtolower('SOZsozYYuAAAAAAACEEEEEIIIIIDNOOOOOOUUUUYsaaaaaaaceeeeeiiiiionoooooouuuuyy'));
		//‘ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËẼÌÍÎÏĨÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëẽìíîïĩðñòóôõöøùúûüýÿaeiouçAEIOUÇ';
		//‘SOZsozYYuAAAAAAACEEEEEIIIIIDNOOOOOOUUUUYsaaaaaaaceeeeeiiiiionoooooouuuuyyaeioucAEIOUÇ';
		$text = utf8_decode($text);
		$regex = array();

		foreach ($to as $key => $value)
		{
			if (isset($regex[$value]))
				$regex[$value] .= $from[$key];
			else 
				$regex[$value] = $value;
		}

		foreach ($regex as $rg_key => $rg)
		{
			$text = preg_replace("/[$rg]/", "_{$rg_key}_", $text);
		}

		foreach ($regex as $rg_key => $rg)
		{
			$text = preg_replace("/_{$rg_key}_/", "[$rg]", $text);
		}
		return utf8_encode($text);
	}
}