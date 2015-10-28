<?php

class City {

	const COLLECTION = "cities";
	const CONTROLLER = "city";
	const COLLECTION_DATA = "cityData";

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

			$where = array("insee"=>array('$in' =>$tabInsee) , $typeData => array( '$exists' => 1 ));
		}
		else
			$where = array("insee"=>array('$in' =>$inseeCities) , $typeData => array( '$exists' => 1 ));

		/*$fields = array("insee", $typeData.$option) ;
		$sort = array($typeData.$option => -1);*/
        $fields[] = "insee" ;
		foreach ($option as $key => $value) {
			$fields[] = $typeData.$value;
			$sort[] = array($typeData.$value => -1);
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
		$simpleCity["geo"] = @$city["geo"];
		$simpleCity["type"] = "city";
		
		return $simpleCity;
	}

}
?>