<?php

class City {

	const COLLECTION = "cities";
	const CONTROLLER = "city";
	const COLLECTION_DATA = "cityData";

	const COLOR = "#E6304C";
	const REGION = "region";
	const DEPARTEMENT = "departement";
	const CITY = "city";
	const NEIGHBOUR_HOOD = "neighbourhood";
	const CITOYENS = "citoyens";
	const COLLECTION_IMPORTHISTORY = "importHistory";
	const ICON = "fa-university";
	const ZONES = "zones";

	public static $dataBinding = array(
	    "name" => array("name" => "name", "rules" => array("required")),
	    "alternateName" => array("name" => "alternateName", "rules" => array("required")),
	    "insee" => array("name" => "insee", "rules" => array("required")),
	    "country" => array("name" => "birthDate", "rules" => array("required")),
	    "geo" => array("name" => "geo", "rules" => array("required","geoValid")),
	    "geoPosition" => array("name" => "geoPosition", "rules" => array("required","geoPositionValid")),
	    "geoShape" => array("name" => "geoShape"/*, "rules" => array("geoShapeValid")*/),
	 	"postalCodes" => array("name" => "postalCodes"/*, "rules" => array("postalCodesValid")*/),
	    "regionName" => array("name" => "regionName"),
	    "region" => array("name" => "region"),
	    "depName" => array("name" => "depName"),
	    "dep" => array("name" => "dep"),
	    "osmID" => array("name" => "osmID"),
	    "wikidataID" => array("name" => "wikidataID"),
	    "modified" => array("name" => "modified"),
	    "updated" => array("name" => "updated"),
	    "creator" => array("name" => "creator"),
	    "created" => array("name" => "created"),
	    "new" => array("name" => "new")
	);


	public static function insert($city, $userid){
		
		unset($city["save"]);
		//var_dump($city);
		$city["modified"] = new MongoDate(time());
		$city["updated"] = time();
        $city["creator"] = $userid;
        $city["created"] = time();
        $city["new"] = true;
        $city["geoPosition"]["coordinates"][0] = floatval($city["geoPosition"]["coordinates"][0]);
    	$city["geoPosition"]["coordinates"][1] = floatval($city["geoPosition"]["coordinates"][1]);

    	$city["geoShape"] = self::getGeoShape($city["name"], $city["country"], $city["osmID"]);
    	if($city["geoShape"] == null)
    		unset($city["geoShape"]);
    	$postalCodes = array();

    	if(!empty($city["postalCodes"])){
    		foreach ($city["postalCodes"] as $keyCP => $cp) {
    			$newCP = array();
	    		$newCP["postalCode"] = $cp["postalCode"];
	    		$newCP["name"] = $cp["name"];
	    		$newCP["geo"] = $cp["geo"];
	    		$newCP["geoPosition"] = $cp["geoPosition"];
	    		$newCP["geoPosition"]["coordinates"][0] = floatval($cp["geoPosition"]["coordinates"][0]);
	    		$newCP["geoPosition"]["coordinates"][1] = floatval($cp["geoPosition"]["coordinates"][1]);
	    		$postalCodes[] = $newCP;
	    	}
    	}
    	
    	$city["postalCodes"] = $postalCodes;
    	
	    try {
    		$valid = DataValidator::validate( ucfirst(self::CONTROLLER), json_decode (json_encode ($city), true) );
    	} catch (CTKException $e) {
    		$valid = array("result"=>false, "msg" => $e->getMessage());
    	}
    	//check insee
    	if( $valid["result"]){
    		$exist = PHDB::findOne(self::COLLECTION, array("insee" => $city["insee"]));
    		//var_dump(json_encode($city));
    		if(empty($exist)){
    			PHDB::insert(self::COLLECTION, $city );
				$res = array("result"=>true,
	                         "msg"=>"La commune a été enregistrer.",
	                         "city"=>json_encode($city),
	                         "id"=>(string)$city["_id"]); 
    		}else{
    			$res = array("result"=>false,
	                         "msg"=>"La commune existe déjà",
	                         "city"=>json_encode($city)); 
    		}

			 
		}else 
        	$res = array( "result" => false, 
                      "msg" => Yii::t("common","Something went really bad : ".$valid['msg']),
                      "city"=>json_encode($city) );
	    return $res;
	}

	public static function getGeoShape($name, $country, $osmID){
		$resNominatim = json_decode(SIG::getGeoByAddressNominatim(null, null, $name, $country, true, true),true);
		foreach ($resNominatim as $key => $value) {
			if($osmID == $value["osm_id"]){
				return $value["geojson"];
			}
		}
		return null;
	}
	
	public static function getInseeWikidataIDByCountry ($country) { 
        $wiki = array(
            "FR"    => "P374",
            "CH"    => "P771",
            "ES"    => "P772",
            "BR​" 	=> "P1585",
            "MX"    => null,
        );  
        return  ( isset($wiki[$country]) ? $wiki[$country] : false );
    }

    public static function countryNotSplitCP ($country) { 
        $wiki = array("BR​");  
        return  ( isset($wiki[$country]) ? true : false );
     }

	/* Retourne des infos sur la commune dans la collection cities" */
	public static function getWhere($params, $fields=null, $limit=20) 
	{
	  	$city =PHDB::findAndSort( self::COLLECTION,$params, array("created" =>1), $limit, $fields);
	  	return $city;
	}

	/* Retourne des infos sur la commune dans la collection cityData" */
	public static function getWhereData($params, $fields=null, $limit=20, $sort=null) 
	{
		ini_set('memory_limit', '-1');
		if(isset($sort)){
			//var_dump($sort);
			$cityData =PHDB::findAndSort( self::COLLECTION_DATA,$params, $sort, $limit, $fields);
		}else{
			$cityData =PHDB::findAndSort( self::COLLECTION_DATA,$params, array("created" =>1), $limit, $fields);
		}
	  	
	  	return $cityData;
	}

	/* Retourne l'id d'une commune par rapport a son code insee */
	public static function getIdByInsee($insee){
		$id = null;
		$where = array("insee" => $insee);
		$cities = self::getWhere($where);
		foreach ($cities as $key => $value) {
			$id = $value["_id"];
		}
		return $id;
	}

	public static function getUnikey($city){
		//var_dump($city);
		if(@$city["cp"])
			return $city["country"]."_".$city["insee"]."-".$city["cp"];
		else if(@$city["postalCode"])
			return $city["country"]."_".$city["insee"]."-".$city["postalCode"];
		else if(@$city["postalCodes"])
			return $city["country"]."_".$city["insee"]."-".$city["postalCodes"][0]["postalCode"];

		return false;
	}

	public static function getUnikeyMap($key){
		$keyStr = str_replace("-", "_", $key);
		$keyT = explode("_", $keyStr);
		$res = array("country" => $keyT[0] , "insee" => $keyT[1] );
		if( strpos($key, "-") )
			$res["cp"] = $keyT[2];
		return $res;
	}

	/* format unikey : COUNTRY_insee-cp */
	public static function getByUnikey($unikey){
		$country = substr($unikey, 0, strpos($unikey, "_"));
		//No cp for the unikey
		if (! strpos($unikey, "-")) {
			$insee = substr($unikey,  strpos($unikey, "_")+1);
		} else {
			$insee = substr($unikey,  strpos($unikey, "_")+1,  strpos($unikey, "-")-strpos($unikey, "_")-1);
			$cp = substr($unikey, strpos($unikey, "-")+1,  strlen($unikey));
		}
		//error_log("INSEE : ".$insee);
		$city = PHDB::findOne( self::COLLECTION , array("insee"=>$insee, "country"=>$country) );// self::getWhere(array("insee"=>$insee, "country"=>$country));
		if (isset($cp)) 
		{
			if(isset($city["postalCodes"]))
			{
				foreach ($city["postalCodes"] as $key => $value) 
				{
					if($value["postalCode"] == $cp)
					{
						$city["name"] = $value["name"];
						$city["cp"] = $value["postalCode"];
						$city["geo"] = $value["geo"];
						$city["geoPosition"] = $value["geoPosition"];
						return $city;
					}
				}
			}
		//If look for a city with only the insee code and without the cp
		} else if (!empty($city)) {
			return $city;
		}
		return null;
		//return array("country" => $country, "insee" => $insee, "cp" => $cp);
	}

	/* Retourne le code de la region d'une commune par rapport a son code insee */
	public static function getCodeRegion($insee){
		$where = array("insee" => $insee);
		$fields = array("region");
		$region = PHDB::findOne( self::COLLECTION, $where ,$fields);
		return $region;
	}

	public static function getDepAndRegionByInsee($insee){
		$where = array("insee" => $insee);
		$fields = array("depName", "regionName", "country");
		$city = PHDB::findOne(self::COLLECTION, $where ,$fields);
		return $city;
	}

	/* Retourne le code du departement d'une commune par rapport a son code insee */
	public static function getCodeDepartement($insee){
		$where = array("insee" => $insee);
		$fields = array("dep");
		$dep = PHDB::findOne( self::COLLECTION, $where ,$fields);
		return $dep;
	}

	/* Retourne le code de la region d'une commune par rapport a son code insee */
	public static function getRegionCitiesByInsee($insee, $fields=null){
		$region = self::getCodeRegion($insee);
		$where = array("region" => $region["region"]);
		$cities = self::getWhere($where, $fields, 0);
		return $cities;
	}

	public static function getDepartementCitiesByInsee($insee, $fields=null){
		$region = self::getCodeRegion($insee);
		$dep = self::getCodeDepartement($insee);
		$where = array("region" => $region["region"], "dep" => $dep["dep"]);
		$cities = self::getWhere($where, $fields , 0);
		return $cities;
	}




	public static function getDepartementByInsee($insee, $fields, $typeData, $option=null, $inseeCities=null){
		$mapDataDep = array();
		$cities = self::getDepartementCitiesByInsee($insee);
		if($inseeCities==null)
		{
			$tabInsee = array();
			foreach ($cities as $key => $value) {
				array_push($tabInsee, $value["insee"]);
			}

			$where = array("insee"=>array('$in' =>$tabInsee) , $typeData => array( '$exists' => 1 ));
		}
		else
			$where = array("insee"=>array('$in' =>$inseeCities) , $typeData => array( '$exists' => 1 ));

		/*$fields = array("insee", $typeData.$option) ;
		$sort = array($typeData.$option => -1);*/

		$fields[] = "insee" ;
		if(!empty($option))
		{
			foreach ($option as $key => $value) {
				$fields[] = $typeData.$value;
				$sort[] = array($typeData.$value => -1);
			}
		}else{
			$fields[] = $typeData;
			$sort = array($typeData => -1);
		}
        

		$cityData = City::getWhereData($where, $fields, 30, $sort);
		foreach ($cityData as $key => $value) {
			foreach ($cities as $k => $v) {
				if(strcmp($v["insee"], $value["insee"])==0){
					$mapDataDep[$v["name"]] = array($value["insee"] => array($typeData => $value[$typeData] ));
				}
			}
		}
		return $mapDataDep;
	}


	public static function getRegionByInsee($insee, $fields, $typeData, $option=null, $inseeCities=null){
		$mapDataRegion = array();
		$cities = self::getRegionCitiesByInsee($insee);

		if($inseeCities==null)
		{
			$tabInsee = array();
			foreach ($cities as $key => $value) {
				array_push($tabInsee, $value["insee"]);
			}

			$where = array("insee"=>array('$in' =>$tabInsee), $typeData => array( '$exists' => 1 ));
		}
		else
			$where = array("insee"=>array('$in' =>$inseeCities), $typeData => array( '$exists' => 1 ));

		/*$fields = array("insee", $typeData.$option) ;
		$sort = array($typeData.$option => -1);*/
        $fields[] = "insee" ;
		if(!empty($option))
		{
			foreach ($option as $key => $value) {
				$fields[] = $typeData.$value;
				$sort[] = array($typeData.$value => -1);
			}
		}else{
			$fields[] = $typeData;
			$sort = array($typeData => -1);
		}
		$cityData = City::getWhereData($where, $fields, 30, $sort);
		foreach ($cityData as $key => $value) {
			foreach ($cities as $k => $v) {
				if(strcmp($v["insee"], $value["insee"])==0){
					$mapDataRegion[$v["name"]] = array($value["insee"] => array($typeData => $value[$typeData] ));
				}
			}
		}

		return $mapDataRegion;
	}


	public static function getDataByListInsee($listInsee, $type){
		$mapData = array();
		
		$where = array("insee"=>array('$in' =>$listInsee) , $type => array( '$exists' => 1 ));
        $fields = array();
        $sort = array($type.".2011.total" => -1);

        $cityData = City::getWhereData($where, $fields, 30, $sort);
		foreach ($cityData as $key => $value) 
		{

			$whereCity = array("insee" => $value["insee"]);
			$fieldsCity  = array("name");
			$city = City::getWhere($whereCity , $fieldsCity );
			foreach ($city as $keyCity => $valueCity) {
				$name = $valueCity["name"];
			}

			$mapData[$name] = array($value["insee"] => array($type => $value[$type] ));
		}
		
		return $mapData;
	}



	public static function getPopulationTotalInsee($insee,$years){
		$where = array("insee"=>$insee, "population" => array( '$exists' => 1 ));
        $fields = array("population.".$years.".total");
        $cityData = City::getWhereData($where, $fields);

        $totalPop = 1;
        foreach ($cityData as $key => $valueCity) {
				$totalPop = $valueCity['population'][$years]['total']['value'];
			}
		return $totalPop;
	}

	public static function getPopulationHommesInsee($insee,$years){
		$where = array("insee"=>$insee, "population" => array( '$exists' => 1 ));
        $fields = array("population.".$years.".hommes.total");
        $cityData = City::getWhereData($where, $fields);

        $totalPop = 1;
        foreach ($cityData as $key => $valueCity) {
				$totalPop = $valueCity['population'][$years]['hommes']['total']['value'];
			}
		return $totalPop;
	}

	public static function getPopulationFemmesInsee($insee,$years){
		$where = array("insee"=>$insee, "population" => array( '$exists' => 1 ));
        $fields = array("population.".$years.".femmes.total");
        $cityData = City::getWhereData($where, $fields);

        $totalPop = 1;
        foreach ($cityData as $key => $valueCity) {
				$totalPop = $valueCity['population'][$years]['femmes']['total']['value'];
			}
		return $totalPop;
	}


	public static function getPopulationTotalInseeDepartement($insee,$years){
		$fields = array("insee");
		$cities = City::getDepartementCitiesByInsee($insee, $fields);

		$count = 1;
		foreach ($cities as $idCities => $value) {

			$data = City::getPopulationTotalInsee($value['insee'],$years);
			$count = $count + $data;
			
			

		}

		return $count;
	}

	public static function getSimpleCityById($id) {

		$simpleCity = array();
		$city = PHDB::findOneById( self::COLLECTION ,$id, array("id" => 1, "name" => 1, "insee" => 1, "cp" => 1, "geo" => 1) );

		$simpleCity["id"] = $id;
		$simpleCity["name"] = @$city["name"];
		$simpleCity["insee"] = @$city["insee"];
		$simpleCity["cp"] = @$city["cp"];
		$simpleCity["country"] = @$city["country"];
		$simpleCity["geo"] = @$city["geo"];
		$simpleCity["type"] = "city";
		
		return $simpleCity;
	}

	//rajoute les attributs "geoPosition" sur chaque City
	//en recopiant les valeurs de l'attribut "geo.longitude" et "geo.latitude"
	public static function updateGeoPositions(){
		//récupère les villes qui on un attribut geo mais qui n'ont pas de geoPosition
		$request=array( "geo" => array( '$exists' => true ),
						"geoPosition.float" => array( '$exists' => false ),
						//"geoPosition" => array( '$exists' => false ),
					  );

		//$maxCities = 300;
		$nbCities=0;
		$cnt=0;
		
		$allCities = PHDB::findAndSort(City::COLLECTION, $request, array("insee"=>1), 1000);
		error_log("------------------------------------------------------------");
		error_log("START update geoPosition Cities : ".count($allCities). " trouvées");
		error_log("------------------------------------------------------------");
		error_log("L'opération peut durer entre 5 et 10 minutes");
		error_log("------------------------------------------------------------");
		
		for($i=0; $i<40 && count($allCities)>0; $i++){
			
			$allCities = PHDB::findAndSort(City::COLLECTION, $request, array("insee"=>1), 1000);
			error_log("iteration n°" . $i . " - update geoPosition Cities : ".count($allCities). " trouvées");
			error_log("###");
			error_log("###");
			
			//si on a trouvé une ville
			if(count($allCities)>0){
				foreach ($allCities as $key => $city) {
					//if($nbCities > $maxCities) return null;
					$nbCities++;
					//on rajoute l'attribut geoPosition (type Point)
					if(isset($city["geo"]["latitude"]) && isset($city["geo"]["longitude"])){
						$lat = $city["geo"]["latitude"];
						$lng = $city["geo"]["longitude"]; 	
						if($cnt<=0){
							error_log("update ".$city["insee"]. " to geoPosition lat:".$lat." lng:".$lng." num : ".$nbCities);		
							$cnt=50;
						}
						$cnt--;
						PHDB::update( City::COLLECTION, 
									  array("insee" => $city["insee"],
									  		"name" => $city["name"], 
									  		"cp" => $city["cp"]), 
				                          array('$set' => 
				                          		array("geoPosition" => 
					                          		array("type"=>"Point",
					                          			  "float"=>"true",
					                          			  "coordinates" => array(floatval($lng), floatval($lat))
					                          			  )
				                          			)
				                          		)
				                    );
					}
				}
			}
		}
		error_log("La mise à jour est terminée. ".$nbCities." communes ont été traitées");
		return true;
	}


	/* Retourne le code du departement d'une commune par rapport a son code insee */
	public static function getAlternateNameByInseeAndCP($insee, $cp){
		$where = array("insee" => $insee,
						"cp" => $cp);
		$fields = array("alternateName");
		$dep = PHDB::findOne( self::COLLECTION, $where ,$fields);
		return $dep;
	}

	public static function getCitiesForcheck(){
		$where = array();
		$count = PHDB::count(self::COLLECTION, $where);

		$cities = array();
		$limit = 50;
		
		$zero = 0;
		if($count%$limit > 0) {
			$zero = 1 ;
			$res = round($count/$limit , 0);
		}	
		
		$result =  $zero + $res;

		$i = 0 ;
		$index=0;
		
		while($i < $result){
			set_time_limit(30) ;
			$rescities = PHDB::findAndLimitAndIndex(self::COLLECTION, $where, $limit, $index);
			$cities = array_merge($cities, $rescities);
			//$cities = $rescities;
			$index += $limit;
			$i++;

		}
		/*var_dump($count);
		var_dump($cities);*/

		return $cities;
	}

	
	public static function getCityByInseeCp($insee, $cp = null){
		$where = array("insee" => $insee);

		if(!empty($cp))
			$where["postalCodes.postalCode"] = $cp;

		//$fields = array("_id");
		$city = PHDB::findOne( City::COLLECTION, $where);// ,$fields);
	
		if(isset($city["postalCodes"]))
		foreach ($city["postalCodes"] as $key => $value) {
			if(!empty($cp) && $value["postalCode"] == $cp){
				$city["namePc"] = $value["name"];
				$city["cp"] = $value["postalCode"];
				$city["geo"] = $value["geo"];
				$city["geoPosition"] = $value["geoPosition"];
				return $city;
			}else{
				$city["namePc"][] = $value["name"];
				$city["cpArray"][] = $value["postalCode"];

				if($value["name"] == $city["name"])
					$city["cp"] = $value["postalCode"];
			}
		}

		if(empty($city["cp"]))
			$city["cp"] = $city["cpArray"][0];
		
		return $city;
	}

	public static function getZone($insee, $cp = null, $zone = null){
		
		$city = PHDB::findOne( City::COLLECTION, array("insee" => $insee));// ,$fields);
	
		$zone = PHDB::findOne( City::ZONES, array("_id" => $insee));
		
		
		return $city;
	}

	public static function getCityByInsee($insee){
		$where = array("insee" => $insee);
		//$fields = array("_id");
		$city = PHDB::findOne( City::COLLECTION, $where);// ,$fields);
		return $city;
	}

	public static function getInternationalCity($cp, $insee, $country=null, $name=null){
		$where = array();//"postalCodes.postalCode" => $cp);

		if($cp != null)
			$where["postalCodes.postalCode"] = $cp;
		if($insee != null)
			$where["insee"] = $insee;
		if($country != null)
			$where["country"] = $country;
		if($name != null){
			$where["postalCodes.name"] = new MongoRegex("/^".(City::wd_remove_accents($name))."/i");
			error_log(new MongoRegex("/^".(City::wd_remove_accents($name))."/i"));
		}
		//$fields = array("_id");
		$city = PHDB::findOne( City::COLLECTION, $where);// ,$fields);
	
		if(isset($city["postalCodes"]))
		foreach ($city["postalCodes"] as $key => $value) {
			if($value["postalCode"] == $cp){
				$city["name"] = $value["name"];
				$city["cp"] = $value["postalCode"];
				$city["geo"] = $value["geo"];
				$city["geoPosition"] = $value["geoPosition"];
				error_log("found : ".$city["name"]);
				return $city;
			}
		}
		return $city;
	}


    //supprime les accents (utilisé pour la recherche de ville pour améliorer les résultats)
    public static function wd_remove_accents($str, $charset='utf-8')
	{
		// return $str;
	    $str = htmlentities($str, ENT_NOQUOTES, $charset);
	    
	    $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
	    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
	    $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
	    
	    return $str;
	}

	public static function getCitiesWithWikiData($wikidataID, $newCities)
	{
		
		$newCities["wikidataID"] = $wikidataID;
		$wikidata = json_decode(SIG::getWikidata($wikidataID),true);
		$valWiki = @$wikidata["entities"][$wikidataID]["claims"];
		$arrayAdd = array();
		$arrayCp = array();
		$wikiInsee = City::getInseeWikidataIDByCountry($newCities["country"]);
		if(!empty($wikiInsee))
	    	$newCities["insee"] = $valWiki[$wikiInsee][0]["mainsnak"]["datavalue"]["value"]."*".$newCities["country"];

	    $postalCodes = array() ;
	    if(!empty($valWiki)){
	       
	        if(!empty($valWiki["P281"])){
	        	
	        	foreach ($valWiki["P281"] as $key => $cp) {
		            if( (strpos($cp["mainsnak"]["datavalue"]["value"],"–") || strpos($cp["mainsnak"]["datavalue"]["value"],"-")) 
		            	&& self::countryNotSplitCP($newCities["country"])) {
		                if(strpos($cp["mainsnak"]["datavalue"]["value"],"–"))
		                    $split = explode("–", $cp["mainsnak"]["datavalue"]["value"]);
		                else
		                    $split = explode("-", $cp["mainsnak"]["datavalue"]["value"]);
		                
		                if(count($split) == 2){
		                    $start = intval($split[0]);
		                    if(!empty($start)){
		                        $end = intval($split[1]);
		                        while($start <= $end ){
		                            $arrayCp[] = trim(strval($start));
		                            $start++;
		                        }
		                    }
		                }
		            }else{
		                $arrayCp[] = $cp["mainsnak"]["datavalue"]["value"];
		            }

		            foreach ($arrayCp as $keyCP => $valueCP) {
		                //var_dump($valueCP);
		                if(!in_array($valueCP, $arrayAdd)){
		                    $arrayAdd[] =  $valueCP;
		                    $postalCodes[]  = array("name" => $newCities["name"],
		                                    "postalCode" => $valueCP,
		                                    "geo" => array( "@type"=>"GeoCoordinates", 
		                                                    "latitude" => $newCities["geo"]["latitude"], 
		                                                    "longitude" => $newCities["geo"]["longitude"]),
		                                    "geoPosition" => array( "type"=>"Point",
		                                    						"float" => true,
		                                                            "coordinates" => array(
		                                                                floatval($newCities["geo"]["longitude"]), 
		                                                                floatval($newCities["geo"]["latitude"]))));  
		                }
		            }
		            
		        }
	        }
	        

		}
		$newCities["postalCodes"] = $postalCodes;
		return $newCities;
	}



	public static function checkCitySimply($city){
		$res = false;
		if(!empty($city["name"]) && !empty($city["alternateName"] )&& !empty($city["country"]) && !empty($city["insee"])
			&& !empty($city["geo"]) && !empty($city["geoPosition"] ))
			$res = true;
		
		return $res;
	}

	public static function getAllCities(){
		$cities = City::getWhere(array());
		//ini_set('memory_limit', '-1');
		//$cities =  PHDB::find( self::COLLECTION,array());
		$res = array(	"goods" => array(),
						"errors" => array(),
						"news" => array());

		foreach ($cities as $key => $city) {
			
			$msg = self::checkCity($city);
			if(!empty($city["new"]) && $city["new"]==true){
				if($msg != "")
					$city["msgErrors"] = $msg;
				$res["news"][(String)$city["_id"]] = $city;
			}
			else if($msg != ""){
				$city["msgErrors"] = $msg;
				$res["errors"][(String)$city["_id"]] = $city;
			}else
				$res["goods"][(String)$city["_id"]] = $city;
		}
		return $res ;
	}

	public static function checkCity($city){
		$msgErrors = "" ;

		if(empty($city["name"]))
			$msgErrors = "The name is missing.<br\>" ;
		if(empty($city["alternateName"]))
			$msgErrors = "The alternateName is missing.<br\>" ;
		if(empty($city["insee"]))
			$msgErrors = "The insee is missing.<br\>" ;
		if(empty($city["country"]))
			$msgErrors = "The country is missing.<br\>" ;
		if(empty($city["dep"]))
			$msgErrors = "The dep is missing.<br\>" ;
		if(empty($city["depName"]))
			$msgErrors = "The depName is missing.<br\>" ;
		if(empty($city["region"]))
			$msgErrors = "The region is missing.<br\>" ;
		if(empty($city["regionName"]))
			$msgErrors = "The regionName is missing.<br\>" ;

		if(empty($city["geo"]))
			$msgErrors = "The geo is missing.<br\>" ;
		if(empty($city["geoPosition"]))
			$msgErrors = "The geoPosition is missing.<br\>" ;

		if(empty($city["geoShape"]))
			$msgErrors = "The geoShape is missing.<br\>" ;

		if(empty($city["postalCodes"])){
			foreach ($city["postalCodes"] as $keyPC => $postalCode) {
				if(empty($postalCode["postalCode"]))
					$msgErrors = "The postalCode is missing.<br\>" ;
				if(empty($postalCode["name"]))
					$msgErrors = "The name is missing for postal code ".$postalCode["postalCode"].".<br\>" ;
				if(empty($postalCode["geo"]))
					$msgErrors = "The geo is missing for postal code ".$postalCode["postalCode"].".<br\>" ;
				if(empty($postalCode["geoPosition"]))
					$msgErrors = "The geoPosition is missing for postal code ".$postalCode["postalCode"].".<br\>" ;
			}
		}

		return $msgErrors;
	}

	
    

	
	public static function getCityByCedex($cp)
	{
		$cityCedex = PHDB::findOne(City::COLLECTION, array("postalCodes.postalCode" => $cp,
                                                            "postalCodes.complement" => array('$exists' => 1)));
        $res = null ;
        if(!empty($cityCedex)){
            foreach ($cityCedex["postalCodes"] as $key => $value) {
            	if(!empty($value["complement"]) && $value["postalCode"] == $cp){
            		$res["name"] = $value["name"];
            		$res["insee"] = $cityCedex["insee"];
            		$res["cp"] = $value["postalCode"];
            		$res["geo"] = $value["geo"];
            		$res["geoPosition"] = $value["geoPosition"];
            		$res["regionName"] = $cityCedex["regionName"];
            		$res["depName"] = $cityCedex["depName"];
            		$res["country"] = $cityCedex["country"];
            	}
            }
        }
        return $res;
	}

	public static function prepCity ($params) { 

        if(!empty($params["name"]))
        	$params["alternateName"]= mb_strtoupper($params["name"]);
        
        if(!empty($params["latitude"]) && !empty($params["longitude"])){
        	$params["geo"]= SIG::getFormatGeo($params["latitude"], $params["longitude"]);
			$params["geoPosition"]= SIG::getFormatGeoPosition($params["latitude"], $params["longitude"]);
        }

        if(!empty($params["wikidata"]))
        	$params["wikidataID"]= $params["wikidata"];
        
        if(!empty($params["osmid"]))
        	$params["osmID"]= $params["osmid"];
        
 		if(!empty($params["postalCodes"])){
 			$newPostalCodes = array();
        	foreach ($params["postalCodes"] as $keyCP => $valueCP) {
        		$newCP = array();
        		$newCP["postalCode"] = $valueCP["postalCode"];
        		$newCP["name"] = $valueCP["name"];
        		$newCP["geo"] = SIG::getFormatGeo($valueCP["latitude"], $valueCP["longitude"]);
        		$newCP["geoPosition"]= SIG::getFormatGeoPosition($valueCP["latitude"], $valueCP["longitude"]);
        		$newPostalCodes[] = $newCP;
        	}
        	$params["postalCodes"] = $newPostalCodes;
 		}
 		
        unset($params["osmid"]);
        unset($params["wikidata"]);
        unset($params["latitude"]);
        unset($params["longitude"]);

		return $params;
    }
	
	public static function getZones($insee){
		$zones =PHDB::findAndSort(self::ZONES,array("insee" =>$insee), array("name" => 1));
	  	return $zones;
	}

	/*
	public static function createCitizenAssemblies(){
		$params = array("habitants" => array('$gt' => 5000));
		$limit = 1000;
		$sort = array("habitants" => -1);
		$cityData = PHDB::findAndSort( self::COLLECTION, $params, $sort, $limit);
		foreach ($cityData as $key => $value) {
			$cityData[$key]["typeSig"] = "city";
			$cityData[$key]["cp"] = $value["postalCodes"][0]["postalCode"];
			//self::createCitizenAssembly($value["insee"]);
		}
		error_log("createCitizenAssembly ".count($cityData));
		return $cityData;
	}*/


	/*public static function createCitizenAssembly($insee, $cp){
		//$params = array("address.codeInsee" => $insee, "address.postalCode" => $cp);
		$CTZAssembly = self::getCitizenAssemblyByInsee($insee, $cp);//PHDB::findOne( Organization::COLLECTION, $params);

		if($CTZAssembly == null){

			$params = array("insee" => $insee);
			$cityData = PHDB::findOne( self::COLLECTION, $params);
			$cityAddress = array();
			foreach ($cityData["postalCodes"] as $key => $value) {
				if($value["postalCode"] == $cp){
					$cityAddress = $value;
				} 	
			}

			//echo "L'assemblée n'existe pas<br>";
			//echo "Création en cours<br>";

			$time = time();

			$CTZAssembly = array(
					"name" => "Assemblée Citoyenne - ". $cityAddress["name"],
					"created" => $time,
					"type" => "group",
					"citizenType" => "citizenAssembly",
					"address" => array(
								"@type"=>"PostalAddress",
								"codeInsee"=>$insee,
								"addresscountry"=>$cityData["country"],
								"postalCode"=>$cityAddress["postalCode"],
								"addressLocality"=>$cityData["name"],
								"streetAddress"=>"",
								),
					"geo" => $cityAddress["geo"],
					"tags" => array("AssembléeCitoyenne"),
					"description" => "L'assemblée citoyenne est un lieu de discussion et d'échange, où vous pouvez aborder tous les sujets concernant la vie commune des citoyens. Vous pouvez également y soumettre des propositions qui seront soumise au vote citoyen.",
					"links" => array(),
					);

			$newAssembly = PHDB::insert( Organization::COLLECTION, $CTZAssembly);

			// foreach ($CTZAssembly as $key => $value) {
			// 	echo "<br><br>[".$key."] <br>";
			// 	var_dump($value);
			// }
			// echo "<br><br><br>";
			
			//$params = array("name" => "Assemblée Citoyenne - ". $cityAddress["name"], "created" => $time);
			//$cityData = PHDB::findOne( self::COLLECTION, $params);

			$actionRoom = array(
					"name" => "Assemblée Citoyenne - ".$cityAddress["name"],
					"type" => "vote",
					"parentType" => Organization::COLLECTION,
					"parentId"=> (string)$CTZAssembly["_id"],
					"tag" => array(),
					"created" => $time);

			$newActionRoom = PHDB::insert( ActionRoom::COLLECTION, $actionRoom);

			return $CTZAssembly;
			//var_dump($CTZAssembly);
			
		}else{
			return $CTZAssembly;
		}
		//$limit = 1000;
		//$sort = array("habitants" => -1);
		// $cityData = PHDB::findOne( self::COLLECTION, $params);
		// foreach ($cityData["postalCode"] as $key => $value) {
		// 	if($value["postalCode"] == $cp){

		// 	}
		// }
		return $CTZAssembly;
	}*/





}
?>