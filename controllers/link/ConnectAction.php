<?php

class ConnectAction extends CAction
{
	 /**
	 * TODO Clement : La PHPDOC
	 */
    public function run() {
	    $result = array("result"=>false, "msg"=>Yii::t("common", "Incorrect request"));
		
		if (! Person::logguedAndValid()) {
			return $result;
		}

	    $roles="";
	    $childId = @$_POST["childId"];
	    $childType = $_POST["childType"];
    	$parentId = $_POST["parentId"];
    	$parentType = $_POST["parentType"];
    	$connectType = $_POST["connectType"];
    	
    	//The childId can be empty => it's an invitation
    	//Let's create it
    	if (empty($childId)) {
    		$childName = $_POST["childName"];
	    	$childEmail = $_POST["childEmail"];	
    	}
    	
    	if ($connectType=="admin"){
	    	$connectType=true;
    	} else {
	    	$connectType=false;
    	}
		
		$actionAdmin = false;
		if(@$_POST["adminAction"] == "true")
	    	$actionAdmin = true;
    				
		$result = Link::addPersonAs($parentId, $parentType, $childId, $childType, $connectType, Yii::app()->session["userId"], $actionAdmin,$roles);
		Rest::json($result);
    }

}