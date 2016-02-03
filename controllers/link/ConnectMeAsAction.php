<?php

class ConnectMeAsAction extends CAction
{
	 /**
	 * Declare somebody as admin of an organization
	 * @param type $id : is the mongoId of the organisation and the id of person that ask to become an admin
	 */
    public function run() {
	    $roles="";
	    $userId = $_POST["userId"];
	    $userType = $_POST["userType"];
    	$parentId = $_POST["parentId"];
    	$parentType = $_POST["parentType"];
    	$connectType = $_POST["connectType"];
    	if ($connectType=="admin"){
	    	$connectType=true;
    	}
    	else{
	    	$connectType=false;
    	}
		if(@$_POST["adminAction"]){
			if($_POST["adminAction"]=="true")
	    		$actionAdmin = true;
	    	else
	    		$actionAdmin = false;
    	}
    	else
    		$actionAdmin=false;
    		
    	$result = array("result"=>false, "msg"=>Yii::t("common", "Incorrect request"));
		
		if (! Person::logguedAndValid()) {
			return $result;
		}
		
		$result = Link::addPersonAs($parentId, $parentType, $userId, $userType, $connectType, Yii::app()->session["userId"], $actionAdmin,$roles);
		Rest::json($result);
    }

}