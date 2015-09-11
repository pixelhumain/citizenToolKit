<?php
class InviteSVAction extends CTKAction
{
    public function run()
    {
        $controller=$this->getController();

        $params = array();
        $params['currentUser'] = Person::getById($this->currentUserId);
        
        if( isset($_GET["isNotSV"])) 
            $params["isNotSV"] = true;
    	
    	if(Yii::app()->request->isAjaxRequest)
    		$controller->renderPartial("inviteSV", $params);
    }
}