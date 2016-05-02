<?php
class InviteAction extends CTKAction
{
    public function run()
    {
        $controller=$this->getController();

        $params = array();
        $params['currentUser'] = Person::getById($this->currentUserId);
        $params['follows'] = array();
        $params['listFollowsId'] = array();
        $follows = Person::getPersonFollowsByUser($this->currentUserId);
        foreach ($follows as $key => $value) {
            if(!empty($value["email"]) && $value["email"] != "")
                $params['follows'][] = $value["email"];
                $params['listFollowsId'][] = (string)$value["_id"];
        }
        
        if(Yii::app()->request->isAjaxRequest)
    		$controller->renderPartial("invite", $params);
    }
}