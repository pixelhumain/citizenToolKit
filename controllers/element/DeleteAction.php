<?php

class DeleteAction extends CAction {
    
    public function run($type, $id) {
    	
        $controller=$this->getController();
        $reason = @$_POST["reason"];

        $controller=$this->getController();
        
        if ( ! Authorisation::canDeleteElement($id, $type, Yii::app()->session["userId"])) {
            Rest::json(array( "result" => false, "msg" => "You are not allowed to delete this element !" ));
            return;
        }

        if ($type == Person::COLLECTION || $type == Person::CONTROLLER) {
            $res = Person::deletePerson($id, Yii::app()->session["userId"]);
        } else if ($type == Organization::COLLECTION || $type == Organization::CONTROLLER) {
            $res = Element::deleteElement($type, $id, $reason, Yii::app()->session["userId"]);
        //TODO SABR - Move Delete POI to DeleteElement
        } else if ($type == POI::COLLECTION || $type == POI::CONTROLLER) {
        	$res = Element::delete($type, $id, Yii::app()->session["userId"]);
        } else {
            Rest::json(array( "result" => false, "msg" => "For now you can only delete Person, organization or POI" ));
            return;   
        }

        Rest::json($res);
    }
}