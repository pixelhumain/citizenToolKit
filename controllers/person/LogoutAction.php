<?php
class LogoutAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
        Person::clearUserSessionData();

        $url = "co2#";
        //print_r(@Yii::app()->session['custom']);
        //print_r(@Yii::app()->session);
        //print_r(@Yii::app()->session["user"]);
        if(@$_GET["network"])
        	$url="/network/default/index?src=".$_GET["network"];
        if(@Yii::app()->session['custom'] && @Yii::app()->session['custom']["url"]){
        	$url=Yii::app()->session['custom']["url"];
        }
    	//echo $url;
    	$controller->redirect(Yii::app()->createUrl($url) );
    }
}