<?php

class FollowAction extends CAction
{
	 /**
	 * TODO Clement : La PHPDOC
	 */
    public function run() {
	    assert('!empty($_POST["childType"]); //The child type is mandatory');
	    assert('!empty($_POST["parentId"]); //The parent id is mandatory');
	    assert('!empty($_POST["parentType"]); //The parent type is mandatory');

	    $result = array("result"=>false, "msg"=>Yii::t("common", "Incorrect request"));
		
		if (! Person::logguedAndValid()) {
			return $result;
		}

	    $roles="";
	    $child = array(
			"childId" => @$_POST["childId"],
	    	"childType" => $_POST["childType"],
	    	"childName" => @$_POST["childName"],
            "childEmail" => @$_POST["childEmail"]
	    );
    	$parentId = $_POST["parentId"];
    	$parentType = $_POST["parentType"];
    	$result = Link::follow($parentId, $parentType, $child, Yii::app()->session["userId"]);
		Rest::json($result);
    }

}