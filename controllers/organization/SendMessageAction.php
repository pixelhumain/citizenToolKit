<?php

class SendMessageAction extends CAction
{
	/**
	* Update an information field for an organization
	*/
    public function run() {
		//validate Captcha 
		//no need to check Captcha twice
		//$captcha = ( isset( Yii::app()->session["checkCaptcha"] ) && Yii::app()->session["checkCaptcha"] ) ? true : false;
		$captcha = false;
		if( isset($_POST['g-recaptcha-response']) && isset( Yii::app()->params["captcha"] ) )
		{
			Yii::import('recaptcha.ReCaptcha', true);
			Yii::import('recaptcha.RequestMethod', true);
			Yii::import('recaptcha.RequestParameters', true);
			Yii::import('recaptcha.Response', true);
			Yii::import('recaptcha.RequestMethod.Post', true);
			Yii::import('recaptcha.RequestMethod.Socket', true);
			Yii::import('recaptcha.RequestMethod.SocketPost', true);
			$recaptcha = new \ReCaptcha\ReCaptcha( Yii::app()->params["captcha"] );
			$resp = $recaptcha->verify( $_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR'] );  
			if ($resp && $resp->isSuccess()) {
				$captcha = true;
				//Yii::app()->session["checkCaptcha"] = true;;
			}
		}
	
		if($captcha) {
			$params = array(
	            "type" => Cron::TYPE_MAIL,
	            "tpl"=>'newContactByEmail', 
	            "subject" => "Vous avez reçu un nouvel email depuis le site internet",
	            "from" => Yii::app()->params['adminEmail'],
	            "to" => Yii::app()->params['adminEmail'],
	            "tplParams" => array( "name"  => @$_POST["contactName"] ,
	                                  "email" => @$_POST["contactEmail"],
	                                  "subject" => @$_POST["contactSujet"],
	                                  "message" => @$_POST["contactMessage"],
	                                  "logo"  => "/images/logo_granddir_2.png",) 
	        );
			// Send email to organization email
			Mail::send($params);
	  		return Rest::json(array("result"=>true, 
	  			"msg"=>"Mail envoyé avec succès !"));
		} else 
	  		return Rest::json(array("result"=>false, "msg"=>Yii::t("organization", "invalid Captcha Test")));
    }
}