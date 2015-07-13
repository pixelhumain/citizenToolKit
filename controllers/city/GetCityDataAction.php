<?php
/**
 */
class GetCityDataAction extends CAction
{
	 public function run($insee, $typeData, $type=null)
    {
        
    	$where = array("insee"=>$insee, $typeData => array( '$exists' => 1 ));
        $fields = array();

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
                        if($k == $typeData)
                        $cityData = array($name => array($insee => array($k => $v )));
                    }
                }
    		}
    	Rest::json($cityData);
    }
}