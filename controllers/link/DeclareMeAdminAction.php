<?php

class DeclareMeAdminAction extends CAction
{
	 /**
	 * Declare somebody as admin of an organization
	 * @param type $id : is the mongoId of the organisation and the id of person that ask to become an admin
	 */
    public function run() {
    	$parentId = $_POST["parentId"];
    	$idPerson = $_POST["userId"];
    	$parentType = $_POST["parentType"];
    	$actionAdmin = $_POST["adminAction"];
    	$result = array("result"=>false, "msg"=>Yii::t("common", "Incorrect request"));
		
		if (! Person::logguedAndValid()) {
			return $result;
		}
		
		$result = Link::addPersonAsAdmin($parentId, $parentType, $idPerson, Yii::app()->session["userId"],$actionAdmin);
		Rest::json($result);
    }

}