<?php

class DeleteAction extends CAction {
    
    public function run($type, $id) {
    	
        $controller=$this->getController();
        $reason = @$_POST["reason"];

        //$controller=$this->getController();
        //echo "here"; exit ;
        
        if ( ! Authorisation::canDeleteElement($id, $type, Yii::app()->session["userId"])) {
            Rest::json( array( "result" => false, "msg" => "You are not allowed to delete this element !" ));
            return;
        }

        $elemTypes = array( 
            Organization::COLLECTION, Organization::CONTROLLER, 
            Project::COLLECTION, Project::CONTROLLER,
            Event::COLLECTION, Event::CONTROLLER,
            Proposal::COLLECTION, Proposal::CONTROLLER,
            Action::COLLECTION, Action::CONTROLLER,
            Room::COLLECTION, Room::CONTROLLER);

        if ($type == Person::COLLECTION || $type == Person::CONTROLLER) {
            $res = Person::deletePerson($id, Yii::app()->session["userId"]);
        } else if ( in_array( $type,$elemTypes )  ) {
            $res = Element::askToDelete($type, $id, $reason, Yii::app()->session["userId"],$elemTypes);
        } else if ( in_array( $type, array( Ressource::COLLECTION,Classified::COLLECTION, Poi::COLLECTION ) ) ) {
            $res = Element::deleteSimple($id,$type, Yii::app()->session["userId"]);
        } else {
            Rest::json(array( "result" => false, "msg" => "Impossible to delete that kind of element ".$type ));
            return;   
        }

        Rest::json($res);
    }
}