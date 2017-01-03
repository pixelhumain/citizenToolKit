<?php
/**
* retreive dynamically 
*/
class AgendaAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        
        CO2Stat::incNbLoad("co2-agenda");

        $params = array("type" => "events");

    	//if(@$_POST['renderPartial'] == true)
    	//echo $controller->renderPartial("liveStream", $params, true);
    	//else
    	echo $controller->renderPartial("social", $params, true);
    }
}