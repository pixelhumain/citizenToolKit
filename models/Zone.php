<?php

class Zone {

	const COLLECTION = "zones";
	//const CONTROLLER = "zone";
	const COLOR = "#E6304C";
	const ICON = "fa-university";

	public static $dataBinding = array(
	   /* "name" => array("name" => "name", "rules" => array("required")),
	    "alternateName" => array("name" => "alternateName", "rules" => array("required")),
	    "insee" => array("name" => "insee", "rules" => array("required")),
	    "country" => array("name" => "birthDate", "rules" => array("required")),
	    "geo" => array("name" => "geo", "rules" => array("required","geoValid")),
	    "geoPosition" => array("name" => "geoPosition", "rules" => array("required","geoPositionValid")),
	    "geoShape" => array("name" => "geoShape"),
	 	"postalCodes" => array("name" => "postalCodes"),
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
	    "new" => array("name" => "new")*/
	);

	/* Retourne des infos sur la commune dans la collection cities" */
	public static function getWhere($params, $fields=null, $limit=20) {
	  	$zones =PHDB::findAndSort( self::COLLECTION,$params, array(), $limit, $fields);
	  	return $zones;
	}

	public static function getDetailById($id){
		$where = array("_id"=>new MongoId($id));
		$zone = PHDB::findOne(self::COLLECTION, $where);

		$city =  City::getByInsee($zone["insee"]);
		//$zone["cityName"] = $city["name"];
		$zone["depName"] = $city["depName"];
		$zone["regionName"] = $city["regionName"];
		return $zone;
	}


	/*public static function createCountry($countryName, $countryCode){
		$countryNominatim = Sig::getGeoByAddressNominatim(null,null, null, $countryCode, true, true, $countryName);
		$zone = array();
		if(!empty($countryNominatim)){
			$zone["name"] = $countryNominatim[0]["display_name"];
			$zone["countryCode"] = $countryCode;
			$zone["geo"] = Sig::getFormatGeo($countryNominatim[0]["lat"], $countryNominatim[0]["lon"]);
			$zone["geoPosition"] = Sig::getFormatGeoPosition($countryNominatim[0]["lat"], $countryNominatim[0]["lon"]);
			$zone["geoShape"] = $countryNominatim[0]["geojson"];
			$zone["osmID"] = $countryNominatim[0]["osm_id"];
			$zone["wikidataID"] = $countryNominatim[0]["extratags"]["wikidata"];
		}
		return $zone;
	}*/

	public static function createLevel($name, $countryCode, $level, $parentKey){
		$zoneNominatim = array() ;
		$zone = array();

		if($level == "1")
			$zoneNominatim = json_decode(SIG::getGeoByAddressNominatim(null,null, null, $countryCode, true, true, $name), true);
		else if($level == "2" || $level == "3" || $level == "4")
			$zoneNominatim = json_decode(SIG::getGeoByAddressNominatim(null,null, null, $countryCode, true, true, $name, true), true);
		
		if(!empty($zoneNominatim)){
			$zone["name"] = $name;
			$zone["countryCode"] = $countryCode;
			$zone["level"] = $level;
			if($level != "1"){
				
				$zone["parentKey"] = $parentKey;
			}
			$zone["geo"] = SIG::getFormatGeo($zoneNominatim[0]["lat"], $zoneNominatim[0]["lon"]);
			$zone["geoPosition"] = SIG::getFormatGeoPosition($zoneNominatim[0]["lat"], $zoneNominatim[0]["lon"]);
			$zone["geoShape"] = $zoneNominatim[0]["geojson"];
			$zone["osmID"] = $zoneNominatim[0]["osm_id"];
			if(!empty($zoneNominatim[0]["extratags"]["wikidata"]))
				$zone["wikidataID"] = $zoneNominatim[0]["extratags"]["wikidata"];
		}
		return $zone;
	}

	public static function save($zone){
		$res = array( 	"result" => false, 
						"error"=>"400",
						"msg" => "error" );
		if(!empty($zone)){
			PHDB::insert(self::COLLECTION, $zone );
			$res = array( 	"result" => true, 
							"msg" => "création Country" );
		}
	}

}
?>