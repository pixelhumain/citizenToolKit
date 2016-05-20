<?php
/**
 */
class GetCityDataAction extends CAction
{
	public function run($insee, $typeData, $typeZone=null, $option=null)
    {
        

    	$where = array("insee"=>$insee, $typeData => array( '$exists' => 1 ));

        if(isset($_POST['optionData']))
        {
            foreach ($_POST['optionData'] as $key => $value){
                $fields[] = $typeData.$value;
            }
            
        }
        else
            $fields = array($typeData);


    	if(isset($typeZone) && strcmp($typeZone, City::REGION)==0)
    	{
            if(isset($_POST['optionCities']) && $_POST['optionCities'] != null)
                $cityData = City::getRegionByInsee($insee,$fields, $typeData, $option, $_POST['optionCities']);
            else
                $cityData = City::getRegionByInsee($insee,$fields, $typeData, $option);
        }	
    	else if(isset($typeZone) && strcmp($typeZone, City::DEPARTEMENT)==0)
    	{
            if(isset($_POST['optionCities']) && $_POST['optionCities'] != null)
                $cityData = City::getDepartementByInsee($insee,$fields, $typeData, $option, $_POST['optionCities']);
            else
                $cityData = City::getDepartementByInsee($insee,$fields, $typeData, $option);
        }	
    	else{
    			$cityData = City::getWhereData($where, $fields);
    			$where = array("insee" => $insee);
    			$fields = array("name");
                //var_dump($cityData);
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