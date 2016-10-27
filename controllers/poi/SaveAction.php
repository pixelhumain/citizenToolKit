<?php

class SaveAction extends CAction {
/**
* Dashboard Organization
*/
    public function run() { 

        if(Yii::app()->request->isAjaxRequest && isset(Yii::app()->session["userId"]))
        {
            $res = Element::save($_POST);
            echo json_encode( $res );  
        }
    }
}

?>