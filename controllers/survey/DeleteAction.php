<?php

//Delete a Survey
class DeleteAction extends CAction {
    
    public function run($id) {
        //Check if connected
        if( ! Person::logguedAndValid()) {
            $res = array("result"=>false, "msg"=>"You must be loggued to delete a room");
        } else {
            $res = Survey::deleteEntry( $id,  Yii::app()->session["userId"]);
        }

        Rest::json( $res );
        Yii::app()->end();
    }
    
}