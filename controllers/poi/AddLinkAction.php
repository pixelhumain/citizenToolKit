<?php

class AddLinkAction extends CAction {
    
    public function run() {
	    //assert('!empty($_POST["childType"])'); //The child type is mandatory');
	    assert('!empty($_POST["parentId"])'); //The parent id is mandatory');
	    assert('!empty($_POST["parentType"])'); //The parent type is mandatory');

	    $result = array("result"=>false, "msg"=>Yii::t("common", "Incorrect request"));
		
		if ( ! Person::logguedAndValid() ) {
			return array("result"=>false, "msg"=>Yii::t("common", "You are not loggued or do not have acces to this feature "));
		}
	
		$parentId = $_POST["parentId"];
    	$parentType = $_POST["parentType"];
    	//$isConnectingAdmin = @$_POST["connectType"];
		$newMembers = array();
		$msg=false;
		$finalResult = false;
		if(@$_POST["childs"]){
			foreach($_POST["childs"] as $key => $contact){
				if(@$contact["childId"] != $parentId ){
					$roles="";
				    $child = array(
						"childId" => @$contact["childId"],
				    	"childType" => @$contact["childType"] == "people" ? "citoyens" : @$contact["childType"],
				    	"childName" => @$contact["childName"],
			            "childEmail" => @$contact["childEmail"],
				    );
			    	$set=array($_POST["connectType"].".".$child["childId"]=>array("type"=>$child["childType"]));    	
			    	PHDB::update(Poi::COLLECTION,array("_id"=>new MongoId($parentId)),array('$set'=>$set));
				}
		 	}
		}else{
			$child=array("name" => $_POST["childName"], "email"=>$_POST["childEmail"],"tags"=>array($_POST["connectType"]),"type"=>$_POST["organizationType"],'invitedBy'=>Yii::app()->session["userId"]);

			$res = Organization::createAndInvite($child);
			if ($res["result"]) {
                $child["childId"]=$res["id"];
				$child["childType"]=Organization::COLLECTION;
			    $set=array($_POST["connectType"].".".$child["childId"]=>array("type"=>$child["childType"])); 	
			    PHDB::update(Poi::COLLECTION,array("_id"=>new MongoId($parentId)),array('$set'=>$set));
            } else 
               return $res;
		}
	 	$result = array("result"=>true, "msg" => $msg,"newMembers" => $newMembers);
		
		Rest::json($result);
    }
}