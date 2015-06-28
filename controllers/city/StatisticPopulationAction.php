<?php
class StatisticPopulationAction extends CAction
{
    public function run($insee, $type=null)
    {
        $controller=$this->getController();
       	$where = array("codeInsee.".$insee => array( '$exists' => 1 ));
    	$fields = array("codeInsee.".$insee);
    	$params["cityData"] = City::getWhereData($where, $fields);

        $params["title"] = "Population/An";
        if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("statistiquePop", $params,true);
	    else
	        $controller->render("statistiquePop",$params);
    }
}