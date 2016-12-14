<?php

class SaveAction extends CAction {
/**
* Dashboard Organization
*/
    public function run() { 

    	$res = array( "result" => false, "msg" => Yii::t("common","Login First") );
        if(isset(Yii::app()->session["userId"]))
        {
            $res = Element::save($_POST);
        }
        	
        echo json_encode( $res );  	
    }
}

?>