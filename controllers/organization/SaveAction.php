<?php

class SaveAction extends CAction
{
    /**
	* Save a new organization with the minimal information
	* @return an array with result and message json encoded
	*/
    public function run() {
		$controller=$this->getController();
		// Retrieve data from form
		$newOrganization = Organization::newOrganizationFromPost($_POST);
		try{
			if ( Person::logguedAndValid() ) {
				//Save the organization
				Rest::json(Organization::insert($newOrganization, Yii::app()->session["userId"]));
			} else {
				return Rest::json(array("result"=>false, "msg"=>"You are not loggued with a valid user !"));
			}
		} catch (CTKException $e) {
			return Rest::json(array("result"=>false, "msg"=>$e->getMessage()));
		}
    }
}