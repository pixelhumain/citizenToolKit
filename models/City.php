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

	/* Retourne des infos sur la commune dans la collection cities" */
	public static function getWhere($params, $fields=null, $limit=20) 
	{
	  	$city =PHDB::findAndSort( self::COLLECTION,$params, array("created" =>1), $limit, $fields);
	  	return $city;
	}

	/* Retourne des infos sur la commune dans la collection cityData" */
	public static function getWhereData($params, $fields=null, $limit=20, $sort=null) 
	{
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
		return $city["country"]."_".$city["insee"]."-".$city["cp"];
	}

	/* format unikey : COUNTRY_insee-cp */
	public static function getByUnikey($unikey){
		$country = substr($unikey, 0, strpos($unikey, "_"));
		$insee = substr($unikey,  strpos($unikey, "_")+1,  strpos($unikey, "-")-strpos($unikey, "_")-1);
		$cp = substr($unikey, strpos($unikey, "-")+1,  strlen($unikey));

		$city = PHDB::findOne( self::COLLECTION , array("insee"=>$insee, "country"=>$country) );// self::getWhere(array("insee"=>$insee, "country"=>$country));
		if(isset($city["postalCodes"]))
		foreach ($city["postalCodes"] as $key => $value) {
			if($value["postalCode"] == $cp){
				$city["name"] = $value["name"];
				$city["cp"] = $value["postalCode"];
				$city["geo"] = $value["geo"];
				$city["geoPosition"] = $value["geoPosition"];
				return $city;
			}
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

	
	public static function getCityByInseeCp($insee, $cp){
		$where = array("insee" => $insee,
					   "postalCodes.postalCode" => $cp);

		//$fields = array("_id");
		$city = PHDB::findOne( City::COLLECTION, $where);// ,$fields);
	
		if(isset($city["postalCodes"]))
		foreach ($city["postalCodes"] as $key => $value) {
			if($value["postalCode"] == $cp){
				$city["namePc"] = $value["name"];
				$city["cp"] = $value["postalCode"];
				$city["geo"] = $value["geo"];
				$city["geoPosition"] = $value["geoPosition"];
				return $city;
			}
		}
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