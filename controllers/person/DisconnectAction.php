<?php
/**
 * disconnect 2 people together 
 */
class DisconnectAction extends CTKAction {
    
    public function run($id,$type, $ownerLink, $targetLink = null) {
		
		if (! $this->userLogguedAndValid()) {
        	return Rest::json(array("result" => false, "msg" => "The current user is not valid : please login."));
        }
        return Rest::json(Link::disconnectPerson(Yii::app()->session['userId'], Person::COLLECTION, $id, $type, $ownerLink,$targetLink));
    }
}