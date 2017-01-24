<?php
/**
* retreive dynamically 
*/
class IndexAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        
        $CO2DomainName = isset(Yii::app()->params["CO2DomainName"]) ? Yii::app()->params["CO2DomainName"] : "CO2";

        Yii::app()->theme = "CO2";
        $params = CO2::getThemeParams();

        $hash = $params["pages"]["#co2.index"]["redirect"];
        //CO2Stat::incNbLoad("co2-social");
    	
        $params = array("type" => @$type );

        if(!@$hash || @$hash=="") $hash="social";
    	echo $controller->renderPartial($hash, $params, true);
    }
}