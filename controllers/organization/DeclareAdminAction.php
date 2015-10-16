<?php

class DeclareAdminAction extends CAction
{
	 /**
	 * Declare somebody as admin of an organization
	 * @param type $id : is the mongoId of the organisation and the id of person that ask to become an admin
	 */
    public function run($idOrganization, $idPerson) {
    	$result = array("result"=>false, "msg"=>Yii::t("common", "Incorrect request"));
		
		if (! Person::logguedAndValid()) {
			return $result;
		}

		$result = Organization::addPersonAsAdmin($idOrganization, $idPerson);

		Rest::json($result);
    }

}