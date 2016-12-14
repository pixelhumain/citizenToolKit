<?php

class SaveAction extends CAction {
/**
* Dashboard Organization
*/
    public function run() { 

        if(isset(Yii::app()->session["userId"]))
        {
            $res = Element::save($_POST);
        }else
        	array( "result" => false, 
                          "msg" => Yii::t("common","Login First") );
        	
        echo json_encode( $res );  	
    }
}

?>