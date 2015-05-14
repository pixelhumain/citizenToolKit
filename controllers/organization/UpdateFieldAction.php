<?php

class UpdateFieldAction extends CAction
{
	/**
	* Update an information field for an organization
	*/
    public function run() {
		$organizationId = "";
		$res = array("result"=>false, "msg"=>"Something went wrong");
		if (!empty($_POST["pk"])) {
			$organizationId = $_POST["pk"];
		} else if (!empty($_POST["id"])) {
			$organizationId = $_POST["id"];
		}

		if ($organizationId != "") {
			if (! empty($_POST["name"]) && ! empty($_POST["value"])) {
				$organizationFieldName = $_POST["name"];
				$organizationFieldValue = $_POST["value"];
				try {
					Organization::updateOrganizationField($organizationId, $organizationFieldName, $organizationFieldValue, Yii::app()->session["userId"] );
					$res = array("result"=>true, "msg"=>"The organization has been updated", $organizationFieldName=>$organizationFieldValue);
				} catch (CTKException $e) {
					$res = array("result"=>false, "msg"=>$e->getMessage(), $organizationFieldName=>$organizationFieldValue);
				}
			}
		} 
		Rest::json($res);
    }
}