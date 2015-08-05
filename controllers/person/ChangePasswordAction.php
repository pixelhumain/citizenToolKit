<?php
/**
* Change the password of the current user
*/
class ChangePasswordAction extends CAction
{
    public function run() {
    	$controller=$this->getController();

    	$userId = @$_POST["userId"];
    	$mode = @$_POST["mode"];
    	if (! Person::logguedAndValid() || Yii::app()->session["userId"] != $userId) {
    		Rest::json(array("result" => false, "msg" => "You can not modify a password of this user !"));
    		return;
    	}

    	if ($mode == "initSV") {
			Rest::json(array("result"=>true, 
				"content" => $controller->renderPartial("changePasswordSV", array(), true)));
    	} else if ($mode == "changePassword") {
    		$res = Person::changePassword(@$_POST["oldPassword"], @$_POST["newPassword"], $userId);
    		Rest::json($res);
    	} else {
    		Rest::json(array("result" => false, "msg" => "Unknown mode !"));
    	}

    }
}