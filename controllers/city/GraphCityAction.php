<?php

class GraphCityAction extends CAction{
   
   public function run($insee, $typeData="population", $typeGraph=null, $typeZone=null, $optionData=null, $inseeCities=null){

        $controller=$this->getController();
        
        if($optionData!=null)
        {   
            $option = [];
            $exploseOptionData = explode('&', $optionData);
            foreach ($exploseOptionData as $key => $value) {
                $param = explode("=", $value);
                $option[] = $param[1];
            } 
        }
        else
        	$option = array();

        if($inseeCities!=null)
        {   
            $listCitiesChecked = [];
            $exploseInseeCities = explode('&', $inseeCities);
            foreach ($exploseInseeCities as $key => $value) {
                $param = explode("=", $value);
                $listCitiesChecked[] = $param[1];
            }
            $listCitiesChecked[] = $insee;
        } 




        if($inseeCities!=null)
        {   
            $cities = [];
            $exploseCitiesData = explode('&', $inseeCities);
            foreach ($exploseCitiesData as $key => $value) {
                $paramCities = explode("=", $value);
                $cities[] = $paramCities[1];
            } 
        }

		$where = array("insee"=>$insee, $typeData => array( '$exists' => 1 ));

        if(!empty($option))
        {
            foreach ($option as $key => $value){
                $fields[] = $typeData.$value;
            }
            
        }
        else
            $fields = array($typeData);


    	if(isset($typeZone) && strcmp($typeZone, City::REGION)==0)
    	{
            if(isset($listCitiesChecked) && $listCitiesChecked != null)
                $cityData = City::getRegionByInsee($insee,$fields, $typeData, $option, $listCitiesChecked);
            else
                $cityData = City::getRegionByInsee($insee,$fields, $typeData, $option);
        }	
    	else if(isset($typeZone) && strcmp($typeZone, City::DEPARTEMENT)==0)
    	{
            if(isset($listCitiesChecked) && $listCitiesChecked != null)
                $cityData = City::getDepartementByInsee($insee,$fields, $typeData, $option, $listCitiesChecked);
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

		$params["cityData"] = json_encode($cityData);
		$params["typeGraph"] = $typeGraph;

  		$params["name_id"] = $typeData;
        if(isset($type))
            $params["name_id"] = $params["name_id"] . $type;
        if(isset($typeGraph))
            $params["name_id"] = $params["name_id"] . $typeGraph;
        if(isset($option))
        {
            $params["optionData"] = json_encode($option);
            foreach ($option as $key => $value){
                $exploseOption = explode('.', $value);
                foreach ($exploseOption as $k => $v){
                    $params["name_id"] = $params["name_id"] .  $v;
                }
            }
           
        }else
        {
			$params["optionData"] = json_encode(array());
        }

        if($inseeCities!=null)  
        {
            $params["inseeCities"] = $cities;
            foreach ($cities as $key => $value){
                $params["name_id"] = $params["name_id"] .  $value;  
            }
           
        } 
            
           
            
            
        $params["name_id"] = str_replace("_", "", $params["name_id"]);
        $params["name_id"] = str_replace(" ", "", $params["name_id"]);
        $params["name_id"] = str_replace("-", "", $params["name_id"]);
        $params["name_id"] = str_replace(".", "", $params["name_id"]);




        //$controller->render("graphCity",$params);
        $controller->renderPartial("graphCity",$params);
    }
    	
}

?>
