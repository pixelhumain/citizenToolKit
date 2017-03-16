<?php

class SaveAction extends CAction {
/**
* Save an Element
* Element can be : Organization, Event, Projets
* See databinding of different types of element for the format of the data
*/

    public function run() { 

    	$res = array( "result" => false, "error"=>"401", "msg" => Yii::t("common","Login First") );
        
        //possibilité d'enregistrer des URL sur Kgougle when not login !?
        if(isset(Yii::app()->session["userId"]) || $_POST["collection"] == "url") { 
            //SBAR - temporary Workaround
        //====== Modified 13/01/2017
            $toSave = array();

            if ( $_SERVER['REQUEST_METHOD'] == 'PUT' || $_SERVER['REQUEST_METHOD'] == 'POST') {
                $headers = getallheaders();

                if ( array_key_exists('X-SmartCitizenData', $headers) 
                    && array_key_exists('X-SmartCitizenMacADDR', $headers)) {
                    
                    $toSave = Thing::fillSmartCitizenData($headers);

                } elseif ( $_SERVER['REQUEST_METHOD'] == 'POST') {
                    $toSave = $_POST;
                    unset($toSave["startDateInput"]);
                    unset($toSave["endDateInput"]);
                }
                $res = Element::save($toSave);
        //====== 
                $res['resquest'] = $_SERVER['REQUEST_METHOD'];
            }
        }
        echo Rest::json( $res ); 
    }
}

?>