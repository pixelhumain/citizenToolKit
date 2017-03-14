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
        echo "oui";
        var_dump("iciiiii");
    	$controller->redirect(Yii::app()->createUrl("co2#social".$network) );
    }
}