<?php
/**
* Activate a user throw admin back office
*/
class ActivateUserAction extends CAction {

	public function run($user) {
		$controller=$this->getController();
		
		if (! Authorisation::isUserSuperAdmin(Yii::app()->session["userId"])) {
			$res = array("result" => false, "msg" => "You must be logged as an admin user to do this action !");
		} else {
			$res = Person::validateUser($user,true);
		}
		Rest::json($res);
	}
}
