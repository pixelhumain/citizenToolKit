<?php

class UpdateAction extends CAction
{
	/**
	* Update an information field for an organization
	*/
    public function run() {
		assert(!empty($_POST["personId"]) != ""); //The person Id is mandatory');
		if ( ! Person::logguedAndValid() ) {
			return Rest::json(array("result"=>false, "msg"=>"You are not loggued with a valid user !"));
		}

		$personId = $_POST["personId"];
		$person = Person::newPersonFromPost($_POST);
		
		$res = array("result"=>false, "msg"=>Yii::t("common", "Something went wrong!"));
		try {
			$res = Person::update($personId, $person, Yii::app()->session["userId"]);
			
		
		} catch (CTKException $e) {
			$res = array("result"=>false, "msg"=>$e->getMessage());
		}

		Rest::json($res);
    }
}