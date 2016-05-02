<?php
/**
* upon Registration a email is send to the new user's email 
* he must click it to activate his account
* This is cleared by removing the tobeactivated field in the pixelactifs collection
*/
class ActivateAction extends CAction
{
    public function run($user, $validationKey, $invitedBy=null) {
    	assert('$user != ""; //The user is mandatory');
    	assert('$validationKey != ""; //The validation Key is mandatory');

    	$controller=$this->getController();
    	$params = array();

        //remove logued user to prevent incoherent action
        Person::clearUserSessionData();
    	
        //Validate email
    	$res = Person::validateEmailAccount($user, $validationKey);
    	if ($res["result"]) {
    		$params["userValidated"] = 1;
    		$params["email"] = $res["account"]["email"];
    	} else {
    		$params["msg"] = $res["msg"];
    	}
        
    	//InvitedBy
    
    	if (@$res["account"]["tobeactivated"] == true) {
	    	//echo true;
            $params["tobeactivated"] = true;
            $params["pendingUserId"] = $user;
            if (!empty($invitedBy)) {
        		Yii::app()->session["invitor"] = Person::getSimpleUserById($invitedBy);
        	} else {
                Yii::app()->session["invitor"] = "";
            }
        }
		//print_r($params);
        //var_dump($params);
        $params = implode('&', array_map(function ($v, $k) { return $k . '=' . $v; }, 
                                            $params, 
                                            array_keys($params)
                                        ));
	    $controller->redirect(Yii::app()->createUrl("/".$controller->module->id)."?".$params."#panel.box-login");
    }

    
}