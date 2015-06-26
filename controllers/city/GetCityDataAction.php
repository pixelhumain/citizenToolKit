<?php
/**
 */
class GetCityDataAction extends CAction
{
	 public function run($insee, $type=null)
    {

    	$where = array("codeInsee.".$insee => array( '$exists' => 1 ));
    	$fields = array("codeInsee.".$insee);
    	if(isset($type) && strcmp($type, City::REGION)==0)
    		$cityData = City::getRegionByInsee($insee,$fields);
    	else if(isset($type) && strcmp($type, City::DEPARTEMENT)==0)
    		$cityData = City::getDepartementByInsee($insee,$fields);
    	else
    		$cityData = City::getWhereData($where, $fields);
    	Rest::json($cityData);
    }
}