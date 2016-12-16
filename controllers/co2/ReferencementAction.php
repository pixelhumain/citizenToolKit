<?php
/**
* retreive dynamically 
*/
class ReferencementAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        
        $query = array("country"=>"NC");
    	$cities = PHDB::find(City::COLLECTION, $query);

    	$params = array("subdomain" => "referencement",
                        "mainTitle" => "Référencer votre site Calédonien",
                        "placeholderMainSearch" => "",
                        "cities" => $cities);

    	echo $controller->renderPartial("referencement", $params, true);
    }
}