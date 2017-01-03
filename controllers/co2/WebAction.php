<?php
/**
* retreive dynamically 
*/
class WebAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        
        CO2Stat::incNbLoad("co2-web");
    	
    	$params = array();
    	echo $controller->renderPartial("web", $params, true);
    }
}