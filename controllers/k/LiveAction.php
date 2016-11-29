<?php
/**
* retreive dynamically 
*/
class LiveAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        
    	$params = array("subdomain" => "live",
                        "mainTitle" => "Le réseau social des Cagous",
                        "placeholderMainSearch" => "rechercher dans l'actualité ...");

    	echo $controller->renderPartial("live", $params, true);
    }
}