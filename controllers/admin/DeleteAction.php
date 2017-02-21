<?php

class DeleteAction extends CAction {
    
    public function run($type, $id) {
    	
        $controller=$this->getController();
        
        if ( ! Yii::app()->session["userIsAdmin"]) {
            Rest::json(array( "result" => false, "msg" => "You must be a super admin to delete something" ));
            return;
        }

        if ($type != Person::COLLECTION && $type != Person::CONTROLLER) {
            Rest::json(array( "result" => false, "msg" => "For now you can only delete Person" ));
            return;   
        }

        $res = Person::deletePerson($id, Yii::app()->session["userId"]);
        Rest::json($res);
    }
}