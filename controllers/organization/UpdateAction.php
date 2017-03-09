<?php

class UpdateAction extends CAction
{
	/**
	* Update an information field for an organization
	*/
    public function run() {
		assert('!empty($_POST["organizationId"]) != ""; //The organization Id is mandatory');
		if ( ! Person::logguedAndValid() ) {
			return Rest::json(array("result"=>false, "msg"=>"You are not loggued with a valid user !"));
		}

		$organizationId = $_POST["organizationId"];
		$organization = Organization::newOrganizationFromPost($_POST);

		$res = array("result"=>false, "msg"=>Yii::t("common", "Something went wrong!"));
		try {
			$res = Organization::update($organizationId, $organization, Yii::app()->session["userId"]);
		} catch (CTKException $e) {
			$res = array("result"=>false, "msg"=>$e->getMessage());
		}

		Rest::json($res);
    }
}