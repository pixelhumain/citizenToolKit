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
		$postalCode = (!empty($_POST['cp'])) ? $_POST['cp'] : "";
		$geoPosLatitude = (!empty($_POST['geoPosLatitude'])) ? $_POST['geoPosLatitude'] : "";
		$geoPosLongitude = (!empty($_POST['geoPosLongitude'])) ? $_POST['geoPosLongitude'] : "";
		$pwd = (!empty($_POST['pwd'])) ? $_POST['pwd'] : "";
		$city = (!empty($_POST['city'])) ? $_POST['city'] : "";
		$pendingUserId = (!empty($_POST['pendingUserId'])) ? $_POST['pendingUserId'] : "";

		//Get the person data
		$newPerson = array(
			'name'=> $name,
			'username'=> $username,
			'postalCode'=> $postalCode, //TODO : move to address node
			'geoPosLatitude'=> $geoPosLatitude,
			'geoPosLongitude'=> $geoPosLongitude,
			'pwd'=>$pwd,
			'city'=>$city);

		//The user already exist in the db : the data should be updated
		if ($pendingUserId != "") {
			$res = Person::updateMinimalData($pendingUserId, $newPerson);
			if (! $res["result"]) {
				Rest::json($res);
				exit;
			} 

		} else {
			try {
				$newPerson['email'] = $email;
				$res = Person::insert($newPerson, false);
				$newPerson["_id"]=$res["id"];
			} catch (CTKException $e) {
				$res = array("result" => false, "msg"=>$e->getMessage());
				Rest::json($res);
				exit;
			}
		}

		//Try to login with the user
		$res = Person::login($email,$pwd,false);
		if ($res["result"]) {
			$controller->redirect(array("person/login"));
		} else if ($res["msg"] == "betaTestNotOpen") {
			$newPerson["_id"] = $pendingUserId;
			$newPerson['email'] = $email;
			//TODO
			//send communecter overview mail
			//Mail::communecterOverview($newPerson);
			$res = array("result"=>true, "msg"=> Yii::t("login","You are now communnected !")." ".Yii::t("login","Our developpers are fighting to open soon ! Check your mail that will happen soon !"), "id"=>$pendingUserId); 
		} else if ($res["msg"] == "notValidatedEmail") {
			$newPerson["_id"] = $pendingUserId;
			$newPerson['email'] = $email;

			//send validation mail if the user is not validated
			Mail::validatePerson($newPerson);
			$res = array("result"=>true, "msg"=> Yii::t("login","You are now communnected !")." ".Yii::t("login","Check your mailbox you'll receive soon a mail to validate your email address."), "id"=>$pendingUserId); 
		}

		Rest::json($res);
		exit;
    }
}