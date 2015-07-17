<?php
class StatisticCityAction extends CAction
{
    public function run($insee){
    	$controller=$this->getController();
    	$params = array();
    	$controller->render("statistiqueCity",$params);
    }
}

?>