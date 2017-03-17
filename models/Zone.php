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

}
?>