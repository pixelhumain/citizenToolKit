<?php

class DeclareMeAdminAction extends CAction
{
	 /**
	 * Declare somebody as admin of an organization
	 * @param type $id : is the mongoId of the organisation and the id of person that ask to become an admin
	 */
    public function run() {
    	$idOrganization = $_POST["idOrganization"];
    	$idPerson = $_POST["idPerson"];
    	$result = array("result"=>false, "msg"=>Yii::t("common", "Incorrect request"));
		
		if (! Person::logguedAndValid()) {
			return $result;
		}
		$result = Organization::addPersonAsAdmin($idOrganization, $idPerson, Yii::app()->session["userId"]);
		$result["parent"]=Organization::getById($idOrganization);
		Rest::json($result);
    }

}