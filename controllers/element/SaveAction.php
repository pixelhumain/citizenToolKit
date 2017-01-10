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
            unset($_POST["startDateInput"]);
            unset($_POST["endDateInput"]);

            $res = Element::save($_POST);
        }

        echo Rest::json( $res );  	

    }
}

?>