<?php
/**
* retreive dynamically 
*/
class SocialAction extends CAction
{
    public function run($type=null) {
    	$controller=$this->getController();
        

        CO2Stat::incNbLoad("co2-social");
    	
        $params = array("type" => @$type );

    	//if(@$_POST['renderPartial'] == true)
    	//echo $controller->renderPartial("liveStream", $params, true);
    	//else
    	echo $controller->renderPartial("social", $params, true);
    }
}