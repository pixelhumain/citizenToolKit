<?php

class SaveContactAction extends CAction {
/**
* Dashboard Organization
*/
    public function run() { 

        if(Yii::app()->request->isAjaxRequest && isset(Yii::app()->session["userId"]))
        {
            $res = Element::saveContact($_POST);
            echo json_encode( $res );  
        }
    }
}

?>