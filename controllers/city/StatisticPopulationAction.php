<?php

class StatisticPopulationAction extends CAction
{
    public function run($insee,$typeData="population", $typeGraph=null, $type=null, $optionData=null, $inseeCities=null)
    {
    	if($optionData!=null)
        {   
            $option = array();
            $exploseOptionData = explode('&', $optionData);
            foreach ($exploseOptionData as $key => $value) {
                $param = explode("=", $value);
                
                //var_dump($param);
                $option[] = $param[1];
            } 
        }

        if($inseeCities!=null)
        {   
            $cities = array();
            $exploseCitiesData = explode('&', $inseeCities);
            foreach ($exploseCitiesData as $key => $value) {
                $param = explode("=", $value);
                
                //var_dump($param);
                $cities[] = $param[1];
            } 
        }
        

        $controller=$this->getController();
        $where = array("insee"=>$insee, $typeData => array( '$exists' => 1 ));

     	$fields = array($typeData);
        $cityDataTest = array();
        
     	if(isset($type) && strcmp($type, City::REGION)==0)
        {
            if($inseeCities != null)
                $cityDataTest = City::getRegionByInsee($insee,$fields, $typeData, null, $cities);
            else
                $cityDataTest = City::getRegionByInsee($insee,$fields, $typeData);
    		//$cityDataTest = City::getRegionByInsee($insee,$fields, $typeData);
        }
    	else if(isset($type) && strcmp($type, City::DEPARTEMENT)==0)
    	{
            if($inseeCities != null)
                $cityDataTest = City::getDepartementByInsee($insee,$fields, $typeData, null, $cities);
            else
                $cityDataTest = City::getDepartementByInsee($insee,$fields, $typeData);

            //$cityDataTest = City::getDepartementByInsee($insee,$fields, $typeData);
        }	
    	else{
	     	$cityData = City::getWhereData($where, $fields);
	  		$where = array("insee" => $insee);
	  		$fields = array("name");

			$city = City::getWhere($where, $fields);
			foreach ($city as $key => $value) 
			{
				$name = $value["name"];
			}
			//var_dump($cityData);
	  		foreach ($cityData as $key => $value) 
	  		{	
	  			foreach ($value as $k => $v) 
	   			{
	   				if($k == $typeData)
	    				$cityDataTest = array($name => array($insee => array($k => $v )));
	   			}
	  		}
	  	}

  		$params["nbCitiesDepartement"] = count(City::getDepartementCitiesByInsee($insee));
  		$params["nbCitiesRegion"] = count(City::getRegionCitiesByInsee($insee));
     	$params["cityData"] = $cityDataTest;
        $params["title"] = "Population/An";

        $params["name_id"] = $typeData;
        if(isset($type))
            $params["name_id"] = $params["name_id"] . $type;
        if(isset($typeGraph))
            $params["name_id"] = $params["name_id"] . $typeGraph;
        if(isset($option))
        {
            $params["optionData"] = $option;
            foreach ($option as $key => $value){
                $exploseOption = explode('.', $value);
                foreach ($exploseOption as $k => $v){
                    $params["name_id"] = $params["name_id"] .  $v;
                }
            }
           
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

          if(Yii::app()->request->isAjaxRequest)
           	echo $controller->renderPartial("statistiquePop", $params,true);
       	  else
           	$controller->render("statistiquePop",$params);
      }
  }

?>
