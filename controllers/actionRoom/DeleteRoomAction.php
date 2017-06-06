<?php
/**
 * @return [json] 
 */
class DeleteRoomAction extends CAction
{
    public function run($id) {
        $res = array();
        
        //Check if connected
        if( ! Person::logguedAndValid()) {
            $res = array("result"=>false, "msg"=>"You must be loggued to delete a room");
        } else {
            $res = ActionRoom::deleteActionRoom($id, Yii::app()->session["userId"]);
        }

        Rest::json($res);  
        Yii::app()->end();
    }
}