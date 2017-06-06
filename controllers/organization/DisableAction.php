<?php

class DisableAction extends CAction {

    public function run($id) {
    	$result = array("result"=>false, "msg"=>Yii::t("common", "Incorrect request"));
		if(!empty(Yii::app()->session["userId"]) && Role::isSuperAdmin(Role::getRolesUserId(Yii::app()->session["userId"]))) {
			$organization = Organization::getById( $id );
			
			if(!empty($organization)){
				PHDB::update( Organization::COLLECTION, array("_id"=>new MongoId($id)) , 
														array('$set' => array("disabled"=> true)));
				$organization["id"] = $id;
				Notification::actionOnPerson( ActStr::VERB_CLOSE, ActStr::ICON_CLOSE, $organization, array("type"=>Organization::COLLECTION, "id"=> $id, "name"=>$organization["name"]) ) ;
				$result = array("result"=>true,"msg" => Yii::t("organization", "Organization disabled !") );
			}
		}
		Rest::json($result);
    }

}