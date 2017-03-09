<?php

class SaveAction extends CAction {
/**
* Save an Element
* Element can be : Organization, Event, Projets
* See databinding of different types of element for the format of the data
*/

    public function run() { 

    	$res = array( "result" => false, "error"=>"401", "msg" => Yii::t("common","Login First") );
        
        if(isset(Yii::app()->session["userId"])) {
            //SBAR - temporary Workaround
        //====== Modified 13/01/2017
            $toSave = array();

            if ( $_SERVER['REQUEST_METHOD'] == 'PUT' || $_SERVER['REQUEST_METHOD'] == 'POST') {
                $headers = getallheaders();

                if ( array_key_exists('X-SmartCitizenData', $headers) 
                    && array_key_exists('X-SmartCitizenMacADDR', $headers)) {

                    $response = Thing::fillAndSaveSmartCitizenData($headers); 
                    //print_r($response);
                    $res=array();
                    //Element::Save() directement appeler dans la fonction, pour accepter le batch.
                    //Retourne 

                } elseif ( $_SERVER['REQUEST_METHOD'] == 'POST') {
                    $toSave = $_POST;
                    unset($toSave["startDateInput"]);
                    unset($toSave["endDateInput"]);
                    $res['resquest'] = $_SERVER['REQUEST_METHOD'];
                    $res = Element::save($toSave);
                }
            }
        }
        echo Rest::json( $res ); 
    }
}

?>