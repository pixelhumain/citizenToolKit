<?php
class LogoutAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
        Person::clearUserSessionData();

        $url = "co2#";
        
        if(@$_GET["network"])
        	$url="/network/default/index?src=".$_GET["network"];
        
    	$controller->redirect(Yii::app()->createUrl($url) );
    }
}