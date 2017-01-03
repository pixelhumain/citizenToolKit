<?php
/**
* retreive dynamically 
*/
class FreedomAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        

        CO2Stat::incNbLoad("co2-freedom");
    	
        $params = array( );

    	//if(@$_POST['renderPartial'] == true)
    	//echo $controller->renderPartial("liveStream", $params, true);
    	//else
    	echo $controller->renderPartial("freedom", $params, true);
    }
}