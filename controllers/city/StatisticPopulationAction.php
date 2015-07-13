<?php
class StatisticPopulationAction extends CAction
{
    public function run($insee,$typeData="population", $type=null)
    {
        $controller=$this->getController();
        /*$where = array("codeInsee.".$insee.".".$typeData => array( '$exists' => 1 ));
    	$fields = array("codeInsee.".$insee);*/
       	$where = array("insee"=>$insee, $typeData => array( '$exists' => 1 ));
    	$fields = array();

    	
    	$cityData = City::getWhereData($where, $fields);
		$where = array("insee" => $insee);
		$fields = array("name");

		$city = City::getWhere($where, $fields);
		foreach ($city as $key => $value) {
			$name = $value["name"];
		}


		foreach ($cityData as $key => $value) {
			foreach ($value as $k => $v) {
				$cityData = array($name => array($insee => array($k => $v )));
			}
		}

		$params["nbCitiesDepartement"] = count(City::getDepartementCitiesByInsee($insee));
		$params["nbCitiesRegion"] = count(City::getRegionCitiesByInsee($insee));
    	$params["cityData"] = $cityData;

        $params["title"] = "Population/An";


        if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("statistiquePop", $params,true);
	    else
	        $controller->render("statistiquePop",$params);
    }
}