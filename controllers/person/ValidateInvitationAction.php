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
    	$res = Person::isRightValidationKey($user, $validationKey);
    	$msg = "Something went wrong !!";
        
        if ($res==true) {
	    	//Get the invites user in the db
            $account = Person::getById($user);
	    	
            if(!empty($account) && !empty($account["pending"])){
	    		$params["email"] = $account["email"];
                $params["name"] = $account["name"];
	    		//$params["userValidated"] = 1;
				$params["pendingUserId"] = $user;
	    		$invitedBy = $account["invitedBy"];
	    		if (!empty($invitedBy)) 
	    		   Yii::app()->session["invitor"] = Person::getSimpleUserById($invitedBy);
	    		else
	    			$msg = "Something went wrong ! Impossible to retrieve your invitor.";
	    		$msg = "";
	    	}
    	}
    	
        $params["msg"] = $msg;
	    $params = implode('&', array_map(function ($v, $k) { return $k . '=' . urlencode($v); }, 
                                            $params, 
                                            array_keys($params)
                                        ));
        
        $controller->redirect(Yii::app()->createUrl("/".$controller->module->id)."?".$params."#panel.box-register");
    }
}