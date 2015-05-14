<?php

class SaveNewAction extends CAction
{
    /**
	* Save a new organization with the minimal information
	* @return an array with result and message json encoded
	*/
    public function run() {
		$controller=$this->getController();
		// Retrieve data from form
		$newOrganization = Organization::newOrganizationFromPost($_POST);
		try {
			//Save the organization
			Rest::json(Organization::insert($newOrganization, Yii::app()->session["userId"]));
		} catch (CTKException $e) {
			return Rest::json(array("result"=>false, "msg"=>$e->getMessage()));
		}
    }
}