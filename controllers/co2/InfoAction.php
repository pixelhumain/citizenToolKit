<?php
/**
* retreive dynamically 
*/
class InfoAction extends CAction
{
    public function run($p) {
    	$controller=$this->getController();
        
        $CO2DomainName = isset(Yii::app()->params["CO2DomainName"]) ? Yii::app()->params["CO2DomainName"] : "CO2";

        $page = @$p ? $p : "apropos";

        $params = array();

        echo $controller->renderPartial("info/" . $CO2DomainName . "/" . $page, $params, true);
    }
}