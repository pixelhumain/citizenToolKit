<?php
/**
* retreive dynamically 
*/
class PowerAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        
        $params = array(//"medias" => $medias,
    					"subdomain" => "power",
                        "subdomainName" => "power",
                        "icon" => "hand-rock-o", 
                        "mainTitle" => "Un bien <span class='letter-green'>CO</span>mmun dédié à l'intelligence <span class='letter-green'>CO</span>llective",
                        "placeholderMainSearch" => "rechercher une proposition");

    	if(@$_POST['renderPartial'] == true)
    	echo $controller->renderPartial("powerStream", $params, true);
    	else
    	echo $controller->renderPartial("power", $params, true);
    }
}