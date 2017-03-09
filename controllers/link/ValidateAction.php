<?php
/**
 * Action used to validate a link between a child and a parent
 * Ex : 
 * - member need to be validate by an admin of the organization/projet
 * - new admin demand must be validated by a current admin of the organization/projet
 * 
 */
class ValidateAction extends CAction {
    public function run() {
		assert('!empty($_POST["childId"]); //The child id is mandatory');
		assert('!empty($_POST["childType"]); //The child type is mandatory');
	    assert('!empty($_POST["parentId"]); //The parent id is mandatory');
	    assert('!empty($_POST["parentType"]); //The parent type is mandatory');
	    assert('!empty($_POST["linkOption"]); //The link option is mandatory');

		$res = array( "result" => false , "msg" => Yii::t("common","Something went wrong!" ));
		
		$linkOption = $_POST["linkOption"];
		if ($linkOption != Link::IS_ADMIN_PENDING && $linkOption != Link::TO_BE_VALIDATED && $linkOption != Link::IS_INVITING ) {
			return array( "result" => false , "msg" => "Unknown link option : ".$linkOption);
		}

		$res = Link::validateLink($_POST["parentId"], $_POST["parentType"], $_POST["childId"], 
									$_POST["childType"], $linkOption, Yii::app()->session["userId"]);
		
		return Rest::json($res);
	}
}