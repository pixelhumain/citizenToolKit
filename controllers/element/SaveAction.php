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
        //========== danzalDev for test =============
            $arrayToSave = array();

            if ( $_SERVER['REQUEST_METHOD'] == 'PUT' || $_SERVER['REQUEST_METHOD'] == 'POST') {
                $headers = getallheaders();

                if ( array_key_exists('X-SmartCitizenData', $headers) && array_key_exists('X-SmartCitizenMacADDR', $headers)) {
                    
                    $data = $headers['X-SmartCitizenData']; 
                    $datapoints = json_decode($data,true);
                
                    $arrayToSave['key']='thing';
                    $arrayToSave['collection']='thing';
                    $arrayToSave['type']='smartCitizen';
                    $arrayToSave['boardId']=$headers['X-SmartCitizenMacADDR'];
                    $arrayToSave = array_merge($arrayToSave, $datapoints);
                    
                    } elseif ( $_SERVER['REQUEST_METHOD'] == 'POST') {
                        $arrayToSave = $_POST;
                        unset($arrayToSave["startDateInput"]);
                        unset($arrayToSave["endDateInput"]);
                    }
            
            $res = Element::save($arrayToSave);
        //========== danzalDev for test =============

            $res['resquest'] = $_SERVER['REQUEST_METHOD'];
            
            }
        
        }

        echo Rest::json( $res ); 
      
    }
}

?>