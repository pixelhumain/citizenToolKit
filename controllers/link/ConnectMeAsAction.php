<?php

class ConnectMeAsAction extends CAction
{
	 /**
	 * TODO clement : La PHPDOC
	 */
    public function run() {
	    $result = array("result"=>false, "msg"=>Yii::t("common", "Incorrect request"));
		
		if (! Person::logguedAndValid()) {
			return $result;
		}

	    $roles="";
	    $userId = $_POST["userId"];
	    $userType = $_POST["userType"];
    	$parentId = $_POST["parentId"];
    	$parentType = $_POST["parentType"];
    	$connectType = $_POST["connectType"];
    	
    	if ($connectType=="admin"){
	    	$connectType=true;
    	} else {
	    	$connectType=false;
    	}
		
		$actionAdmin = false;
		if(@$_POST["adminAction"] == "true")
	    	$actionAdmin = true;
    				
		$result = Link::addPersonAs($parentId, $parentType, $userId, $userType, $connectType, Yii::app()->session["userId"], $actionAdmin,$roles);
		Rest::json($result);
    }

}