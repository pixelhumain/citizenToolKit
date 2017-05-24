<?php
class DeleteAction extends CAction {
    
    public function run($id= null) {
    	//Check if connected
        if( ! Person::logguedAndValid()) {
            $res = array("result"=>false, "msg"=>"You must be loggued to delete a comment");
        } else {
        	$res = News::delete($id, Yii::app()->session["userId"], true);
        }

        return Rest::json( $res );
    }
}