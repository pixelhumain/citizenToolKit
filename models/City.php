<?php

class City {

	const COLLECTION = "cities";
	const CONTROLLER = "city";
	const COLLECTION_DATA = "cityData";
	const REGION = "region";
	const DEPARTEMENT = "departement";
	const COLLECTION_IMPORTHISTORY = "importHistory";

	public static function getWhere($params, $fields=null, $limit=20) 
	{
	  	$city =PHDB::findAndSort( self::COLLECTION,$params, array("created" =>1), $limit, $fields);
	  	return $city;
	}

	public static function getWhereData($params, $fields=null, $limit=20) 
	{
	  	$cityData =PHDB::findAndSort( self::COLLECTION_DATA,$params, array("created" =>1), $limit, $fields);
	  	return $cityData;
	}

	public static function getIdByInsee($insee){
		$id = null;
		$where = array("insee" => $insee);
		$city = self::getWhere($where);
		foreach ($city as $key => $value) {
			$id = $value["_id"];
		}
		return $id;
	}

	public static function getCodeRegion($insee){
		$where = array("insee" => $insee);
		$fields = array("region");
		$region = PHDB::findOne( self::COLLECTION, $where ,$fields);
		return $region;
	}

	public static function getCodeDepartement($insee){
		$where = array("insee" => $insee);
		$fields = array("dep");
		$dep = PHDB::findOne( self::COLLECTION, $where ,$fields);
		return $dep;
	}

	public static function getRegionCitiesByInsee($insee, $fields=null){
		$region = self::getCodeRegion($insee);
		$where = array("region" => $region["region"]);
		$cities = self::getWhere($where, $fields, 1000);
		
		return $cities;
	}

	public static function getDepartementCitiesByInsee($insee, $fields=null){
		$region = self::getCodeRegion($insee);
		$dep = self::getCodeDepartement($insee);
		$where = array("region" => $region["region"], "dep" => $dep["dep"]);
		$cities = self::getWhere($where, $fields , 1000);
		return $cities;
	}


	public static function getDepartementByInsee($insee, $fields, $type){
		$mapDataDep = array();
		$cities = self::getDepartementCitiesByInsee($insee);
		foreach ($cities as $key => $value) {
			$return = array("codeInsee" => $value["insee"]);
			$where = array("codeInsee.".$value["insee"].".".$type => array( '$exists' => 1 ));
			$fields = array("codeInsee.".$value["insee"]);
			$cityData = City::getWhereData($where, $fields);
			if(isset($cityData)){
				foreach ($cityData as $k => $v) {
					$mapDataDep[$value["name"]] = $v["codeInsee"];
				}
			}	
		}
		return $mapDataDep;
	}


	public static function getRegionByInsee($insee, $fields, $type){
		$mapDataRegion = array();
		$cities = self::getRegionCitiesByInsee($insee);
		foreach ($cities as $key => $value) {
			$return = array("codeInsee" => $value["insee"]);
			$where = array("codeInsee.".$value["insee"].".".$type => array( '$exists' => 1 ));
			$fields = array("codeInsee.".$value["insee"]);
			$cityData = City::getWhereData($where, $fields);
			if(isset($cityData)){
				foreach ($cityData as $k => $v) {
					$mapDataRegion[$value["name"]] = $v["codeInsee"];
				}
			}
		}
		return $mapDataRegion;
	}
}
?>