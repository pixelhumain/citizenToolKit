<?php
class InviteAction extends CTKAction
{
    public function run()
    {
        $controller=$this->getController();

        $params = array();
        $params['currentUser'] = Person::getById($this->currentUserId);
        $params['follows'] = Person::getPersonFollowsByUser($this->currentUserId);
        
        if(Yii::app()->request->isAjaxRequest)
    		$controller->renderPartial("invite", $params);
    }
}