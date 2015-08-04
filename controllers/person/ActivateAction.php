<?php
/**
* upon Registration a email is send to the new user's email 
* he must click it to activate his account
* This is cleared by removing the tobeactivated field in the pixelactifs collection
*/
class ActivateAction extends CAction
{
    public function run($user) {
    	$controller=$this->getController();
	    $account = Person::getById($user);
	    //TODO : move code below to the model Person
	    $res = array("result"=>false);
	    if($account)
	    {
	        //remove tobeactivated attribute on account
	        PHDB::update(	Person::COLLECTION,
                        	array("_id"=>new MongoId($user)), 
                            array('$unset' => array("tobeactivated"=>""))
                        );
	        $res = array("result"=>true);
	        /*Notification::saveNotification(array("type"=>NotificationType::NOTIFICATION_ACTIVATED,
	                      "user"=>$account["_id"]));*/
	    }
	    //TODO : add notification to the cities,region,departement info panel
	    //TODO : redirect to monPH page , inciter le rezotage local

	    $controller->redirect(Yii::app()->homeUrl);
    }
}