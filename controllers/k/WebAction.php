<?php
/**
* retreive dynamically 
*/
class WebAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        
    	$params = array("subdomain" => "web", 
                        "mainTitle" => "Le moteur de recherche des Cagous",
                        "placeholderMainSearch" => "rechercher sur le web calédonien ...");

    	echo $controller->renderPartial("web", $params, true);
    }
}