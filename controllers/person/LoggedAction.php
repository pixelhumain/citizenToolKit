<?php
class LoggedAction extends CAction
{
    public function run()
    {
    	$res = array("userId"=>Yii::app()->session['userId']);
    	if( isset(Yii::app()->session['userId'])){
    		$me = Person::getById(Yii::app()->session['userId']);
            //Yii::app()->request->cookies->clear();
            //Person::removeCookie(@$me);
            if(!empty($me["address"]["codeInsee"]))
                $address=$me["address"];
    		Person::updateCookieCommunexion(Yii::app()->session['userId'], @$address);
    		$res["profilThumbImageUrl"] = $me['profilThumbImageUrl'];
    	}
        Rest::json($res);
    }
}