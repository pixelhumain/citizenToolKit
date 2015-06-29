<?php
/**
 */
class GetCityDataAction extends CAction
{
	 public function run($insee, $typeData, $type=null)
    {

    	$where = array("codeInsee.".$insee.".".$typeData => array( '$exists' => 1 ));
    	$fields = array("codeInsee.".$insee);
    	if(isset($type) && strcmp($type, City::REGION)==0)
    		$cityData = City::getRegionByInsee($insee,$fields, $typeData);
    	else if(isset($type) && strcmp($type, City::DEPARTEMENT)==0)
    		$cityData = City::getDepartementByInsee($insee,$fields, $typeData);
    	else{
    			$cityData = City::getWhereData($where, $fields);
    			$where = array("insee" => $insee);
    			$fields = array("name");

    			$city = City::getWhere($where, $fields);
    			foreach ($city as $key => $value) {
    				$name = $value["name"];
    			}
    			foreach ($cityData as $key => $value) {
    				foreach ($value as $k => $v) {
    					$cityData = array($name => $v);
    				}
    			}
    			//$cityData[$city["name"]] = $cityData[0];
    			//unset($cityData[0]);
    		}
    	Rest::json($cityData);
    }
}