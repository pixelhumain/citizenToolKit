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
            $params = ( $_SERVER['REQUEST_METHOD'] == 'PUT' ) ? parse_str( file_get_contents("php://input"), $params ) : $_POST ;  

            unset($params["startDateInput"]);
            unset($params["endDateInput"]);

            $res = Element::save($params);
            $res['resquest'] = $_SERVER['REQUEST_METHOD'];
        }

        echo Rest::json( $res );  
    }
}

?>