<?php

class StopDeleteAction extends CAction {
    
    public function run($type, $id) {
    	
        $controller=$this->getController();
        $reason = @$_POST["reason"];

        $controller=$this->getController();
        
        if (! Authorisation::isElementAdmin($id, $type, Yii::app()->session["userId"])) {
            Rest::json(array( "result" => false, "msg" => Yii::t('common',"You are not allowed to delete this element !") ));
            return;
        } else if (! Element::isElementStatusDeletePending($type, $id)) {
            Rest::json(array( "result" => false, "msg" => Yii::t('common',"This element is not pending : impossible to stop the process.") ));
            return;
        }

        if ( $type == Organization::COLLECTION || $type == Organization::CONTROLLER ||
                    $type == Project::COLLECTION || $type == Project::CONTROLLER ||
                    $type == Event::COLLECTION || $type == Event::CONTROLLER ) {
            $res = Element::stopToDelete($type, $id, Yii::app()->session["userId"]);
        } else {
            Rest::json(array( "result" => false, "msg" => Yii::t('common',"Impossible to stop deleting that kind of element".$type) ));
            return;   
        }

        Rest::json($res);
    }
}