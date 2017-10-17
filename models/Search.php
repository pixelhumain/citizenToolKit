<?php

class Search {

	/**
	 * Find elements of collection based on criteria (field contains value)
	 * By default the criterias will be separated bay a "OR"
	 * @param array $criterias array (field=>value)
	 * @param String $sortOnField sort on this field name
	 * @param integer $nbResultMax number of results max to return
	 * @return array of elements of collection
	 */
	public static function findByCriterias($collection, $criterias, $sortOnField="", $nbResultMax = 10) {

	  	$seprator = '$or';
	  	$query = array();

	  	//Add the criterias 
	  	foreach ($criterias as $field => $value) {
	  		$aCriteria = array();
	  		$aCriteria[$field] = new MongoRegex("/$value/i");
	  		array_push($query, $aCriteria);
	  	}

	  	if (count($criterias) > 1) {
	  		$where = array($seprator => $query);
	  	} else {
	  		$where = $query;
	  	}

	  	$res = PHDB::findAndSort($collection, $where, array($sortOnField => 1), $nbResultMax);
	  	//$res = PHDB::find($collection, $where);
	  	return $res;
	 }

	public static function accentToRegex($text) {

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

	public static function globalAutoComplete($post,  $filter = null, $api=false){

		$search = @$post['name'] ? trim(urldecode($post['name'])) : "";
        $searchLocality = isset($post['locality']) ? $post['locality'] : null;
        //$localities = isset($post['localities']) ? $post['localities'] : null;
        $searchType = isset($post['searchType']) ? $post['searchType'] : null;
        $searchTags = isset($post['searchTag']) ? $post['searchTag'] : null;
        $indexMin = isset($post['indexMin']) ? $post['indexMin'] : 0;
        $indexMax = isset($post['indexMax']) ? $post['indexMax'] : 30;
        $country = isset($post['country']) ? $post['country'] : "";
        $priceMin = isset($_POST['priceMin']) ? $_POST['priceMin'] : null;
        $priceMax = isset($_POST['priceMax']) ? $_POST['priceMax'] : null;
        $devise = isset($_POST['devise']) ? $_POST['devise'] : null;
        $latest = isset($_POST['latest']) ? $_POST['latest'] : null;
        $searchSType = !empty($post['searchSType']) ? $post['searchSType'] : "";


        $indexStep = $indexMax - $indexMin;
        
        $searchTypeOrga = ""; /* used in CO2 to find different organisation type */
        
        if( sizeOf($searchType) == 1 &&
        	@$searchType[0] == Organization::TYPE_NGO ||
         	@$searchType[0] == Organization::TYPE_BUSINESS ||
         	@$searchType[0] == Organization::TYPE_GROUP ||
        	@$searchType[0] == Organization::TYPE_GOV) {
	        	$searchTypeOrga = $searchType[0];
	        	$searchType = array(Organization::COLLECTION);
        }

        //*********************************  DEFINE GLOBAL QUERY   ******************************************
        $query = array();

        $query = self::searchString($search, $query);
       
        $query = array('$and' => array( $query , array("state" => array('$ne' => "uncomplete")) ));
  		if($latest)
  			$query = array('$and' => array($query, array("updated"=>array('$exists'=>1))));

  		if($api == true){
  			//$query = array('$and' => array($query, array("preferences.isOpenData"=> true)));
  		}

  		
  		
        //*********************************  TAGS   ******************************************


  		if( /*!empty($searchTags)*/ count($searchTags) > 1  || count($searchTags) == 1 && $searchTags[0] != "" ){
  			if( (strcmp($filter, Classified::COLLECTION) != 0 && self::typeWanted(Classified::COLLECTION, $searchType)) ||
  				(strcmp($filter, Place::COLLECTION) != 0 && self::typeWanted(Place::COLLECTION, $searchType)) ){
        		$queryTags =  self::searchTags($searchTags, '$all') ;
	  		}
  			else 
  				$queryTags =  self::searchTags($searchTags) ;
  			if(!empty($queryTags))
  				$query = array('$and' => array( $query , $queryTags) );
  		}
  		//unset($tmpTags);
  		//var_dump($query);

  		//*********************************  DEFINE LOCALITY QUERY   ****************************************
  		//$query = array('$and' => array( $query , self::searchLocality($post, $query) ) );
  		if(!empty($searchLocality))
  			$query = self::searchLocality($searchLocality, $query);
  		
  		//var_dump($query);
  		$allRes = array();

  		//*********************************  CITIES   ******************************************
  		if(!empty($search) && !empty($locality)){
	        if(strcmp($filter, City::COLLECTION) != 0 && self::typeWanted(City::COLLECTION, $searchType)){
		  		$allCitiesRes = self::searchCities($search, $locality, $country);
		  	}

		  	if(isset($allCitiesRes)) usort($allCitiesRes, "self::mySortByName");

		  	if(count($allRes) < $indexMax){
		  		if(isset($allCitiesRes)) 
		  			$allRes = array_merge($allRes, $allCitiesRes);
		  	} 
		}
	  		
        //*********************************  PERSONS   ******************************************
       	if(strcmp($filter, Person::COLLECTION) != 0 && (self::typeWanted(Person::COLLECTION, $searchType) || self::typeWanted("persons", $searchType) ) ) {
       		$localityReferences['CITYKEY'] = "";
	  		$localityReferences['CODE_POSTAL'] = "address.postalCode";
	  		$localityReferences['DEPARTEMENT'] = "address.postalCode";
	  		$localityReferences['REGION'] = ""; //Spécifique
	  		$prefLocality = false;
	  		foreach ($localityReferences as $key => $value){
	  			if(!empty($post["searchLocality".$key])){
	  				$prefLocality = true;
	  			} 
	  		}
        	$allRes = array_merge($allRes, self::searchPersons($query, $indexStep, $indexMin, $prefLocality));

	  	}

	  	//*********************************  ORGANISATIONS   ******************************************
        if(strcmp($filter, Organization::COLLECTION) != 0 && self::typeWanted(Organization::COLLECTION, $searchType)){
        	$allRes = array_merge($allRes, self::searchOrganizations($query, $indexStep, $indexMin,  $searchType, $searchTypeOrga));
	  	}

	  	date_default_timezone_set('UTC');
				
	  	//*********************************  EVENT   ******************************************
        if(strcmp($filter, Event::COLLECTION) != 0 && self::typeWanted(Event::COLLECTION, $searchType)){
        	$allRes = array_merge($allRes, self::searchEvents($query, $indexStep, $indexMin, $searchSType));
	  	}
	  	//*********************************  PROJECTS   ******************************************
        if(strcmp($filter, Project::COLLECTION) != 0 && self::typeWanted(Project::COLLECTION, $searchType)){
        	$allRes = array_merge($allRes, self::searchProject($query, $indexStep, $indexMin));
	  	}
		//*********************************  CLASSIFIED   ******************************************
        if(strcmp($filter, Classified::COLLECTION) != 0 && self::typeWanted(Classified::COLLECTION, $searchType)){
        	//var_dump($query) ; exit;
        	if(!empty($searchTags) && in_array("favorites", $searchTags))
        		$allRes = array_merge($allRes, self::searchFavorites(Classified::COLLECTION));
        	else 
        		$allRes = array_merge($allRes, self::searchClassified($query, $indexStep, $indexMin, @$priceMin, @$priceMax, @$devise));
	  	}
	  	//*********************************  POI   ******************************************
        if(strcmp($filter, Poi::COLLECTION) != 0 && self::typeWanted(Poi::COLLECTION, $searchType)){
        	$allRes = array_merge($allRes, self::searchPoi($query, $indexStep, $indexMin));
	  	}

	  	//*********************************  PLACE   ******************************************
        if(strcmp($filter, Place::COLLECTION) != 0 && self::typeWanted(Place::COLLECTION, $searchType)){
        	$allRes = array_merge($allRes, self::searchPlace($query, $indexStep, $indexMin));
	  	}

	  	//*********************************  DDA   ******************************************
        if(strcmp($filter, ActionRoom::COLLECTION) != 0 && self::typeWanted(ActionRoom::COLLECTION, $searchType)){
        	$allRes = array_merge($allRes, self::searchDDA($query, $indexMax));
	  	}

		//*********************************  VOTES / propositions   ******************************************
		//error_log(print_r($searchType)); 
		//error_log("filter : ".$filter);
        if(isset(Yii::app()->session["userId"]) && 
        	//(strcmp($filter, ActionRoom::TYPE_VOTE) != 0 && 
        		self::typeWanted(ActionRoom::TYPE_VOTE, $searchType) ||
        	//(strcmp($filter, ActionRoom::TYPE_ACTIONS) != 0 && 
        		self::typeWanted(ActionRoom::TYPE_ACTIONS, $searchType)
        	 ){    
        	$allRes = array_merge($allRes, self::searchVotes($query, $indexStep, $indexMin, $searchType));
        }

	  	
	  	if(@$post['tpl'] == "/pod/nowList"){
	  		usort($allRes, "self::mySortByUpdated");
	  	}

	  	foreach ($allRes as $key => $value) {
			if(@$value["updated"]) {
				if(self::typeWanted(Event::COLLECTION, $searchType))
					$allRes[$key]["updatedLbl"] = Translate::pastTime(@$value["startDate"],"date");
				else
					$allRes[$key]["updatedLbl"] = Translate::pastTime(@$value["updated"],"timestamp");
	  		}
	  	}
	  	//var_dump($allRes);
	  	return $allRes ;
    }

    //*********************************  Search   ******************************************
	public static function searchString($search, $query){

        if(strpos($search, "#") > -1){
        	$searchTagText = substr($search, 1, strlen($search)); 
        	$query = self::searchTags(array($searchTagText));
  		}else{
  			$searchRegExp = self::accentToRegex($search);
  			$query = array( "name" => new MongoRegex("/.*{$searchRegExp}.*/i"));
	        $explodeSearchRegExp = explode(" ", $searchRegExp);
	        if(count($explodeSearchRegExp)>1){
		        $andArray=array();
		        foreach($explodeSearchRegExp as $data){
			        array_push($andArray,array("name" => new MongoRegex("/.*{$data}.*/i")));
		        }
		        $query = array('$or' => array($query,array('$and'=> $andArray)));
	        }
  		}
        
  		return $query;
  		
	}

	//*********************************  TAGS   ******************************************
	public static function searchTags($searchTags, $verb = '$in' ){
        $tmpTags = array();
        $query = array();
  		if(!empty($searchTags)){
  			foreach ($searchTags as $value) {
  				if(trim($value) != "")
	  				//$tmpTags[] = new MongoRegex("/^".$value."$/i");
	  				$tmpTags[] = new MongoRegex("/^".self::accentToRegex($value)."$/i");
	  		}
	  		if(count($tmpTags)){
	  			$allverb = array('$in', '$all');
	  			if(!in_array($verb, $allverb))
	  				$verb = '$in';
	  			$query = array("tags" => array($verb => $tmpTags)) ;
	  		}
  		}
  		return $query;
  		
	}

	//*********************************  Zones   ******************************************
	public static function searchZones($localities){
		$query = array();
		foreach ($localities as $key => $locality){

			$zone = PHDB::findOne( "zones", array("_id"=>new MongoId($locality["id"])));
			//if($zone["level"] == 0){
				$queryLocality = array( /*"address.addressCountry" => $zone["country"],*/
										"address.codeInsee" => $zone["insee"],
										'geoPosition' => array(
											'$geoWithin'  => array(
												'$polygon' => $zone["geoShape"]["coordinates"][0]) ) );
			//}
			if(empty($query))
				$query = $queryLocality;
			else if(!empty($queryLocality))
				$query = array('$or' => array($query ,$queryLocality));

		}
		return $query ;
	}

	//*********************************  Zones   ******************************************
	public static function searchLocality($localities, $query){
		$allQueryLocality = array();
		foreach ($localities as $key => $locality){
			if(!empty($locality)){
				if($locality["type"] == City::CONTROLLER)
					$queryLocality = array("address.localityId" => $key);
				else if($locality["type"] == "cp")
					$queryLocality = array("address.postalCode" => new MongoRegex("/^".$key."/i"));
				else
					$queryLocality = array("address.".$locality["type"] => $key);
				
			
				if(empty($allQueryLocality))
					$allQueryLocality = $queryLocality;
				else if(!empty($queryLocality))
					$allQueryLocality = array('$or' => array($allQueryLocality ,$queryLocality));
			}
		}
		if(!empty($allQueryLocality))
			$query = array('$and' => array($allQueryLocality));
		//var_dump($query); exit;
		
		return $query ;
	}

	//*********************************  DEFINE LOCALITY QUERY   ****************************************

  	//trie les éléments dans l'ordre alphabetique par name
  	public static function mySortByName($a, $b){ // error_log("sort : ");//.$a['name']);
  		if(isset($a["_id"]) && isset($b["name"])){
	   		return ( strtolower($b["name"]) < strtolower($a["name"]) );
	    } else{
			return false;
		}
	}
  	
  	//trie les éléments dans l'ordre alphabetique par updated
  	public static function mySortByUpdated($a, $b){ // error_log("sort : ");//.$a['name']);
  		if(isset($a["updated"]) && isset($b["updated"])){
	   		return ( strtolower($b["updated"]) > strtolower($a["updated"]) );
	    } else{
			return false;
		}
	}

	//supprime les accents (utilisé pour la recherche de ville pour améliorer les résultats)
    public static function wd_remove_accents($str, $charset='utf-8')
	{
		return $str;
	    $str = htmlentities($str, ENT_NOQUOTES, $charset);
	    
	    $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
	    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
	    $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
	    
	    return $str;
	}

	public static function getTypeOfLocalisation($locStr){
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

	public static function typeWanted($type, $searchType){
		if($searchType == null || $searchType[0] == "all") return true;
		return in_array($type, $searchType);
	}



	public static function checkScopeParent($parentObj){ //error_log("checkScopeParent");
		$localityReferences['CITYKEY'] = "";
  		$localityReferences['CODE_POSTAL'] = "address.postalCode";
  		$localityReferences['DEPARTEMENT'] = "address.postalCode";
  		$localityReferences['REGION'] = ""; //Spécifique

  		$countScope = 0;
  		foreach ($localityReferences as $key => $value){
  			if(isset($_POST["searchLocality".$key]) && count($_POST["searchLocality".$key])>0 && $_POST["searchLocality".$key][0] != "" ){ 
  				$countScope++; 
  			}
  		}
  		if($countScope==0){ //error_log("return true EMPTY"); 
  			return true; }
  		
		foreach ($localityReferences as $key => $value) 
  		{

  			if(isset($_POST["searchLocality".$key]) 
  				&& is_array($_POST["searchLocality".$key])
  				&& count($_POST["searchLocality".$key])>0)
  			{
  				foreach ($_POST["searchLocality".$key] as $localityRef) 
  				{
  					if(isset($localityRef) && $localityRef != ""){
	  					//OneRegion
	  					if($key == "CITYKEY"){
	  						
	  						$city = City::getByUnikey($localityRef);
			        		if (empty($city["cp"])) {
				        		if(@$parentObj["address"]["addressCountry"] == $city["country"] &&
				        		   @$parentObj["address"]["codeInsee"] == $city["insee"]) return true;
			        		}else{
			        			if(@$parentObj["address"]["addressCountry"] == $city["country"] &&
				        		   @$parentObj["address"]["codeInsee"] == $city["insee"] &&
				        		   @$parentObj["address"]["postalCode"] == $city["cp"]) return true;
			        		}
			        		
		  				}
		  				elseif($key == "CODE_POSTAL"){
		  					if(@$parentObj["address"]["postalCode"] == $localityRef) return true;
			        		//$queryLocality = array($value => new MongoRegex("/".$localityRef."/i"));
		  				}
		  				elseif($key == "DEPARTEMENT"){
		  					$dep = PHDB::findOne( City::COLLECTION, array("depName" => $localityRef), array("dep"));	
		        			if(preg_match("/^{$dep['dep']}/i", @$parentObj["address"]["postalCode"])) return true;
		  				}
		  				elseif($key == "REGION"){
		  					$deps = PHDB::find( City::COLLECTION, array("regionName" => $localityRef), array("dep"));
		        			$departements = array();
		        			$inQuest = array();
		        			if(is_array($deps))foreach($deps as $index => $value)
		        			{
		        				if(!in_array($value["dep"], $departements)){
			        				$departements[] = $value["dep"];
			        				if(preg_match("/^{$value['dep']}/i", @$parentObj["address"]["postalCode"])) return true;
						        }
		        			}		        		
		  				}
		  			}
  				}
  			}
  		}
  		return false;
	}

	public static function checkTagsParent($parentObj, $tags){ //return true;
		if(count($tags)<=0) return true;
		foreach ($tags as $key => $tag) { error_log("checkTagsParent tag : " .$tag);
			if(@$parentObj["tags"]){
				foreach ($parentObj["tags"] as $key => $parentTag) { error_log("checkTagsParent parentTag : " .$parentTag);
					if(preg_match("/.*{$tag}.*/i", $parentTag)){
						error_log("checkTagsParent return true");
						return true;
					}
				}
			}
		}error_log("checkTagsParent return false");
						
		return false;
	}

	//*********************************  PERSONS   ******************************************
  	public static function searchPersons($query, $indexStep, $indexMin, $prefLocality=false){
       	$res = array();
       	$allCitoyen = PHDB::findAndSortAndLimitAndIndex ( Person::COLLECTION , $query, 
  										  array("updated" => -1), $indexStep, $indexMin);

  		foreach ($allCitoyen as $key => $value) {

  			if( $prefLocality == false ||  
  				Preference::showPreference($value, Person::COLLECTION, "locality", Yii::app()->session["userId"])){
  				$person = Person::getSimpleUserById($key,$value);
	  			$person["type"] = Person::COLLECTION;
				$person["typeSig"] = "citoyens";
				$res[$key] = $person;
  			}


  			
  		}
  		return $res;
	}


	//*********************************  ORGANIZATIONS   ******************************************
  	public static function searchOrganizations($query, $indexStep, $indexMin, $searchType, $searchTypeOrga){
       	$res = array();
    	$queryOrganization = $query;
    	if( !isset( $queryOrganization['$and'] ) ) 
    		$queryOrganization['$and'] = array();

    	array_push( $queryOrganization[ '$and' ], array( "disabled" => array('$exists' => false) ) );

    	if(sizeof($searchType)==1 && @$searchTypeOrga != "")
    		array_push( $queryOrganization[ '$and' ], array( "type" => $searchTypeOrga ) );

    	//var_dump($indexStep);
    	//var_dump($indexMin);
  		$allOrganizations = PHDB::findAndSortAndLimitAndIndex ( Organization::COLLECTION ,$queryOrganization, 
  												array("updated" => -1), $indexStep, $indexMin);

  		//var_dump($allOrganizations);
  		foreach ($allOrganizations as $key => $value) 
  		{
  			if(!empty($value)){

	  			$orga = Organization::getSimpleOrganizationById($key,$value);
	  			if( @$value["links"]["followers"][Yii::app()->session["userId"]] )
		  			$orga["isFollowed"] = true;

		  		if(@$orga["type"] != "")
					$orga["typeOrga"] = $orga["type"];
				$orga["type"] = "organizations";

				$orga["typeSig"] = Organization::COLLECTION;
				$res[$key] = $orga;
			}
  		}
  		return $res;
	}

	

	//*********************************  EVENT   ******************************************
	public static function searchEvents($query, $indexStep, $indexMin, $searchSType){
		date_default_timezone_set('UTC');
    	$queryEvent = $query;

    	if( !isset( $queryEvent['$and'] ) ) 
    		$queryEvent['$and'] = array();
    	
    	array_push( $queryEvent[ '$and' ], array( "endDate" => array( '$gte' => new MongoDate( time() ) ) ) );

    	if(isset($searchSType) && $searchSType != "")
        		array_push( $queryEvent[ '$and' ], array( "type" => $_POST["searchSType"] ) );
    	
    	$allEvents = PHDB::findAndSortAndLimitAndIndex( PHType::TYPE_EVENTS, $queryEvent, 
  										array("startDate" => 1), $indexStep, $indexMin);
  		foreach ($allEvents as $key => $value) {
  			$allEvents[$key]["typeEvent"] = @$allEvents[$key]["type"];
			$allEvents[$key]["type"] = "events";
			$allEvents[$key]["typeSig"] = Event::COLLECTION;

			if(@$value["links"]["attendees"][Yii::app()->session["userId"]]){
	  			$allEvents[$key]["isFollowed"] = true;
  			}
			if(@$allEvents[$key]["startDate"]){
				$allEvents[$key]["startDateTime"] = date(DateTime::ISO8601, $allEvents[$key]["startDate"]->sec);
				$allEvents[$key]["startDate"] = date(DateTime::ISO8601, $allEvents[$key]["startDate"]->sec);
			}
			if(@$allEvents[$key]["endDate"]){
				$allEvents[$key]["endDateTime"] = date(DateTime::ISO8601, $allEvents[$key]["endDate"]->sec);
				$allEvents[$key]["endDate"] = date(DateTime::ISO8601, $allEvents[$key]["endDate"]->sec);
			}
			if(@$allEvents[$key]["organizerId"] &&
			   @$allEvents[$key]["organizerType"] &&
			   @$allEvents[$key]["organizerId"] != "dontKnow"){ 

				$allEvents[$key]["organizerObj"] = 
				Element::getElementById(@$allEvents[$key]["organizerId"], @$allEvents[$key]["organizerType"]);
				$allEvents[$key]["organizerObj"]["type"] = @$allEvents[$key]["organizerType"];
			}
  		}
  		return $allEvents;
	  	
	}


	//*********************************  PROJECTS   ******************************************
	public static function searchProject($query, $indexStep, $indexMin){
		date_default_timezone_set('UTC');
        $allProject = PHDB::findAndSortAndLimitAndIndex(Project::COLLECTION, $query, 
	  												array("updated" => -1), $indexStep, $indexMin);
  		foreach ($allProject as $key => $value) {
  			if(@$project["links"]["followers"][Yii::app()->session["userId"]]){
	  			$allProject[$key]["isFollowed"] = true;
  			}
			$allProject[$key]["type"] = "projects";
			$allProject[$key]["typeSig"] = Project::COLLECTION;
			
			if(@$allProject[$key]["startDate"])
				$allProject[$key]["startDate"] = date('Y-m-d H:i:s', @$allProject[$key]["startDate"]->sec);
			if(@$allProject[$key]["endDate"])
				$allProject[$key]["endDate"] = date('Y-m-d H:i:s', @$allProject[$key]["endDate"]->sec);
  		}
  		return $allProject;	
	}

	//*********************************  CLASSIFIED   ******************************************
	public static function searchClassified($query, $indexStep, $indexMin, $priceMin, $priceMax, $devise){

		
		$queryPrice = array('$and' =>	array(array('devise' => $devise)) ) ;
				
		if(@$priceMin) $queryPrice['$and'][] = array('price' => array('$gte' => (int)$priceMin));
		if(@$priceMax) $queryPrice['$and'][] = array('price' => array('$lte' => (int)$priceMax));
		if(@$priceMin || @$priceMax) 
			$query = array('$and' => array( $query , $queryPrice) );
		
		$allClassified = PHDB::findAndSortAndLimitAndIndex(Classified::COLLECTION, $query, 
	  												array("updated" => -1), $indexStep, $indexMin);

  		foreach ($allClassified as $key => $value) {
			if(@$value["parentId"] && @$value["parentType"])
				$parent = Element::getElementSimpleById(@$value["parentId"], @$value["parentType"]);
			else
				$parent=array();
			$allClassified[$key]["parent"] = $parent;
			$allClassified[$key]["category"] = @$allClassified[$key]["type"];
			$allClassified[$key]["type"] = "classified";
			//if(@$value["type"])
			//	$allClassified[$key]["typeSig"] = Classified::COLLECTION.".".$value["type"];
			//else
			$allClassified[$key]["typeSig"] = Classified::COLLECTION;
		}
		return $allClassified;
	}

	//*********************************  CLASSIFIED   ******************************************
	public static function searchFavorites($type){

		$person = Person::getById(Yii::app()->session["userId"]);
		$res = array();

		if( @$person["collections"] && @$person["collections"]["favorites"] && @$person["collections"]["favorites"][$type] ){
			foreach ($person["collections"]["favorites"][$type] as $key => $value) {
				$el = PHDB::findOne($type, array("_id" => new MongoId($key)) );
				$el["type"] = $type;
				$el["typeSig"] = $type;
				$res[$key] = $el;
			}
		}
		return $res;
	}

	//*********************************  POI   ******************************************
	public static function searchPoi($query, $indexStep, $indexMin){
    	$allPoi = PHDB::findAndSortAndLimitAndIndex(Poi::COLLECTION, $query, 
  												array("updated" => -1), $indexStep, $indexMin);
  		foreach ($allPoi as $key => $value) {
	  		if(@$value["parentId"] && @$value["parentType"])
	  			$parent = Element::getElementSimpleById(@$value["parentId"], @$value["parentType"]);
	  		else
	  			$parent=array();
			$allPoi[$key]["parent"] = $parent;
			if(@$value["type"])
				$allPoi[$key]["typeSig"] = Poi::COLLECTION;//.".".$value["type"];
			else
				$allPoi[$key]["typeSig"] = Poi::COLLECTION;
			
			$allPoi[$key]["typePoi"] = @$allPoi[$key]["type"];
			$allPoi[$key]["type"] = Poi::COLLECTION;
  		}
  		return $allPoi;
  	}

  	//*********************************  Place   ******************************************
	public static function searchPlace($query, $indexStep, $indexMin){
    	$allPlace = PHDB::findAndSortAndLimitAndIndex(Place::COLLECTION, $query, 
  												array("updated" => -1), $indexStep, $indexMin);
  		foreach ($allPlace as $key => $value) {
	  		if(@$value["parentId"] && @$value["parentType"])
	  			$parent = Element::getElementSimpleById(@$value["parentId"], @$value["parentType"]);
	  		else
	  			$parent=array();
			$allPlace[$key]["parent"] = $parent;
			if(@$value["type"])
				$allPlace[$key]["typeSig"] = Place::COLLECTION.".".$value["type"];
			else
				$allPlace[$key]["typeSig"] = Place::COLLECTION;
  		}
  		return $allPlace;
  	}



  	//*********************************  DDA   ******************************************
  	public static function searchDDA($query, $indexMax){
  		$allRes = array();
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
  		
    	$allFound = PHDB::findAndSort( ActionRoom::COLLECTION_ACTIONS, $query, array("updated" => -1), $indexMax);
  		foreach ($allFound as $key => $value) {
			$allFound[$key]["type"] = $value["type"];
			$allFound[$key]["typeSig"] = ActionRoom::COLLECTION_ACTIONS;
  		}
  		$allRes = array_merge($allRes, $allFound);

    	$allFound = PHDB::findAndSort( Survey::COLLECTION, $query, array("updated" => -1), $indexMax);
  		foreach ($allFound as $key => $value) {
			$allFound[$key]["type"] = $value["type"];
			$allFound[$key]["typeSig"] = Survey::CONTROLLER;
  		}
  		$allRes = array_merge($allRes, $allFound);
	  	return $allRes;
	}



	//*********************************  VOTES / propositions   ******************************************
    public static function searchVotes($query, $indexStep, $indexMin, $searchType){
    	$allFound = array();
    	if(!empty(Yii::app()->session["userId"])){
    		// $myLinks = Person::getPersonLinksByPersonId( Yii::app()->session["userId"] );
    		// //créer un array avec uniquement les id de mes orgas
	    	// $orgasId = array();
	    	// foreach ($myLinks["organizations"] as $key => $link) {
	    	// 	if(self::checkScopeParent($link) == true)//en vérifiant si l'orga correspond aux scopes demandés
	    	// 		$orgasId[] = (string)$link["_id"];
	    	// }
	    	     
	    	// //créer un array avec uniquement les id de mes projets
	    	// $projectsId = array();
	    	// foreach ($myLinks["projects"] as $key => $link) {
	    	// 	if(self::checkScopeParent($link) == true)//en vérifiant si le projet correspond aux scopes demandés
	    	// 		$projectsId[] = (string)$link["_id"];
	    	// }
	    	
	    	// $query = array( '$or' => array( array("parentType"=>"organizations", "parentId" => array('$in' => $orgasId) ),
	    	// 								array("parentType"=>"projects", "parentId" => array('$in' => $projectsId) )
	    	// 						 )
	    	// 			  );

	    	// //rajoute les résultats pour mon conseil citoyen
	    	$me = Person::getSimpleUserById(Yii::app()->session["userId"]);
	    	$myCityKey = @$me["address"]["addressCountry"] ? $me["address"]["addressCountry"] : false;
	    	if($myCityKey!=false){
	    		$myCityKey .= @$me["address"]["codeInsee"] ? "_".$me["address"]["codeInsee"] : false;
	    		if($myCityKey!=false){
	    			$myCityKey .= @$me["address"]["postalCode"] ? "-".$me["address"]["postalCode"] : "";
	        		//$query['$or'][] = array("parentType"=>"cities", "parentId" => $myCityKey);
	        	}
	    	}
	    	
	    	$query = array();

	    	$allRooms = array(); //PHDB::find( ActionRoom::COLLECTION, $query);
	    	//var_dump($allRooms); exit;

	    	//crée une array avec uniquement les id des rooms
	    	$allRoomsId = array();
	    	foreach ($allRooms as $key => $room) {
	    		$allRoomsId[] = (string)$room["_id"];
	    	}

	    	if(self::typeWanted( ActionRoom::TYPE_VOTE, $searchType)){
				$collection = Survey::COLLECTION;
				$parentRow = "survey";
			}
	    	if(self::typeWanted( ActionRoom::TYPE_ACTIONS, $searchType)){ 
	    		$collection = Action::NODE_ACTIONS;
	    		$parentRow = "room";
	    	}

	    	$query = array();//$parentRow => array('$in' => $allRoomsId) );
	    	
	    	//if(count($tmpTags))
	    	//$query = array('$and' => array( $query , array("tags" => array('$in' => $tmpTags)))) ;
	    	
	    	//echo "search : ". $search." - ".(string)strpos($search, "#");
	    	if(@$search != "" && strpos(@$search, "#") === false){
	        	$searchRegExp = self::accentToRegex($search);
	        	$queryFullTxt = array( '$or' => array( array("name" => new MongoRegex("/.*{$searchRegExp}.*/i")),
	        						   				   array("message" => new MongoRegex("/.*{$searchRegExp}.*/i")))
	        						);
	        	if(isset($query['$and'])) $query['$and'][] = $queryFullTxt;
	        	else $query = array('$and' => array( $query , $queryFullTxt)) ;
	        }

			//var_dump($query); exit;

	        $allFound = PHDB::findAndSortAndLimitAndIndex($collection, $query, array("updated" => -1), $indexStep, $indexMin);

	    	foreach ($allRooms as $keyR => $room) {
	    		//pour chaque room des orga, on ajoute quelques info sur le parentObj
	    		if(@$myLinks)
	    		foreach ($myLinks["organizations"] as $keyL => $orga) {
	    			//error_log("orga " . (string)$orga['_id'] ."==". (string)$room['parentId']);
	    			if((string)$orga['_id'] == (string)$room['parentId'] && $room['parentType'] == "organizations"){
	    				$allRooms[$keyR]["parentObj"]["_id"] 	 = $orga["_id"];
	    				$allRooms[$keyR]["parentObj"]["name"] 	 = $orga["name"];
	    				$allRooms[$keyR]["parentObj"]["address"] = @$orga["address"];
	    				$allRooms[$keyR]["parentObj"]["typeSig"] = $orga["typeSig"];
	    				break;
	    			}
	    		}

	    		//pour chaque room des projets, on ajoute les infos du parentObj
	    		if(@$myLinks)
	    		foreach ($myLinks["projects"] as $keyL => $project) {
	    			//error_log("project " . (string)$project['_id'] ."==". (string)$room['parentId']);
	    			if((string)$project['_id'] == (string)$room['parentId'] && $room['parentType'] == "projects"){
	    				$allRooms[$keyR]["parentObj"]["_id"] 	 = $project["_id"];
	    				$allRooms[$keyR]["parentObj"]["name"] 	 = $project["name"];
	    				$allRooms[$keyR]["parentObj"]["address"] = @$project["address"];
	    				$allRooms[$keyR]["parentObj"]["typeSig"] = $project["typeSig"];
	    				break;
	    			}
	    		}

	    		//les conseils citoyens
	    		if($myCityKey!=false && $room["parentType"] == "cities"){
	    			$myCity = City::getByUnikey($myCityKey); 			
					$cityCheck["name"] = $myCity["name"];
					$cityCheck["address"] = array("postalCode" => $myCity["cp"], "countryCode" => $myCity["country"]);
					$cityCheck["codeInsee"] = $myCity["insee"];
					$cityCheck["geo"] = $myCity["geo"];
					$cityCheck["typeSig"] = "city";

					if(self::checkScopeParent($cityCheck) == true){
	    				$allRooms[$keyR]["parentObj"]["name"] = $cityCheck["name"];
	    				$allRooms[$keyR]["parentObj"]["address"] = @$cityCheck["address"];
	    				$allRooms[$keyR]["parentObj"]["address"]["addressLocality"] = @$cityCheck["name"];
	    				$allRooms[$keyR]["parentObj"]["address"]["addressCountry"] = @$cityCheck["address"]["countryCode"];
	    				$allRooms[$keyR]["parentObj"]["typeSig"] = $cityCheck["typeSig"];
	    				$allRooms[$keyR]["geo"] = @$cityCheck["geo"];
	    			}else{
	    				$allRooms[$keyR] = array();
	    			}
	    		}

	    		$allRooms[$keyR]["typeSig"] = @$allRooms[$keyR]["type"];
	    	}
	    	
	    	//pour chaque resultat, on ajoute les infos du parentRoom
	    	foreach ($allFound as $keyS => $survey) {
	    		foreach ($allRooms as $keyR => $room) {
	    			if((string)$survey[$parentRow] == (string)@$room['_id']){
	    				$allFound[$keyS]["parentRoom"] = $room;
	    				$allFound[$keyS]["geo"] = @$room["geo"];
	    				if($room["parentType"] == "cities")
	    					$allFound[$keyS]["address"] = @$room["parentObj"]["address"];
	    				break;
	    			}else if(!isset($room['_id'])){
	    				unset($allFound[$keyS]);
	    				//$allFound[$keyS] = array();
	    				//break;
	    			}
	    		}
	    		
	    		if(@$allFound[$keyS]["dateEnd"]) $allFound[$keyS]["dateEnd"] =  date("Y-m-d H:i:s", $allFound[$keyS]["dateEnd"]);
				if(@$allFound[$keyS]["endDate"]) $allFound[$keyS]["endDate"] =  date("Y-m-d H:i:s", $allFound[$keyS]["endDate"]);
				if(@$allFound[$keyS]["startDate"]) $allFound[$keyS]["startDate"] =  date("Y-m-d H:i:s", $allFound[$keyS]["startDate"]);
				if(@$allFound[$keyS]["created"]) $allFound[$keyS]["created"] =  date("Y-m-d H:i:s", $allFound[$keyS]["created"]);
				
	    	}
    	}   
    		
    	
    	
    	return $allFound;
    }


    //*********************************  CITIES   ******************************************
    public static function searchCities($search, $locality, $country){   
  		$query = array( "name" => new MongoRegex("/".self::wd_remove_accents($search)."/i"));//array('$text' => array('$search' => $search));//
  		
  		//*********************************  DEFINE LOCALITY QUERY   ******************************************
    	if($locality == null || $locality == "")
    		$locality = $search;
    	
    	$type = self::getTypeOfLocalisation($locality);

		if($type == "NAME"){ 
    		$query = array('$or' => array( array( "name" => new MongoRegex("/".self::wd_remove_accents($locality)."/i")),
    									   array( "alternateName" => new MongoRegex("/".self::wd_remove_accents($locality)."/i")),
    									   array( "postalCodes.name" => array('$in' => array(new MongoRegex("/".self::wd_remove_accents($locality)."/i"))))
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
		  						"depName" => isset($data["depName"]) ? $data["depName"] : "", 
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
  		}

  		return $allCitiesRes;
  	}

    static public function removeEmptyWords($search)
	{
        $stopwords = array(" ", "", "-", "?", "!", ",", ".", "/", "le", "la", "les", "un", "une", "des", "mon", "ton", "son", "pour", 
                            "à", "a", "d", "d'", "de", "notre", "votre", "leur", "leurs", "mes", "tes", "ses", "du");

        $arraySearch = explode(" ", $search);
        $resArraySearch = array();
        foreach ($arraySearch as $key => $word) {
            if(!in_array($word, $stopwords)){
                $resArraySearch[] = $word;
            }
        }

        $resStr = "";
        foreach ($resArraySearch as $key => $word) {
            if($resStr != "") $resStr .= " ";
            $resStr .= $word;
        }
        return $resStr;
    }

}