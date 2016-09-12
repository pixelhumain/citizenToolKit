<?php

class MultiConnectAction extends CAction
{
	 /**
	 * TODO Clement : La PHPDOC
	 */
    public function run() {

    	assert('!empty($_POST["childs"])'); //The childs are mandatory');
	    //assert('!empty($_POST["childType"])'); //The child type is mandatory');
	    assert('!empty($_POST["parentId"])'); //The parent id is mandatory');
	    assert('!empty($_POST["parentType"])'); //The parent type is mandatory');

	    $result = array("result"=>false, "msg"=>Yii::t("common", "Incorrect request"));
		
		if ( ! Person::logguedAndValid() ) {
			return array("result"=>false, "msg"=>Yii::t("common", "You are not loggued or do not have acces to this feature "));
		}
	
		$parentId = $_POST["parentId"];
    	$parentType = $_POST["parentType"];
    	$isConnectingAdmin = @$_POST["connectType"];

		foreach($_POST["childs"] as $key => $contact){
		    $roles="";
		    $child = array(
				"childId" => @$contact["childId"],
		    	"childType" => @$contact["childType"] == "people" ? "citoyens" : @$contact["childType"],
		    	"childName" => @$contact["childName"],
	            "childEmail" => @$contact["childEmail"]
		    );
	    	    	
	    	$isConnectingAdmin= ($isConnectingAdmin=="admin") ? true : false;
	    	
	    	$res = Link::connectParentToChild($parentId, $parentType, $child, $isConnectingAdmin, Yii::app()->session["userId"], $roles);
		}
		if($res["result"] == true)
		$result = array("result"=>true, "msg" => "Le(s) nouveaux membres ont été ajoutés correctement");
		else $result = $res;
		
		Rest::json($result);
    }

}