<?php

class GetContactsAction extends CAction {

    public function run($type, $id) { 
    	$contacts = Element::getContacts($id, $type);
		return Rest::json($contacts);
		Yii::app()->end();
	}
}

?>