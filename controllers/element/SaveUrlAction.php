<?php

class SaveUrlAction extends CAction {
/**
* Dashboard Organization
*/
    public function run() { 

        if(Yii::app()->request->isAjaxRequest && isset(Yii::app()->session["userId"]))
        {
            $res = Element::saveUrl($_POST);
            echo json_encode( $res );  
        }
    }
}

?>