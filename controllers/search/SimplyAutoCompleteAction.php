<?php
class SimplyAutoCompleteAction extends CAction
{
	public function run($filter = null)
    {

  		// $pathParams = Yii::app()->controller->module->viewPath.'/default/dir/';
		// echo file_get_contents($pathParams."simply.json");
		// die();
        $search = isset($_POST['name']) ? trim(urldecode($_POST['name'])) : null;
        $locality = isset($_POST['locality']) ? trim(urldecode($_POST['locality'])) : null;
        $scope = isset($_POST['scope']) ? $_POST['scope'] : null;
        $searchType = isset($_POST['searchType']) ? $_POST['searchType'] : null;
        $searchTags = isset($_POST['searchTag']) ? $_POST['searchTag'] : null;
        $searchPrefTag = isset($_POST['searchPrefTag']) ? $_POST['searchPrefTag'] : null;
        $searchBy = isset($_POST['searchBy']) ? $_POST['searchBy'] : "INSEE";
        $indexMin = isset($_POST['indexMin']) ? $_POST['indexMin'] : 0;
        $indexMax = isset($_POST['indexMax']) ? $_POST['indexMax'] : 100;
        $country = isset($_POST['country']) ? $_POST['country'] : "";
        $sourceKey = isset($_POST['sourceKey']) ? $_POST['sourceKey'] : null;
        $mainTag = isset($_POST['mainTag']) ? $_POST['mainTag'] : null;
        $paramsFiltre = isset($_POST['paramsFiltre']) ? $_POST['paramsFiltre'] : null;

        $parent = isset($_POST['parent']) ? $_POST['parent'] : null;


   //      if($search == null && $sourceKey == null) {
   //      	Rest::json(array());
			// Yii::app()->end();
   //      }

        $getCreator = false ;
  //       if( $sourceKey != null && $sourceKey != "" && strpos($sourceKey[0], "@") > 0 ) {
  //       	$split = explode("@", $sourceKey[0]);
  //       	$query = array();
  //       	try{
  //       		$element = Element::getByTypeAndId($split[1], $split[0]);
	 //        	if(!empty($element) && 
	 //        		(	$split[1] != Person::COLLECTION || 
  //                    Preference::showPreference($element, $split[1], "directory", Yii::app()->session["userId"]) ) ) {

	 //        		$query = array("creator" => $split[0]);
		//         	$links = array("events", "projects", "followers", "members", "memberOf", "subEvents", "follows", "attendees", "organizer", "contributors");
		//         	foreach ($links as $key => $value) {
		//         		$query = array('$or' => array($query, array("links.".$value.".".$split[0] => array('$exists' => 1))));
		//         	}
		//         	$getCreator = true ;
	 //        	}
  //       	}catch (MongoException $m){
				
		// 	}
		// }

		if( !empty($parent) ) {
			//Rest::json($parent); exit ;
			$query = array();
			if(Organization::COLLECTION == $parent["type"] || Project::COLLECTION == $parent["type"]){
				if(strcmp($filter, Organization::COLLECTION) != 0 && Search::typeWanted(Organization::COLLECTION, $searchType)){
					$q = array("links.memberOf.".$parent["id"]=> array('$exists' => 1) );
					$query = Search::concatQuery($query, $q, '$or');
				}

				if(strcmp($filter, Project::COLLECTION) != 0 && Search::typeWanted(Project::COLLECTION, $searchType)){
					$q = array("links.contributors.".$parent["id"]=> array('$exists' => 1) );
					$query = Search::concatQuery($query, $q, '$or');
				}
					

				if(strcmp($filter, Event::COLLECTION) != 0 && Search::typeWanted(Event::COLLECTION, $searchType)){
					$q = array("links.organizer.".$parent["id"]=> array('$exists' => 1) );
					$query = Search::concatQuery($query, $q, '$or');
				}

				if(strcmp($filter, Poi::COLLECTION) != 0 && Search::typeWanted(Poi::COLLECTION, $searchType)){
					$q = array("parentType" => $parent["type"], "parentId" => $parent["id"]);
					$query = Search::concatQuery($query, $q, '$or');
				}

				if(strcmp($filter, Classified::COLLECTION) != 0 && Search::typeWanted(Classified::COLLECTION, $searchType)){
					$q = array("parentId"=> $parent["id"] );
					$query = Search::concatQuery($query, $q, '$or');
					$q = array("links.organizer.".$parent["id"]=> array('$exists' => 1) );
					$query = Search::concatQuery($query, $q, '$or');
					$q = array("organizerId" => $parent["id"] );
					$query = Search::concatQuery($query, $q, '$or');
					//Rest::json($query);exit;
				}
			}

			//Rest::json($query); exit ;
			
		}  else {
			/***********************************  DEFINE GLOBAL QUERY   *****************************************/

			$query = array();
			$query = Search::searchString($search, $query);

			/***********************************  TAGS   *****************************************/

			if(!empty($searchTags)) {
				$verbTag = ( (!empty($paramsFiltre) && '$all' == $paramsFiltre) ? '$all' : '$in' ) ;
		  		$queryTags =  Search::searchTags($searchTags, $verbTag) ;

				if( !empty($queryTags) )
					$query = array('$and' => array( $query , $queryTags) );
			}

	  		/***********************************  COMPLETED   *****************************************/
	  		$query = array('$and' => array( $query , array("state" => array('$ne' => "uncomplete")) ));

	      /***********************************   MAINTAG    *****************************************/
	    	if(!empty($mainTag)){
				$verbMainTag = ( (!empty($searchPrefTag) && '$or' == $searchPrefTag) ? '$or' : '$and' );
				$queryTags =  Search::searchTags($mainTag, $verbMainTag) ;
				if( !empty($queryTags) )
					$query = array('$and' => array( $query , $queryTags) );
			}
			$query = Search::searchSourceKey($sourceKey, $query);
	  	}

	  	$query =  Search::searchLocalityNetworkOld($query, $_POST);
	  	if(!empty($scope))
	  		$query =  Search::searchLocalityNetwork($scope, $query);
	  	//Rest::json($query);exit;
	    $allRes = array();
        /***********************************  PERSONS   *****************************************/
       if(strcmp($filter, Person::COLLECTION) != 0 && Search::typeWanted("citoyen", $searchType)){

        	$allCitoyen = PHDB::find ( Person::COLLECTION , $query, array("name", "address", "shortDescription", "description"));
	  		foreach ($allCitoyen as $key => $value) {
	  			$person = Person::getSimpleUserById($key, $value);
	  			$person["type"] = "citoyen";
				$person["typeSig"] = "citoyens";
				$allCitoyen[$key] = $person;
	  		}
	  		$allRes = array_merge($allRes, $allCitoyen);
	  	}

	  	/***********************************  ORGANISATIONS   *****************************************/


	  	if(strcmp($filter, Organization::COLLECTION) != 0 && Search::typeWanted(Organization::COLLECTION, $searchType)){

	  		//Rest::json($query); exit ;
			$allRes = array_merge($allRes, Search::searchOrganizations($query, 0, $indexMin,  $searchType, null));
	  	}

	  	/***********************************  EVENT   *****************************************/

	  	if(strcmp($filter, Event::COLLECTION) != 0 && Search::typeWanted(Event::COLLECTION, $searchType)){

			if(!empty($startDate)){
				array_push( $query[ '$and' ], array( "startDate" => array( '$gte' => new MongoDate( (float)$startDate ) ) ) );
       		}
       		if(!empty($endDate)){
       			array_push( $query[ '$and' ], array( "endDate" => array( '$lte' => new MongoDate( (float)$endDate ) ) ) );
       		}

			$allRes = array_merge($allRes, Search::searchEvents($query, 0, $indexMin, null));
	  	}

	  	/***********************************  PROJECTS   *****************************************/

	  	if(strcmp($filter, Project::COLLECTION) != 0 && Search::typeWanted(Project::COLLECTION, $searchType)){
			$allRes = array_merge($allRes, Search::searchProject($query, 0, $indexMin));
	  	}

	  	if(strcmp($filter, Poi::COLLECTION) != 0 && Search::typeWanted(Poi::COLLECTION, $searchType)){
			$allRes = array_merge($allRes, Search::searchPoi($query, 0, $indexMin));
	  	}

	  	//*********************************  CLASSIFIED   ******************************************
		if(strcmp($filter, Classified::COLLECTION) != 0 && Search::typeWanted(Classified::COLLECTION, $searchType)){

			if(!empty($searchTags) && in_array("favorites", $searchTags))
				$allRes = array_merge($allRes, Search::searchFavorites(Classified::COLLECTION));
			else 
				$allRes = array_merge($allRes, Search::searchClassified($query, 0, $indexMin, @$priceMin, @$priceMax, @$devise));
	  	}

	  	/***********************************  CITIES   *****************************************/
        if(strcmp($filter, City::COLLECTION) != 0 && Search::typeWanted("cities", $searchType)){
	  		$query = array( "name" => new MongoRegex("/".Search::wd_remove_accents($search)."/i"));//array('$text' => array('$search' => $search));//

	  		/***********************************  DEFINE LOCALITY QUERY   *****************************************/
	        	if($locality == null || $locality == ""){
		    		$locality = $search;
		    	}
		    	$type = Search::getTypeOfLocalisation($locality);
		    	if($searchBy == "INSEE") $type = $searchBy;
	        	error_log("type " . $type);
	    		if($type == "NAME"){
	        		$query = array('$or' => array( array( "name" => new MongoRegex("/".Search::wd_remove_accents($locality)."/i")),
	        									   array( "alternateName" => new MongoRegex("/".Search::wd_remove_accents($locality)."/i")),
	        									   array("postalCodes.name" => array('$in' => array(new MongoRegex("/".Search::wd_remove_accents($locality)."/i"))))
	        					));
	        		//error_log("search city with : " . Search::wd_remove_accents($locality));
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

		//Rest::json($allRes); exit;
	  	if(isset($allRes)) //si on a des resultat dans la liste
	  		if(!Search::typeWanted("events", $searchType)) //si on n'est pas en mode "event" (les event sont classé par date)
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
}
