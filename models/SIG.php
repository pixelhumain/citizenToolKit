<?php
/*
Contains anything generix for the site 
 */
class SIG
{
    //const CITIES_COLLECTION_NAME = "cities";

    public static function clientScripts()
    {
        $cs = Yii::app()->getClientScript();
		$cs->registerCssFile(Yii::app()->theme->baseUrl. '/assets/css/sig.css');
		//$cs->registerCssFile("//cdn.leafletjs.com/leaflet-0.7.3/leaflet.css");
		$cs->registerCssFile(Yii::app()->theme->baseUrl. '/assets/css/leaflet.css');
		$cs->registerCssFile(Yii::app()->theme->baseUrl. '/assets/css/leaflet.draw.css');
		$cs->registerCssFile(Yii::app()->theme->baseUrl. '/assets/css/leaflet.draw.ie.css');
		$cs->registerCssFile(Yii::app()->theme->baseUrl. '/assets/css/MarkerCluster.css');
		$cs->registerCssFile(Yii::app()->theme->baseUrl. '/assets/css/MarkerCluster.Default.css');

		$cs->registerScriptFile('//cdn.leafletjs.com/leaflet-0.7.3/leaflet.js');
		$cs->registerScriptFile(Yii::app()->theme->baseUrl.'/assets/js/leaflet.draw-src.js' , CClientScript::POS_END);
		$cs->registerScriptFile(Yii::app()->theme->baseUrl.'/assets/js/leaflet.draw.js' , CClientScript::POS_END);
		$cs->registerScriptFile(Yii::app()->theme->baseUrl.'/assets/js/leaflet.markercluster-src.js' , CClientScript::POS_END);
		return $cs;
    }
    


    public static function geoCodage($organization){
    	if(!empty($organization['address']['streetAddress']))
		{
			$nominatim = "http://nominatim.openstreetmap.org/search?q=".urlencode($organization['address']['streetAddress'])."&format=json&polygon=0&addressdetails=1";

			$curl = curl_init($nominatim);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$returnCURL = json_decode(curl_exec($curl),true);
			//var_dump($returnCURL);
			if(!empty($returnCURL) || $returnCURL != array())
			{
				foreach ($returnCURL as $key => $valueAdress) {
					$newOrganization['address']['geo']['@type'] = "GeoCoordinates" ;
					$newOrganization['address']['geo']['latitude'] = $valueAdress['lat'];
					$newOrganization['address']['geo']['longitude'] = $valueAdress['lon'] ;
				}

			}	
			curl_close($curl);
		}
    }
	
	//ajoute la position géographique d'une donnée si elle contient un Code Postal
	//add geographical position to a data if it contains Postal Code
	public static function addGeoPositionToEntity($entity){
		if(empty($entity["geo"]) && !empty($entity["address"]["postalCode"])){
			$geoPos = self::getPositionByCp($entity["address"]["postalCode"]);
			if($geoPos != false){
				$entity["geo"] = $geoPos;
			}
			
		} 
		return $entity;
	}

	//ajoute la position géographique d'une donnée si elle contient un Code Postal
	//add geographical position to a data if it contains Postal Code
	public static function updateEntityGeoposition($entityType, $entityId, $latitude, $longitude){
		
		error_log("updateEntity Start");
		$geo = array("@type"=>"GeoCoordinates", "latitude" => $latitude, "longitude" => $longitude);
		$geoPosition = array("type"=>"Point", "coordinates" => array(floatval($longitude), floatval($latitude)));

		//PH::update($entityType,array("geo" => $geo));

		if($entityType == PHType::TYPE_CITOYEN || $entityType == PHType::TYPE_PERSON ){
			var_dump($entityType);
			error_log("update TYPE_CITOYEN");
			Person::updatePersonField($entityId, "geo", $geo, Yii::app()->session['userId'] );
			Person::updatePersonField($entityId, "geoPosition", $geoPosition, Yii::app()->session['userId'] );
		}
		if($entityType == PHType::TYPE_ORGANIZATIONS){
			error_log("update TYPE_ORGANIZATIONS");
			Organization::updateOrganizationField($entityId, "geo", $geo, Yii::app()->session['userId'] );
			Organization::updateOrganizationField($entityId, "geoPosition", $geoPosition, Yii::app()->session['userId'] );
		}
		if($entityType == PHType::TYPE_PROJECTS){
			error_log("update TYPE_PROJECTS");
			Project::updateProjectField($entityId, "geo", $geo, Yii::app()->session['userId'] );
			Project::updateProjectField($entityId, "geoPosition", $geoPosition, Yii::app()->session['userId'] );
		}
		if($entityType == PHType::TYPE_EVENTS){
			error_log("update TYPE_EVENTS");
			Event::updateEventField($entityId, "geo", $geo, Yii::app()->session['userId'] );
			Event::updateEventField($entityId, "geoPosition", $geoPosition, Yii::app()->session['userId'] );
		}

		if(Import::isUncomplete($entityId, $entityType))
			Import::checkWarning($entityId, $entityType, Yii::app()->session['userId']);
		error_log("updateEntity OK");
	}

	//return geographical position of inseeCode
	public static function getGeoPositionByInseeCode($inseeCode, $postalCode = null, $cityName = null){
		$city = self::getCityByCodeInsee($inseeCode);
		foreach ($city["postalCodes"] as $data){
			if($data["postalCode"] == $postalCode){
				if ($cityName == null || $data["name"] == $cityName){
				$geopos = array( 	"@type" => "GeoCoordinates",
									"latitude" => $data["geo"]["latitude"],
									"longitude" => $data["geo"]["longitude"]);
				}
			}
		}						
		return $geopos;
	}

  	//récupère la position géographique depuis les Cities
  	//get geo position from Cities collection in data base
	public static function getPositionByCp($cp){
  		$city = PHDB::findOne ( 'cities', array("cp"=>$cp) );
		if(!empty($city)){
			return array( 	"@type" => "GeoCoordinates",
							"latitude" => $city["geo"]["latitude"],
							"longitude" => $city["geo"]["longitude"]);
		} return false;
		
	}

	//récupère la ville qui correspond à une position géographique
	//https://docs.mongodb.org/manual/reference/operator/query/near/#op._S_near
	public static function getCityByLatLng($lat, $lng, $cp){

		// $request = array("geoShape"  => 
		// 				  array('$geoIntersects'  => 
		// 				  	array('$geometry' => 
		// 				  		array("type" 	    => "Point", 
		// 				  			  "coordinates" => array(floatval($lng), floatval($lat)))
		// 				  		)));
		// if($cp != null){ $request = array_merge(array("cp"  => $cp), $request); }
		
		// $oneCity =	PHDB::findOne(City::COLLECTION, $request);

		$oneCity = null;
		//City::updateGeoPositions();
		//error_log($lng." - ".$lat);
		if($oneCity == null){
			$request = array("geoPosition" => array( '$exists' => true ),
							 "geoPosition.coordinates"  => 
							  array('$near'  => 
								  	array(	'$geometry' => 
								  			array("type" 	    => "Point", 
								  			   	  "coordinates" => array( floatval($lng), 
								  			  						   	  floatval($lat) )
											  			 		),
							  		 		'$maxDistance' => 50000,
							  		 		'$minDistance' => 10
							  			 ),
						  	 		)
					   		);
				
			if($cp != null){ $request = array_merge(array("postalCodes.postalCode" => array('$in' => array($cp))), $request); }

			$oneCity =	PHDB::findAndSort(City::COLLECTION, $request, array());
			//var_dump($oneCity);
		}

		// var_dump($request);	
		// var_dump($oneCity);	
		//var_dump($oneCity);
		return $oneCity;
	}

	//récupère le code insee d'une position geographique
	//(préciser un CP pour un résultat plus rapide)
	public static function getInseeByLatLngCp($lat, $lng, $cp){
		// $oneCity =	self::getCityByLatLng($lat, $lng, $cp);
		// if($oneCity != null && $oneCity["insee"] != null) return $oneCity;//["insee"];
		// else return null;

		$cities =	self::getCityByLatLng($lat, $lng, $cp);
		if($cities != null) return $cities;//["insee"];
		else return null;


	}

	//TODO : FAIRE LA VERIFICATION AVEC LES GEOSHAPES DES COUNTRY
	public static function getCountryByLatLng($lat, $lng, $cp){
		//$oneCity =	self::getCityByLatLng($lat, $lng);
		return null; //$oneCity["country"];
	}


	/**
	 * Get the city by insee code. Can throw Exception if the city is unknown.
	 * @param String $codeInsee the code insee of the city
	 * @return Array With all the field as the cities collection
	 */
	public static function getCityByCodeInsee($codeInsee) {
		if (empty($codeInsee)) {
			throw new InvalidArgumentException("The Insee Code is mandatory");
		}

		$city = PHDB::findOne(City::COLLECTION, array("insee" => $codeInsee));
		if (empty($city)) {
			throw new CTKException("Impossible to find the city with the insee code : ".$codeInsee);
		} else {
			return $city;
		}
	}

	/**
	 * Get the city by insee code. Can throw Exception if the city is unknown.
	 * @param String $codeInsee the code insee of the city
	 * @return Array With all the field as the cities collection
	 */
	public static function getLatLngByInsee($codeInsee,$postalCode=null) {
		if (empty($codeInsee)) {
			throw new InvalidArgumentException("The Insee Code is mandatory");
		}

		$city = PHDB::findOne(City::COLLECTION, array("insee" => $codeInsee));
		if (empty($city)) {
			throw new CTKException("Impossible to find the city with the insee code : ".$codeInsee);
		} else {
			if(@$postalCode && $postalCode != null){
				foreach ($city["postalCodes"] as $data){
					if ($data["postalCode"]==$postalCode){
						$position = isset($data["geo"]) ? array("geo" => $data["geo"]) : "";
						if($position == ""){
							$position = isset($data["geoPosition"]) ? array("geoPosition" => $data["geoPosition"]) : "";	
						}
						if(isset($data["name"]))	{ $position["name"] 	= $data["name"]; }
						break;
					}
				}
			}
			else{
				$position = isset($city["geo"]) ? array("geo" => $city["geo"]) : "";
				if($position == ""){
					$position = isset($city["geoPosition"]) ? array("geoPosition" => $city["geoPosition"]) : "";	
				}
				if(isset($city["name"]))	{ $position["name"] 	= $city["name"]; }
			}
			if(isset($city["geoShape"])){ $position["geoShape"] = $city["geoShape"]; }
			//var_dump($position); die();
			
			return $position;
		}
	}

	/**
	 * Get the city by insee code. Can throw Exception if the city is unknown.
	 * @param String $codeInsee the code insee of the city
	 * @return Array With all the field as the cities collection
	 */
	public static function getCodeInseeByCityName($cityName) {
		if (empty($cityName)) {
			throw new InvalidArgumentException("The City Name is mandatory");
		}
 		//error_log($cityName);
 		//error_log(utf8_encode($cityName));

		$city = PHDB::findOne(City::COLLECTION, array("name" => new MongoRegex("/".PHDB::wd_remove_accents($cityName)."/i")));
		if (empty($city)) {
			throw new CTKException("Impossible to find the city with the City Name : ".$cityName);
		} else {
			return $city;
		}
	}

	/**
	 * Get the city label by insee code. Can throw Exception if the city is unknown.
	 * @param String $codeInsee the code insee of the city
	 * @return Array With all the field as the cities collection
	 */
	public static function getCitiesByPostalCode($postalCode) {
		if (empty($postalCode)) {
			throw new InvalidArgumentException("The postal Code is mandatory");
		}
		$city = PHDB::findAndSort(City::COLLECTION, array("postalCodes.postalCode" => array('$in' => array($postalCode))), array("name" => -1));
		$cities = array();
		foreach($city as $value){
			foreach($value["postalCodes"] as $data){
				if($data["postalCode"] == $postalCode){
					$newCity["insee"] = $value["insee"];
					$newCity["name"] = $data["name"];
					$newCity["postalCode"] = $data["postalCode"];
					$newCity["geo"] = $data["geo"];
					$newCity["geoPosition"] = $data["geoPosition"];
					$cities[]=$newCity;	
				}
		
			}		
		}
		return $cities;
		
	}

	public static function getAdressSchemaLikeByCodeInsee($codeInsee, $postalCode = null, $cityName = null) {
		$city = self::getCityByCodeInsee($codeInsee);
		$address=array();
		$address["@type"] = "PostalAddress";
		$address["codeInsee"] = isset($city['insee']) ? $city['insee'] : "" ;
		$address["addressCountry"] = isset($city['country']) ? $city['country'] : "";
		if($postalCode != null){
			foreach ($city["postalCodes"] as $data){
				if ($data["postalCode"]==$postalCode){
					if ($cityName == null || $data["name"] == $cityName){
					$address["postalCode"] = $data["postalCode"];
					$address["addressLocality"] = $data["name"]; 
					}
					
				}
			}
		}
		return $address;
	}

	public static function getGeoQuery($params, $att){
		return array(	$att  => array( '$exists' => true ),
    					$att.'.latitude' => array('$gt' => floatval($params['latMinScope']), '$lt' => floatval($params['latMaxScope'])),
						$att.'.longitude' => array('$gt' => floatval($params['lngMinScope']), '$lt' => floatval($params['lngMaxScope']))
					  );
	}



	public static function getCityByLatLngGeoShape($lat, $lng, $cp){
		$request = array("geoShape"  => 
		 				  array('$geoIntersects'  => 
		 				  	array('$geometry' => 
		 				  		array("type" 	    => "Point", 
	 				  			  	"coordinates" => array(floatval($lng), floatval($lat)))
		 				  		)));
		if($cp != null){ $request = array_merge(array("postalCodes.postalCode" => array('$in' => array($cp))), $request); }
		
		$oneCity =	PHDB::findOne(City::COLLECTION, $request);

		return $oneCity;
	}



	/**
	 * get all entity(persons, organizations, projects, events) badly geoLocalited
	 * @return Array
	 * @author Raphael RIVIERE
	 */
    public static function getEntityBadlyGeoLocalited() {
    	$res = array() ;
       	$entities[Person::COLLECTION] = PHDB::find(Person::COLLECTION);
       	$entities[Organization::COLLECTION] = PHDB::find(Organization::COLLECTION);
       	$entities[Event::COLLECTION] = PHDB::find(Event::COLLECTION);
       	$entities[Project::COLLECTION] = PHDB::find(Project::COLLECTION);
       	foreach ($entities as $type => $typeEntities) {
	       	foreach ($typeEntities as $key => $entity) {
	       		if(!empty($entity['address'])){
	       			if(!empty($entity['address']["codeInsee"]) && !empty($entity['address']["postalCode"])){
	       				$insee = $entity['address']["codeInsee"];
	       				if(!empty($entity['geo'])){
	       					$find = false;
	       					$city = SIG::getCityByLatLngGeoShape($entity['geo']["latitude"], $entity['geo']["longitude"], $entity['address']["postalCode"]);
	     					if(!empty($city)){
	       						if($city["insee"] != $insee){
		       						$result["id"] = (String)$entity["_id"];
	       							$result["name"] = $entity["name"];
			       					$result["error"] = "Cette entité est mal géolocalisé";
			       					$res[$type][]= $result ;
		       					}
	       					}else{
	       						$result["id"] = (String)$entity["_id"];
	       						$result["name"] = $entity["name"];
		       					$result["error"] = "Nous n'avons pas trouver de commune";
		       					$res[$type][]= $result ;
	       					}
	       				}else{
		       				$result["id"] = (String)$entity["_id"];
	       					$result["name"] = $entity["name"];
		       				$result["error"] = "Cette entité n'a pas de géolocalisation";
		       				$res[$type][]= $result ;
		       			}
	       			}else{
	       				$result["id"] = (String)$entity["_id"];
	       				$result["name"] = $entity["name"];
	       				$result["error"] = "Cette entité n'a pas de code Insee et/ou de code postal";
	       				$res[$type][]= $result ;
	       			}	
	       		}
	       	}
       	}	
        return $res;
    }

}