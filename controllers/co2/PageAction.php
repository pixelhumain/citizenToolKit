<?php
/**
* retreive dynamically 
*/
class PageAction extends CAction
{
    public function run($type, $id) {
    	$controller=$this->getController();

        CO2Stat::incNbLoad("co2-page");
    	
        $params = array("id" => @$id,
                        "type" => @$type,
                        "subdomain" => "page",
                        "mainTitle" => "Page perso",
                        "placeholderMainSearch" => "");

    	//if(@$_POST['renderPartial'] == true)
    	//echo $controller->renderPartial("liveStream", $params, true);
    	//else
    	echo $controller->renderPartial("page", $params, true);
    }
}