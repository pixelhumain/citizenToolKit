<?php
/**
* Change the role of a user
*/
class ChangeRoleAction extends CAction
{
    public function run() {
    	$controller=$this->getController();

    	$userId = @$_POST["id"];
    	$action = @$_POST["action"];

    	if (! (Person::logguedAndValid() && Yii::app()->session["userIsAdmin"])) {
    		Rest::json(array("result" => false, "msg" => "You are not super admin : you can not modify this role !"));
    		return;
    	}

		$res = Role::updatePersonRole($action, $userId);
    	Rest::json($res);
    }
}