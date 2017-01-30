<?php
/**
* retreive dynamically 
*/
class PowerAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        
        CO2Stat::incNbLoad("co2-power");

        $params = array("type" => "vote");

        //if(@$_POST['renderPartial'] == true)
        //echo $controller->renderPartial("liveStream", $params, true);
        //else
        echo $controller->renderPartial("social", $params, true);
    }
}