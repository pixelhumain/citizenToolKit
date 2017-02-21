<?php
/**
* Change the password of the current user
*/
class ChangePasswordAction extends CAction {
    public function run() {

    	$controller=$this->getController();
        
        if (isset($_GET["mode"])) {
            $mode = @$_GET["mode"];
            $userId = @$_GET["id"];
        } else {
            $userId = @$_POST["id"];
            $mode = @$_POST["mode"];
        }

        if (! Person::logguedAndValid() || Yii::app()->session["userId"] != $userId) {
            Rest::json(array("result" => false, "msg" => "You can not modify a password of this user !"));
            return;
        }

        if ($mode == "initSV") {
            echo $controller->renderPartial("changePasswordSV", array(), true);
        } else if ($mode == "changePassword") {
            $res = Person::changePassword(@$_POST["oldPassword"], @$_POST["newPassword"], $userId);
            Rest::json($res);
        } else {
    		Rest::json(array("result" => false, "msg" => "Unknown mode !"));
    	}
    }
}