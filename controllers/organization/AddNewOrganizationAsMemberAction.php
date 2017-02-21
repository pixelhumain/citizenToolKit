<?php

class AddNewOrganizationAsMemberAction extends CAction
{
	/** TODO CDA -- TO DELETE NON ???
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
			if ($resp && $resp->isSuccess())
			{
				$captcha = true;
				//Yii::app()->session["checkCaptcha"] = true;;
			}
		}
	
		if($captcha){
		//Get the person data
			$newPerson = array(
				'name'=>$_POST['personName'],
				'email'=>$_POST['personEmail'],
				'postalCode'=>$_POST['personPostalCode'],
				'pwd'=>$_POST['password'],
				'city'=>$_POST['personCity']);

			// Retrieve data from form
			try {
				$newOrganization = Organization::newOrganizationFromPost($_POST);
				$res = Organization::createPersonOrganizationAndAddMember($newPerson, $newOrganization, $_POST['parentOrganization']);
				//notify parent Organization 
				$creator = Person::getById(Yii::app()->session['userId']);
				$newOrganization['id'] = $res["id"];
				unset(Yii::app()->session["checkCaptcha"]);
			} catch (CTKException $e) {
				return Rest::json(array("result"=>false, "msg"=>$e->getMessage()));
			} 
	  		return Rest::json(array("result"=>true, "msg"=>Yii::t("organization", "Your organization has been added with success. Check your mail box : you will recieive soon a mail from us.")));
		} else 
	  		return Rest::json( array("result"=>false, "msg"=> Yii::t("organization", "invalid Captcha Test") ) );
    }
}