<?php
class LoggedAction extends CAction
{
    public function run()
    {
    	$res = array("userId"=>Yii::app()->session['userId']);
    	if( isset(Yii::app()->session['userId'])){
    		$me = Person::getById(Yii::app()->session['userId']);
    		Person::updateCookieCommunexion(Yii::app()->session['userId'], @$me["address"]);
    		$res["profilThumbImageUrl"] = $me['profilThumbImageUrl'];
    	}
        Rest::json($res);
    }
}