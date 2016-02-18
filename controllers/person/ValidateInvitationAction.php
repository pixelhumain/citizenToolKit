<?php
/**
* When a user is invited and click on the link on his invitation email
* Verify email key and redirect to sign in in order to register this user
*/
class ValidateInvitationAction extends CAction
{
    public function run($user, $validationKey) {
    	assert('$user != ""; //The user is mandatory');
    	assert('$validationKey != ""; //The validation Key is mandatory');

    	$controller=$this->getController();
    	$params = array("person/login");	   
    	//Validate email
    	$res = Person::isRightValidationKey($user, $validationKey);
    	if ($res==true) {
	    	$account = Person::getById($user);
	    	if(!empty($account)){
	    		$params["email"] = $account["email"];
				$params["pendingUserId"] = $user;
	    		$invitedBy= $account["invitedBy"];
	    		if (!empty($invitedBy)) 
	    		   Yii::app()->session["invitor"] = Person::getSimpleUserById($invitedBy);
	    		else
	    			$params["msg"]="You're not invited";	
	    	}
    		//$params["name"] = $res["account"]["name"];
    	} else {
    		$params["msg"] = "Something went wrong !!";
    	}
    	//InvitedBy

	    $controller->redirect($params);
    }

    
}