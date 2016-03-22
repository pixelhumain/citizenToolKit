<?php

class ConnectAction extends CAction
{
	 /**
	 * TODO Clement : La PHPDOC
	 */
    public function run() {
	    assert('!empty($_POST["childType"]); //The child type is mandatory');
	    assert('!empty($_POST["parentId"]); //The parent id is mandatory');
	    assert('!empty($_POST["parentType"]); //The parent type is mandatory');

	    $result = array("result"=>false, "msg"=>Yii::t("common", "Incorrect request"));
		
		if ( ! Person::logguedAndValid() ) {
			return array("result"=>false, "msg"=>Yii::t("common", "You are not loggued or do not have acces to this feature "));
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
    	$isConnectingAdmin = @$_POST["connectType"];
    	
    	if ($isConnectingAdmin=="admin"){
	    	$isConnectingAdmin=true;
    	} else {
	    	$isConnectingAdmin=false;
    	}
    				
		$result = Link::connectParentToChild($parentId, $parentType, $child, $isConnectingAdmin, Yii::app()->session["userId"], $roles);

		Rest::json($result);
    }

}