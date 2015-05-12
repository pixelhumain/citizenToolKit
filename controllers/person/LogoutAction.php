<?php
class LogoutAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
        Person::clearUserSessionData();
    	$controller->redirect(Yii::app()->homeUrl);
    }
}