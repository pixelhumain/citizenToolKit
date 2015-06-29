<?php

//require("../../pixelhumain/ph/vendor/yahoo/Yahoo.inc");
//require_once __DIR__ . "/../../../../pixelhumain/ph/vendor/yahoo/Yahoo.inc";
//require(__DIR__ . '/../../../../pixelhumain/ph/vendor/yahoo/Yahoo.inc');
 



class YahooAction extends CAction
{
	
    public function run()
    {
    	$controller=$this->getController();
    	$controller->render("yahoo");
    }
}
?>