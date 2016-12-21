<?php
class LogoutAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
        Person::clearUserSessionData();
        $network="";
        if(@$_GET["network"])
        	$network="?network=".$_GET["network"];
    	$controller->redirect( Yii::app()->createUrl($controller->module->id.$network) );
    }
}