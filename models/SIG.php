<?php
/*
Contains anything generix for the site 
 */
class SIG
{
    const CITIES_COLLECTION_NAME = "cities";

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

	//return geographical position of inseeCode
	public static function getGeoPositionByInseeCode($inseeCode){
		$city = self::getCityByCodeInsee($inseeCode);
		$geopos = array( 	"@type" => "GeoCoordinates",
							"latitude" => $city["geo"]["latitude"],
							"longitude" => $city["geo"]["longitude"]);
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
	public static function getCityByLatLng($lat, $lng, $cp){

		$request = array("geoShape"  => 
						  array('$geoIntersects'  => 
						  	array('$geometry' => 
						  		array("type" 	    => "Point", 
						  			  "coordinates" => array($lng, $lat))
						  		)));
		if($cp != null){
			$request = array_merge(array("cp"  => $cp), $request);
		}

		$oneCity =	PHDB::findOne(self::CITIES_COLLECTION_NAME, $request);
		return $oneCity;
	}

	//récupère le code insee d'une position geographique
	//(préciser un CP pour un résultat plus rapide)
	public static function getInseeByLatLngCp($lat, $lng, $cp){
		$oneCity =	self::getCityByLatLng($lat, $lng, $cp);
		if($oneCity != null && $oneCity["insee"] != null) return $oneCity;//["insee"];
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

		$city = PHDB::findOne(self::CITIES_COLLECTION_NAME, array("insee" => $codeInsee));
		if (empty($city)) {
			throw new CTKException("Impossible to find the city with the insee code : ".$codeInsee);
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

		$city = PHDB::findAndSort(self::CITIES_COLLECTION_NAME, array("cp" => $postalCode), array("name" => -1));
		return $city;
	}

	public static function getAdressSchemaLikeByCodeInsee($codeInsee) {
		$city = self::getCityByCodeInsee($codeInsee);

		$address = array("@type"=>"PostalAddress", "postalCode"=> $city['cp'], 
				"addressLocality" => $city["alternateName"], "codeInsee" => $codeInsee);
		return $address;
	}

	public static function getGeoQuery($params, $att){
		return array(	$att  => array( '$exists' => true ),
    					$att.'.latitude' => array('$gt' => floatval($params['latMinScope']), '$lt' => floatval($params['latMaxScope'])),
						$att.'.longitude' => array('$gt' => floatval($params['lngMinScope']), '$lt' => floatval($params['lngMaxScope']))
					  );
	}
}