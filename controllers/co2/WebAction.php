<?php
/**
* retreive dynamically 
*/
class WebAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        
    	// $params = array("appName" => "web", 
     //                    "subdomainName" => "web",
     //                    "icon" => "search", 
     //                    "mainTitle" => "Le moteur de recherche <span class='letter-green'>du green-web</span>",
     //                    "placeholderMainSearch" => "rechercher sur le green web  ...");
    	$params = array();
    	echo $controller->renderPartial("web", $params, true);
    }
}