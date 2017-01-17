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
	public static function updateEntityGeoposition($entityType, $entityId, $latitude, $longitude, $addressIndex=null){

		error_log("updateEntity Start");
		if(!empty($latitude) && !empty($latitude) ){
			$geo = array("@type"=>"GeoCoordinates", "latitude" => $latitude, "longitude" => $longitude);
			$geoPosition = array("type"=>"Point", "coordinates" => array(floatval($longitude), floatval($latitude)));
		}else{
			$geo = null;
			$geoPosition = null;
		}

		if(!empty($addressIndex)){
			$geo["addressesIndex"] = $addressIndex ;
			$geoPosition["addressesIndex"] = $addressIndex ;
		}

		//PH::update($entityType,array("geo" => $geo));

		/*if($entityType == PHType::TYPE_CITOYEN || $entityType == PHType::TYPE_PERSON ){
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
		}*/

		$types = array(Person::COLLECTION, Event::COLLECTION, Organization::COLLECTION, Project::COLLECTION);
		if(in_array($entityType, $types)){
		 	Element::updateField($entityType, $entityId, "geo", $geo);
		 	Element::updateField($entityType, $entityId, "geoPosition", $geoPosition);
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
		if (empty($geopos)) throw new CTKException("Impossible to find a postalCode for insee :".$inseeCode." and cp : ".$postalCode);
		
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

	////récupère la ville qui correspond à une position géographique
	//récupère les villes qui se trouvent dans un rayon de 50km d'un position geo
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
			$request = array("postalCodes.geoPosition" => array( '$exists' => true ),
							 "postalCodes.geoPosition"  => 
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

			$cities =	PHDB::findAndSort(City::COLLECTION, $request, array());
			$allCities = array();
			foreach ($cities as $key => $value) {
				foreach ($value["postalCodes"] as $keyCP => $valueCP) {
					$city = $value;
					$city["typeSig"] = "city";
					$city["cp"] = $valueCP["postalCode"];
					$city["name"] = $valueCP["name"];
					$city["geo"] = $valueCP["geo"];
					$city["geoPosition"] = $valueCP["geoPosition"];
					$allCities[] = $city;

				}
			}
			//var_dump($oneCity);
		}

		// var_dump($request);
		// var_dump($oneCity);
		//var_dump($oneCity);
		return $allCities;
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
					$newCity["geo"] = @$data["geo"];
					$newCity["geoPosition"] = @$data["geoPosition"];
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
		$address["depName"] = isset($city['depName']) ? $city['depName'] : "";
		$address["regionName"] = isset($city['regionName']) ? $city['regionName'] : "";
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
	       					if( ($entity['geo']["latitude"]>= -90 && $entity['geo']["latitude"]<= 90) && ($entity['geo']["longitude"]>= -180 && $entity['geo']["longitude"]<= 180) ){

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
		       					$result["error"] = "Cette entité a une mauvaise géolocalisation";
		       					$res[$type][]= $result ;
				            }

	       				}else{
		       				$result["id"] = (String)$entity["_id"];
		       				//$result["name"] = (String)$entity["_id"];
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

    // Nominatim
    public static function getLocalityByLatLonNominatim($lat, $lon){
    	try{
			$url = "http://nominatim.openstreetmap.org/reverse?format=json&lat=".$lat."&lon=".$lon."&zoom=18&addressdetails=1" ;
        	$res =  file_get_contents($url);
	        return $res;
        }catch (CTKException $e){
            return null ;
        }
    }


    public static function getGeoByAddressNominatim($street = null, $cp = null, $city = null, $country = null, $polygon_geojson = null, $extratags = null){
        try{
	        $url = "http://nominatim.openstreetmap.org/search?format=json&addressdetails=1" ;
	        if(!empty($street))
	            $url .= "&street=".str_replace(" ", "+", $street);
	        
	        if(!empty($cp)){
	            $url .= "&postalcode=".str_replace(" ", "+", $cp);
	        }
	            
	        if(!empty($city)){
	            $url .= "&city=".str_replace(" ", "+", $city);
	        }
	        if(!empty($country))
	            $url .= "&countrycodes=".self::changeCountryForNominatim($country);

	        if(!empty($polygon_geojson)){
	            $url .= "&polygon_geojson=1";
	        }

	        if(!empty($extratags)){
	            $url .= "&extratags=1";
	        }
	        //var_dump($url);
	        $res =  file_get_contents($url);
	        return $res;
			//return self::getUrl($url);
		}catch (CTKException $e){
            return null ;
        }
    }

    public static function changeCountryForNominatim($country){
		$codeCountry = array("FR" => array("RE")) ;
		foreach ($codeCountry as $key => $value) {
			if(in_array($country, $value))
				$country = $key ;
		}
		return $country ;

	}

    // GoogleMap
    public static function getGeoByAddressGoogleMap($street = null, $cp = null, $city = null, $country = null, $polygon_geojson = null){
        try{
	        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" ;

	        
	        
	        if(!empty($street)){
	            $url .= str_replace(" ", "+", $street);
	            //$url .= "&components=route:".str_replace(" ", "+", $street);
	        }
	        if(!empty($cp)){
	            if(!empty($street))
	            	$url .= "+".str_replace(" ", "+", $cp);
	                //$url .= "|postal_code:".str_replace(" ", "+", $cp);
	            else
	            	$url .= str_replace(" ", "+", $cp);
	            	//$url .= "&components=postal_code:".str_replace(" ", "+", $cp);
	        }
	        if(!empty($city)){
	            $url .= "+".str_replace(" ", "+", $city);
	        }
	        if(!empty($country)){
	        	$url .= "&components=country:".str_replace(" ", "+", $country);
	        }
	        $url .= "&key=".Yii::app()->params['google']['keyMaps'] ;
	        //var_dump($url);
	        $res =  file_get_contents($url);
	        return $res;
	        //return self::getUrl($url) ;
        }catch (CTKException $e){
            return null ;
        }
    }

    // DataGouv
    public static function getGeoByAddressDataGouv($street = null, $cp = null, $city = null, $polygon_geojson = null){
	    try{
	        $url = "http://api-adresse.data.gouv.fr/search/?q=" ;
	        if(!empty($street))
	            $url .= str_replace(" ", "+", $street);
	        
	        if(!empty($city)){
	            $url .= "+".str_replace(" ", "+", $city);
	        }

	        if(!empty($cp)){
	            $url .= "&postcode=".str_replace(" ", "+", $cp);
	        }
	        $url .= "&type=street";
	        //var_dump($url);
	        /*$res =  file_get_contents($url);
	        return $res;*/
	        return self::getUrl($url) ;
        }catch (CTKException $e){
            return null ;
        }
    }

    public static function getLocalityByLatLonDataGouv($lat, $lon){
    	try{
	        $url = "http://api-adresse.data.gouv.fr/reverse/?lon=".$lon."&lat=".$lat."&zoom=18&addressdetails=1" ;
	        $res =  file_get_contents($url);
	        return $res;
        }catch (CTKException $e){
            return null ;
        }
    }

    public static function getUrl($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result ;
    }


    public static function getWikidata($wikidataID){
        try{
	        $url = "https://www.wikidata.org/wiki/Special:EntityData/".$wikidataID.".json" ;
	        $res =  file_get_contents($url);
	         //var_dump($res);
	        return $res;
			//return self::getUrl($url);
		}catch (CTKException $e){
            return null ;
        }
    }


    public static function getFormatGeo($latitude, $longitude){
        return array("@type"=>"GeoCoordinates", "latitude" => $latitude, "longitude" => $longitude);
    }

    public static function getFormatGeoPosition($latitude, $longitude){
        return array("type"=>"Point", "float"=> true, "coordinates" => array(floatval($longitude), floatval($latitude)));
    }

}
