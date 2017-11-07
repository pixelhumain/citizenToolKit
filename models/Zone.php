<?php

class Zone {

	const COLLECTION = "zones";
	const CONTROLLER = "zone";
	const TRANSLATE = "translate";
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
	    "level1" => array("name" => "level1"),
	    "level2" => array("name" => "level2"),
	    "level3" => array("name" => "level3"),
	    "level4" => array("name" => "level4"),
	    "level1Name" => array("name" => "level1Name"),
	    "level2Name" => array("name" => "level2Name"),
	    "level3Name" => array("name" => "level3Name"),
	    "level4Name" => array("name" => "level4Name"),
	    "osmID" => array("name" => "osmID"),
	    "wikidataID" => array("name" => "wikidataID"),
	    "modified" => array("name" => "modified"),
	    "updated" => array("name" => "updated"),
	    "creator" => array("name" => "creator"),
	    "created" => array("name" => "created"),
	    "new" => array("name" => "new")*/
	);

	public static function getById($id,$fields=array()) {
	  	$zone = PHDB::findOne(self::COLLECTION, array("_id"=>new MongoId($id)), $fields);
	  	return $zone;
	}

	public static function getByKey($key) {
	  	$zone = PHDB::findOne(self::COLLECTION, array("key"=> $key));
	  	return $zone;
	}

	public static function getTranslateById($id, $type) {
	  	$translate = PHDB::findOne(self::TRANSLATE, array("parentId"=> $id, "parentType" => $type));
	  	return $translate;
	}

	public static function getZoneAndTranslateById($id) {
	  	$zone = self::getById($id);
	  	$translate = self::getTranslateById($id, Zone::COLLECTION);
	  	$zone["translates"] = $translate["translates"];
	  	return $zone;
	}

	/* Retourne des infos sur la commune dans la collection cities" */
	public static function getWhere($params, $fields=null, $limit=20) {
	  	$zones =PHDB::findAndSort( self::COLLECTION,$params, array(), $limit, $fields);
	  	return $zones;
	}

	public static function getDetailById($id){
		$where = array("_id"=>new MongoId($id));
		$zone = PHDB::findOne(self::COLLECTION, $where);
		return $zone;
	}

	public static function getLevelIdById($id, $zone=null, $type){

		if(empty($zone)){
			$where = array("_id"=>new MongoId($id));
			$zone = PHDB::findOne($type, $where);
		}

		$res = array("level1" => $zone["level1"]);

		if(!empty($zone["level2"]))
			$res["level2"] = $zone["level2"];
		if(!empty($zone["level3"]))
			$res["level3"] = $zone["level3"];
		if(!empty($zone["level4"]))
			$res["level4"] = $zone["level4"];

		return $res;
	}


	public static function createLevel($name, $countryCode, $level, $level2 = null, $level3 = null){
		$zoneNominatim = array() ;
		$zone = array();

		$state = false;
		$county = false;


		if($level == "2"){
			$state = true;
		}else if($level == "3"){
			if($countryCode == "BE")
				$county = true;
			else
				$state = true;
		}else if($level == "4"){
			$county = true;
		}

		$zoneNominatim = json_decode(SIG::getGeoByAddressNominatim(null,null, null, $countryCode, true, true, $name, $state, $county), true);

		if(empty($zoneNominatim)){
			$resNominatim = json_decode(SIG::getGeoByAddressNominatim(null,null, $name, $countryCode, true, true), true);
			if(!empty($resNominatim)){
				foreach ($resNominatim as $key => $value) {
					if(empty($value["address"]["city"])){
						$zoneNominatim = array($value);
					}
				}
			}
		}
		
		if(!empty($zoneNominatim)){
			$zone["name"] = $name;
			$zone["countryCode"] = $countryCode;
			$zone["level"] = array($level);
			if($level != "1"){
				$zone["level1"] = self::getIdCountryByCountryCode($countryCode);
				if($level != "2" && !empty($level2)){
					$zone["level2"] = self::getIdLevelByNameAndCountry($level2, "2", $countryCode);
				}
				if($level != "3" && !empty($level3)){
					$zone["level3"] = self::getIdLevelByNameAndCountry($level3, "3", $countryCode);
				}
			}

			$zone["geo"] = SIG::getFormatGeo($zoneNominatim[0]["lat"], $zoneNominatim[0]["lon"]);
			$zone["geoPosition"] = SIG::getFormatGeoPosition($zoneNominatim[0]["lat"], $zoneNominatim[0]["lon"]);
			//$zone["geoShape"] = $zoneNominatim[0]["geojson"];
			if(!empty($zoneNominatim[0]["osm_id"]))
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
			Zone::insertTranslate( (String)$zone["_id"], 
    									self::COLLECTION, 
    									$zone["countryCode"],
    									$zone["name"],
    									(!empty($zone["osmID"]) ? $zone["osmID"] : null),
    									(!empty($zone["wikidataID"]) ? $zone["wikidataID"] : null));
			// $key = self::createKey($zone);

			// PHDB::update(self::COLLECTION,
			// 		array("_id"=>new MongoId($zone["_id"])),
			// 		array('$set' => array("key" => $key))	
			// );
			$res = array( 	"result" => true, 
							"msg" => "création Country", "zone"=>$zone);
		}
		return $res;
	}


	public static function createKey($zone){
		$key = $zone["countryCode"];
		if(in_array("1", $zone["level"]) ){
			$key .= "@".(String)$zone["_id"] ;
		}else{
			$key .= "@".( ( empty($zone["level1"]) ) ? "" : $zone["level1"] );
		}

		if(in_array("2", $zone["level"]) || in_array("3", $zone["level"]) || in_array("4", $zone["level"])){

			if(in_array("2", $zone["level"])){
				$key .= "@".(String)$zone["_id"] ;
			}
			else {
				$key .= "@".( ( empty($zone["level2"]) ) ? "" : $zone["level2"] );
			}

			if(in_array("3", $zone["level"]) || in_array("4", $zone["level"])){
				if(in_array("3", $zone["level"])){
					$key .= "@".(String)$zone["_id"] ;
				}
				else{
					$key .= "@".( ( empty($zone["level3"]) ) ? "" : $zone["level3"] );
				}

				if(in_array("4", $zone["level"])){
					$key .= "@".(String)$zone["_id"] ;
				}
			}
		}
		return $key ;
	}

	public static function getCountryByCountryCode($countryCode){
		$where = array(	"countryCode"=> $countryCode,
						"level" => "1");
		$country = PHDB::findOne(self::COLLECTION, $where);
		return $country;
	}

	public static function getIdCountryByCountryCode($countryCode){
		$country = self::getCountryByCountryCode($countryCode);
		return ( ( empty($country["_id"]) ) ? null : (String)$country["_id"] );
	}

	public static function getLevelByNameAndCountry($name, $level, $countryCode){
		$where = array(	"countryCode"=> $countryCode,
						"level" => $level,
						"name" => $name);
		$zone = PHDB::findOne(self::COLLECTION, $where);
		return $zone;
	}

	public static function getIdLevelByNameAndCountry($name, $level, $countryCode){
		$zone = self::getLevelByNameAndCountry($name, $level, $countryCode);
		return ( ( empty($zone["_id"]) ) ? null : (String)$zone["_id"] );
	}


	public static function getAreaAdministrative($countryCode, $level, $idZone = null, $idInCountry = null, $name = null){
		$where = array(	"countryCode"=> $countryCode,
						"level" => $level);

		$zone = array();
		if(!empty($idInCountry) || !empty($name) ){

			if(!empty($idInCountry))
				$where["idInCountry"] = $idInCountry ;

			if(!empty($name))
				$where["name"] = $name ;

			$zone = PHDB::findOne(self::COLLECTION, $where);
		}
		
		return $zone;
	}




	public static function insertTranslate($parentId, $parentType, $countryCode, $origin, $osmID = null, $wikidataID = null){
		$res = array("result" => false);
		$translate = array();
		$info = array();

		if($parentType != self::COLLECTION && $parentType != City::COLLECTION)

		if(!empty($osmID)){
			$zoneNominatim =  json_decode(SIG::getUrl("http://nominatim.openstreetmap.org/lookup?format=json&namedetails=1&osm_ids=R".$osmID), true);
		
			if(!empty($zoneNominatim) && !empty($zoneNominatim[0]["namedetails"])){
				
				foreach ($zoneNominatim[0]["namedetails"] as $keyName => $valueName) {
					$arrayName = explode(":", $keyName);
					if(!empty($arrayName[1]) && $arrayName[0] == "name" && strlen($arrayName[1]) == 2 && $origin != $valueName){
						$translate[strtoupper($arrayName[1])] = $valueName;
					}
				}
			}
		}

		if(!empty($wikidataID)){

			$zoneWiki =  json_decode(SIG::getUrl("https://www.wikidata.org/wiki/Special:EntityData/".$wikidataID.".json"), true);
		
			if(!empty($zoneWiki) && !empty($zoneWiki["entities"][$wikidataID]["labels"])){
				foreach ($zoneWiki["entities"][$wikidataID]["labels"] as $keyName => $valueName) {
					
					if(strlen($keyName) == 2 && !array_key_exists(strtoupper($keyName), $translate) && $origin != $valueName["value"]){
						$translate[strtoupper($keyName)] = $valueName["value"];
					}
				}
			}
		}

		//if(!empty($translate)){
		$info["countryCode"] = $countryCode;
		$info["parentId"] = $parentId;
		$info["parentType"] = $parentType;
		$info["translates"] = $translate;
		$info["origin"] = $origin;
		PHDB::insert(Zone::TRANSLATE, $info);
		PHDB::update($parentType, 
					array("_id"=>new MongoId($parentId)),
					array('$set' => array("translateId" => (String)$info["_id"]))
		);
		$res = array("result" => true, "translate" => $info);
		//}
		return $res ;
	}

	public static function getNameCountry($id){
		$translates = self::getTranslateById($id, Zone::COLLECTION);
		$userT = strtoupper(Yii::app()->language) ;
		if(!empty($translates) ){
			$name = (!empty($translates["translates"][$userT]) ? $translates["translates"][$userT] : $translates["origin"]);
		}else
			$name = "";
		
		return $name;
	}


	public static function getNameOrigin($id){
		$translates = self::getTranslateById($id, Zone::COLLECTION);
		return $translates["origin"];
	}

	public static function getListCountry(){
		$where = array(	"level" => "1");
		$fields = array("name","level", "translateId", "countryCode", "ownACity");
		$zones = PHDB::find(self::COLLECTION, $where, $fields);
		$res = array();
		$trad = PHDB::find(	self::TRANSLATE, 
							array( 	"parentId"=> array('$in' => array_keys($zones) ), 
									"parentType" => Zone::COLLECTION ), 
							array("origin", "translates.".strtoupper(Yii::app()->language) ) );
		foreach ($zones as $key => $zone) {
			$newZone = array( 	"name" => ( !empty($trad[$zone["translateId"]]["translates"][strtoupper(Yii::app()->language)])  ? $trad[$zone["translateId"]]["translates"][strtoupper(Yii::app()->language)] : $trad[$zone["translateId"]]["origin"]),
								"countryCode" => $zone["countryCode"],
								"level" => $zone["level"],
								"translateId" => $zone["translateId"]);
			if( !empty($zone["ownACity"]) )
				$newZone["ownACity"] = $zone["ownACity"] ;
			$res[$key] = $newZone ;
		}
		asort($res);
		return $res ;
	}


	public static function getWhereTranlate($params, $fields=null, $limit=20) {
	  	$zones =PHDB::findAndSort( self::TRANSLATE,$params, array(), $limit, $fields);
	  	return $zones;
	}

}
?>