<?php
/**
* When a user is invited and click on the link on his invitation email
* Verify email key and redirect to sign in in order to register this user
*/
class ValidateInvitationAction extends CAction {

    public function run($user, $validationKey) {
        assert('$user != ""; //The user is mandatory');
        assert('$validationKey != ""; //The validation Key is mandatory');

        $controller=$this->getController();
        $params = array();
        
        //Validate validation key
        $error = "somethingWrong";
        $res = Person::isRightValidationKey($user, $validationKey);
        
        if ($res==true) {
            //Get the invited user in the db
            $account = Person::getById($user, false);
            $error="";

            if(!empty($account)) {
                if(!empty($account["pending"])){
                    $params["email"] = $account["email"];
                    $params["name"] = $account["name"];
                    //$params["userValidated"] = 1;
                    $params["pendingUserId"] = $user;
                    $invitedBy = @$account["invitedBy"];
                    if (!empty($invitedBy)) 
                       $params["invitor"] = $invitedBy;
                    else
                        //Something went wrong ! Impossible to retrieve your invitor.
                        $error = "unknwonInvitor";
                    $msg = "";
                } else {
                    //Your account already exists on the plateform : please try to login
                    $error = "accountAlreadyExists";
                    $params["error"] = $error;
                    $controller->redirect(Yii::app()->createUrl("/".$controller->module->id)."?".$this->arrayToUrlParams($params)."#panel.box-login");
                }
            }
        }
        
        $params["error"] = $error;
        $controller->redirect(Yii::app()->createUrl("/".$controller->module->id)."?".$this->arrayToUrlParams($params)."#panel.box-register");
    }

    private function arrayToUrlParams($params) {
        $params = implode('&', array_map(function ($v, $k) { return $k . '=' . urlencode($v); }, 
                                            $params, 
                                            array_keys($params)
                                        ));
        return $params;

    }
}