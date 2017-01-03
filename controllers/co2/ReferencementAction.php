<?php
/**
* retreive dynamically 
*/
class ReferencementAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        
        //$query = array("country"=>"NC");
    	$cities = CO2::getCitiesNewCaledonia(); //PHDB::find(City::COLLECTION, $query);

        CO2Stat::incNbLoad("co2-referencement");

    	$params = array("subdomain" => "referencement",
                        "mainTitle" => "Référencer votre site Calédonien",
                        "placeholderMainSearch" => "",
                        "cities" => $cities);

    	echo $controller->renderPartial("referencement", $params, true);
    }
}