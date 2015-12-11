<?php
/*
* make sure the organization exists
* only accept person and organization types
* if no id is given means the nw member doesn't exist
* 	create and Invite the new entity by email
* set up the roles of the member
* Link member to the organization
* notify all members 
*/
class AddAsMemberAction extends CAction
{
    public function run() {
		$res = array( "result" => false , "msg" => Yii::t("common","Something went wrong!" ));
		
		$userId = (isset($_POST['userId'])) ? $_POST['userId'] : "";
		$userType = (isset($_POST['userType'])) ? $_POST['userType'] : "";
		$parentId = $_POST["parentId"];
		$parentType = $_POST["parentType"];
		if($parentType==Organization::COLLECTION){
			$parent = Organization::getById( $parentId );
			$connectTypeOf="memberOf";
			$connectType="members";
		}
		else if ($parentType==Project::COLLECTION){
			$parent = Project::getById( $parentId );			
			$connectTypeOf = "projects";
			$connectType = "contributors";
		}
		//The member does not exist we have to create a new member

		try {
			if(@$parent["links"][$connectType][$userId]["roles"])
				$roles=$parent["links"][$connectType][$userId]["roles"];
			else 
				$roles="";
				//2. Remove the links
				$res=Link::updateLink($parentType,$parentId,$userId,$userType,$connectType,$connectTypeOf,"toBeValidated");		

			$user = array(
				"id"=>$userId,
				"type"=>$userType
			);
			if(isset($_POST['userName']))
				$user["name"] = $_POST['userName'];			
			Notification::actionOnPerson ( ActStr::VERB_ACCEPT, ActStr::ICON_SHARE, $user, array("type"=>$parentType,"id"=> $parentId,"name"=>$parent["name"]) ) ;
			//$res["member"] = $class::getById($memberId);
		} catch (CommunecterException $e) {
			$res = array( "result" => false , "msg" => $e->getMessage() );
		}

		return Rest::json($res);
	}
}