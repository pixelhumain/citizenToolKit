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
        } else if ( $type == Organization::COLLECTION || $type == Organization::CONTROLLER ||
                    $type == Project::COLLECTION || $type == Project::CONTROLLER ||
                    $type == Event::COLLECTION || $type == Event::CONTROLLER ) {
            $res = Element::askToDelete($type, $id, $reason, Yii::app()->session["userId"]);
        //TODO SABR - Move Delete POI to DeleteElement
        } else if ($type == Poi::COLLECTION) {
        	$res = Poi::delete($id, Yii::app()->session["userId"]);
        } else {
            Rest::json(array( "result" => false, "msg" => "Impossible to delete that kind of element ".$type ));
            return;   
        }

        Rest::json($res);
    }
}