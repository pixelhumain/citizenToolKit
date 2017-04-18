<?php
class DeleteAction extends CAction {
    public function run($id= null) {
    	//Check if connected
        if( ! Person::logguedAndValid()) {
            $res = array("result"=>false, "msg"=>"You must be loggued to delete a comment");
        } else {
            $res = Comment::delete($id, Yii::app()->session["userId"]);
        }
        return Rest::json( $res );
    }
}