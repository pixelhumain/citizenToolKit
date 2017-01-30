<?php
/**
   * Register a new user for the application
   * Data expected in the post : name, email, postalCode and pwd
   * @return Array as json with result => boolean and msg => String
   */
class RegisterAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();

        $name = (!empty($_POST['name'])) ? $_POST['name'] : "";
        $username = (!empty($_POST['username'])) ? $_POST['username'] : "";
		$email = (!empty($_POST['email'])) ? $_POST['email'] : "";
		$pwd = (!empty($_POST['pwd'])) ? $_POST['pwd'] : "";
		$pendingUserId = (!empty($_POST['pendingUserId'])) ? $_POST['pendingUserId'] : "";

		$newPerson = array(
			'name'=> $name,
			'username'=> $username,
			'pwd'=>$pwd
		);

		$inviteCode = (@$_POST['inviteCode']) ? $_POST['inviteCode'] : null;

		if (@$_POST['mode']) 
			$mode = Person::REGISTER_MODE_TWO_STEPS;
		else 
			$mode = Person::REGISTER_MODE_NORMAL;

		// Deprecated but keep it for Rest calls.
		if ($mode == Person::REGISTER_MODE_NORMAL) {
			$newPerson['city'] = @$_POST['city'];
			$newPerson['postalCode'] = @$_POST['cp'];
			$newPerson['geoPosLatitude'] = @$_POST['geoPosLatitude'];
			$newPerson['geoPosLongitude'] = @$_POST['geoPosLongitude'];
		}

		$pendingUserId = Person::getPendingUserByEmail($email);
		//The user already exist in the db (invitation process) : the data should be updated
		if ($pendingUserId != "") {
			$res = Person::updateMinimalData($pendingUserId, $newPerson);
			if (! $res["result"]) {
				Rest::json($res);
				exit;
			} 
		//New user
		} else {
			try {
				$newPerson['email'] = $email;
				$res = Person::insert($newPerson, $mode,$inviteCode);
				$newPerson["_id"]=$res["id"];
			} catch (CTKException $e) {
				$res = array("result" => false, "msg"=>$e->getMessage());
				Rest::json($res);
				exit;
			}
		}

		//Try to login with the user
		$res = Person::login($email,$pwd,true);
		if ($res["result"]) {
			
		} else if ($res["msg"] == "notValidatedEmail") {
			//send validation mail to the user
			$newPerson['email'] = $email;
			$newPerson["inviteCode"] = $inviteCode;
			Mail::validatePerson($newPerson);
			$res = array("result"=>true, "msg"=> Yii::t("login","Congratulation your account is created !")."<br>".Yii::t("login","Last step to enter : we sent you an email, click on the link to validate your mail address."), "id"=>$pendingUserId);
		} else if ($res["msg"] == "betaTestNotOpen") {
			$newPerson["_id"] = $pendingUserId;
			$newPerson['email'] = $email;
			//send betatest information mail
			Mail::betaTestInformation($newPerson);
			$res = array("result"=>true, 
				"msg"=> Yii::t("login","You are now communnected !")."<br>".Yii::t("login","Our developpers are fighting to open soon ! Check your mail that will happen soon !")."<br>".Yii::t("login","If you really want to start testing the platform before, send us an email and we'll consider your demand :)"), 
				"id"=>$pendingUserId); 
		} 
		Rest::json($res);
		exit;
    }
}