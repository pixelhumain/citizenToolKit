<?php
class GlobalAutoCompleteAction extends CAction
{
    public function run($filter = null)
    {
    	//ini_set('memory_limit', '-1');
        $search = @$_POST['name'] ? trim(urldecode($_POST['name'])) : "";
        $locality = isset($_POST['locality']) ? trim(urldecode($_POST['locality'])) : null;
        $searchType = isset($_POST['searchType']) ? $_POST['searchType'] : null;
        $searchTag = isset($_POST['searchTag']) ? $_POST['searchTag'] : null;
        $searchBy = isset($_POST['searchBy']) ? $_POST['searchBy'] : "INSEE";
        $indexMin = isset($_POST['indexMin']) ? $_POST['indexMin'] : 0;
        $indexMax = isset($_POST['indexMax']) ? $_POST['indexMax'] : 30;
        $country = isset($_POST['country']) ? $_POST['country'] : "";
        $latest = isset($_POST['latest']) ? $_POST['latest'] : null;

        $indexStep = $indexMax - $indexMin;
        
        $searchTypeOrga = ""; /* used in CO2 to find different organisation type */
        if( sizeOf($searchType) == 1 &&
        	$searchType[0] == Organization::TYPE_NGO ||
         	$searchType[0] == Organization::TYPE_BUSINESS ||
         	$searchType[0] == Organization::TYPE_GROUP ||
        	$searchType[0] == Organization::TYPE_GOV) {
	        	$searchTypeOrga = $searchType[0];
	        	$searchType = array(Organization::COLLECTION);
        } 
       // error_log("global search " . $search . " - searchType : ". $searchType); //. " & locality : ". $locality. " & country : ". $country);
	    
   //      if($search == "" && $locality == "") {
   //      	Rest::json(array());
			// Yii::app()->end();
   //      }

        /***********************************  DEFINE GLOBAL QUERY   *****************************************/
        $query = array();
        
       // if(isset($search) && $search != "")
        $searchRegExp = self::accentToRegex($search);
        $query = array( "name" => new MongoRegex("/.*{$searchRegExp}.*/i"));
        //implode $search
        $explodeSearchRegExp = explode(" ", $searchRegExp);
        if(count($explodeSearchRegExp)>1){
	        $andArray=array();
	        foreach($explodeSearchRegExp as $data){
		        array_push($andArray,array("name" => new MongoRegex("/.*{$data}.*/i")));
	        }
	        $query = array('$or' => array($query,array('$and'=> $andArray)));
        }

        
        /***********************************  TAGS   *****************************************/
        $tmpTags = array();
        if(strpos($search, "#") > -1){
        	$searchTagText = substr($search, 1, strlen($search)); 
        	$query = array( "tags" => array('$in' => array(new MongoRegex("/^".$searchTagText."$/i")))) ; 
        	$tmpTags[] = new MongoRegex("/^".$searchTagText."$/i");
  		}
  		if(!empty($searchTag))
  			foreach ($searchTag as $value) { 
  				if($value != "")
	  				$tmpTags[] = new MongoRegex("/^".$value."$/i");
	  		}
  		if(count($tmpTags)){
  			$query = array('$and' => array( $query , array("tags" => array('$in' => $tmpTags)))) ;
  		}
  		//unset($tmpTags);
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

        	if(sizeof($searchType)==1 && @$searchTypeOrga != "")
        		array_push( $queryOrganization[ '$and' ], array( "type" => $searchTypeOrga ) );

        	//var_dump($queryOrganization); exit;
	  		$allOrganizations = PHDB::findAndSortAndLimitAndIndex ( Organization::COLLECTION ,$queryOrganization, 
	  												array("updated" => -1), $indexStep, $indexMin);
	  		foreach ($allOrganizations as $key => $value) 
	  		{
	  			if(!empty($value)){
		  			$orga = Organization::getSimpleOrganizationById($key,$value);
		  			if( @$value["links"]["followers"][Yii::app()->session["userId"]] )
			  			$orga["isFollowed"] = true;

			  		if(@$orga["type"] != "")
						$orga["typeOrga"] = $orga["type"];
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
					$allEvents[$key]["startDate"] = date(DateTime::ISO8601, $allEvents[$key]["startDate"]->sec);
				if(@$allEvents[$key]["endDate"])
					$allEvents[$key]["endDate"] = date(DateTime::ISO8601, $allEvents[$key]["endDate"]->sec);
	  		}

	  		$allRes = array_merge($allRes, $allEvents);

	  	}
	  	//error_log("recherche - indexMin : ".$indexMin." - "." indexMax : ".$indexMax);
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
	  		//error_log(sizeof($allProject));
	  	}
	/***********************************  POI   *****************************************/
        if(strcmp($filter, Classified::COLLECTION) != 0 && $this->typeWanted(Classified::COLLECTION, $searchType)){
        	$allPoi = PHDB::findAndSortAndLimitAndIndex(Classified::COLLECTION, $query, 
	  												array("updated" => -1), $indexStep, $indexMin);
	  		foreach ($allPoi as $key => $value) {
		  		if(@$value["parentId"] && @$value["parentType"])
		  			$parent = Element::getElementSimpleById(@$value["parentId"], @$value["parentType"]);
		  		else
		  			$parent=array();
				$allPoi[$key]["parent"] = $parent;
				//$allPoi[$key]["type"] = "poi";
				if(@$value["type"])
					$allPoi[$key]["typeSig"] = Classified::COLLECTION.".".$value["type"];
				else
					$allPoi[$key]["typeSig"] = Classified::COLLECTION;
	  		}
	  		//$res["project"] = $allProject;
	  		$allRes = array_merge($allRes, $allPoi);
	  		//error_log(sizeof($allPoi));
	  	}

	  	/***********************************  POI   *****************************************/
        if(strcmp($filter, Poi::COLLECTION) != 0 && $this->typeWanted(Poi::COLLECTION, $searchType)){
        	$allPoi = PHDB::findAndSortAndLimitAndIndex(Poi::COLLECTION, $query, 
	  												array("updated" => -1), $indexStep, $indexMin);
	  		foreach ($allPoi as $key => $value) {
		  		if(@$value["parentId"] && @$value["parentType"])
		  			$parent = Element::getElementSimpleById(@$value["parentId"], @$value["parentType"]);
		  		else
		  			$parent=array();
				$allPoi[$key]["parent"] = $parent;
				//$allPoi[$key]["type"] = "poi";
				if(@$value["type"])
					$allPoi[$key]["typeSig"] = Poi::COLLECTION.".".$value["type"];
				else
					$allPoi[$key]["typeSig"] = Poi::COLLECTION;
	  		}
	  		//$res["project"] = $allProject;
	  		$allRes = array_merge($allRes, $allPoi);
	  		//error_log(sizeof($allPoi));
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
	  		//$res["project"] = $allProject;
	  		$allRes = array_merge($allRes, $allFound);
	  	}
		
		

		/***********************************  VOTES / propositions   *****************************************/
        if(isset(Yii::app()->session["userId"]) && 
        	(strcmp($filter, ActionRoom::TYPE_VOTE) != 0 && $this->typeWanted(ActionRoom::TYPE_VOTE, $searchType)) ||
        	(strcmp($filter, ActionRoom::TYPE_ACTIONS) != 0 && $this->typeWanted(ActionRoom::TYPE_ACTIONS, $searchType))
        	 )
        {    
        	$myLinks = Person::getPersonLinksByPersonId( Yii::app()->session["userId"] );
        	
        	//créer un array avec uniquement les id de mes orgas
        	$orgasId = array();
        	foreach ($myLinks["organizations"] as $key => $link) {
        		if($this->checkScopeParent($link) == true)//en vérifiant si l'orga correspond aux scopes demandés
        			$orgasId[] = (string)$link["_id"];
        	}
        	     
        	//créer un array avec uniquement les id de mes projets
        	$projectsId = array();
        	foreach ($myLinks["projects"] as $key => $link) {
        		if($this->checkScopeParent($link) == true)//en vérifiant si le projet correspond aux scopes demandés
        			$projectsId[] = (string)$link["_id"];
        	}
        	
        	$query = array( '$or' => array( array("parentType"=>"organizations", "parentId" => array('$in' => $orgasId) ),
        									array("parentType"=>"projects", "parentId" => array('$in' => $projectsId) )
        							 )
        				  );

        	//rajoute les résultats pour mon conseil citoyen
        	$me = Person::getSimpleUserById(Yii::app()->session["userId"]);
        	$myCityKey = @$me["address"]["addressCountry"] ? $me["address"]["addressCountry"] : false;
        	if($myCityKey!=false){
        		$myCityKey .= @$me["address"]["codeInsee"] ? "_".$me["address"]["codeInsee"] : false;
        		if($myCityKey!=false){
        			$myCityKey .= @$me["address"]["postalCode"] ? "-".$me["address"]["postalCode"] : "";
	        		$query['$or'][] = array("parentType"=>"cities", "parentId" => $myCityKey);
	        	}
        	}
        	

        	$allRooms = PHDB::find( ActionRoom::COLLECTION, $query);
        	
        	//crée une array avec uniquement les id des rooms
        	$allRoomsId = array();
        	foreach ($allRooms as $key => $room) {
        		$allRoomsId[] = (string)$room["_id"];
        	}

        	if($this->typeWanted( ActionRoom::TYPE_VOTE, $searchType)){
				$collection = Survey::COLLECTION;
				$parentRow = "survey";
			}
        	if($this->typeWanted( ActionRoom::TYPE_ACTIONS, $searchType)){ 
        		$collection = Action::NODE_ACTIONS;
        		$parentRow = "room";
        	}

        	$query = array($parentRow => array('$in' => $allRoomsId) );
        	
        	if(count($tmpTags))
        	$query = array('$and' => array( $query , array("tags" => array('$in' => $tmpTags)))) ;
        	
        	//echo "search : ". $search." - ".(string)strpos($search, "#");
        	if($search != "" && strpos($search, "#") === false){
	        	$searchRegExp = self::accentToRegex($search);
	        	$queryFullTxt = array( '$or' => array( array("name" => new MongoRegex("/.*{$searchRegExp}.*/i")),
	        						   				   array("message" => new MongoRegex("/.*{$searchRegExp}.*/i")))
	        						);
	        	if(isset($query['$and'])) $query['$and'][] = $queryFullTxt;
	        	else $query = array('$and' => array( $query , $queryFullTxt)) ;
	        }

	        /* //requete pour masquer les actions / votes fermés = date dépassé
	           //ne marche pas, mais pas util, puisque les resultats sont affichés par date updated, 
	           //les vieux resultats seront automatiquement en derniers
	           
	        if(isset($query['$and'])) 
	        	$query['$and'][] =  array( '$or' => array( array( "dateEnd" => array( '$gte' => new MongoDate( time() ) ) )),
	        						   				   	   array('dateEnd' => array('$exists'=>false))
	        						);
	        	//array( "dateEnd" => array( '$gte' => new MongoDate( time() ) ) );
	        else $query = array('$and' => array( $query , array( '$or' => array( array( "dateEnd" => array( '$gte' => new MongoDate( time() ) ) )),
	        						   				   	   array('dateEnd' => array('$exists'=>false))
	        						) ) ) ;
			*/

	        //var_dump($query); exit;
        	//error_log("collection : ".$collection);
        	//récupère toutes les propositions ou actions qui correspondent aux rooms trouvées précédement
        	$allFound = PHDB::findAndSortAndLimitAndIndex($collection, $query, array("updated" => -1), $indexStep, $indexMin);

        	foreach ($allRooms as $keyR => $room) {
        		//pour chaque room des orga, on ajoute quelques info sur le parentObj
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

    				if($this->checkScopeParent($cityCheck) == true){
	    				$allRooms[$keyR]["parentObj"]["name"] = $cityCheck["name"];
	    				$allRooms[$keyR]["parentObj"]["address"] = @$cityCheck["address"];
	    				$allRooms[$keyR]["parentObj"]["address"]["addressLocality"] = @$cityCheck["name"];
	    				$allRooms[$keyR]["parentObj"]["address"]["addressCountry"] = @$cityCheck["address"]["countryCode"];
	    				$allRooms[$keyR]["parentObj"]["typeSig"] = $cityCheck["typeSig"];
	    				$allRooms[$keyR]["geo"] = @$cityCheck["geo"];
	    			}else{
	    				//array_splice($allRooms, $keyR, 1);
	    				//unset($allRooms[$keyR]);
	    				$allRooms[$keyR] = array();
	    			}
        		}

        		//var_dump($allRooms[$keyR]);exit;

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
        	
        	$allRes = array_merge($allRes, $allFound);
        }


	  	/***********************************  CITIES   *****************************************/
        if(strcmp($filter, City::COLLECTION) != 0 && $this->typeWanted(City::COLLECTION, $searchType)){
	  		$query = array( "name" => new MongoRegex("/".self::wd_remove_accents($search)."/i"));//array('$text' => array('$search' => $search));//

	  		
	  		/***********************************  DEFINE LOCALITY QUERY   *****************************************/
	        	if($locality == null || $locality == "")
		    		$locality = $search;
		    	$type = $this->getTypeOfLocalisation($locality);
		    	//if($searchBy == "INSEE") $type = $searchBy;
	        	//error_log("type " . $type);
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

	  	if(@$_POST['tpl'] == "/pod/nowList"){
	  		usort($allRes, "mySortByUpdated");
	  	}
	  	/*
	  	$limitRes = array();
	  	$index = 0;
	  	foreach ($allRes as $key => $value) {
	  		if($index < $indexMax && $index >= $indexMin){ $limitRes[] = $value;
		  	}//else{ break; }
		  	$index++;
	  	}
		*/

		foreach ($allRes as $key => $value) {
			if(@$value["updated"]) $allRes[$key]["updatedLbl"] = Translate::pastTime($value["updated"],"timestamp");
	  	}

	  	$limitRes = $allRes;
	  	
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
		if($searchType == null || $searchType[0] == "all") return true;
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

	private function checkScopeParent($parentObj){ //error_log("checkScopeParent");
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
	  		if($countScope==0){ error_log("return true EMPTY"); return true; }
	  		
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

		private function checkTagsParent($parentObj, $tags){ //return true;
			
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
}