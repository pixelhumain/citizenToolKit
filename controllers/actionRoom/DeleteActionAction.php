<?php

//Delete an action
class DeleteActionAction extends CAction {
    
    public function run($id) {
        //Check if connected
        if( ! Person::logguedAndValid()) {
            $res = array("result"=>false, "msg"=>"You must be loggued to delete an action");
        } else {
            $res = Actions::deleteAction( $id,  Yii::app()->session["userId"]);
        }

        Rest::json( $res );
        Yii::app()->end();
    }
    
}