<?php
class SaveMemberAction extends CAction
{
    public function run() {
		$res = array( "result" => false , "msg" => Yii::t("common","Something went wrong!" ));
		
		$memberId = (isset($_POST['memberId'])) ? $_POST['memberId'] : "";
		$memberType = (isset($_POST['memberType'])) ? $_POST['memberType'] : "";
		$memberOfId = $_POST["parentOrganisation"];
		$memberOfType = Organization::COLLECTION;
		$organization = Organization::getById( $memberOfId );

		$isAdmin = false;

		if($memberType == Person::COLLECTION) {
			$class = "Person";
			$isAdmin = (isset($_POST["memberIsAdmin"])) ? $_POST["memberIsAdmin"] : false;
			if ($isAdmin == "1") {
				$isAdmin = true;
			} else {
				$isAdmin = false;
			}
		} else if ($memberType == Organization::COLLECTION) {
			$class = "Organization";
			$isAdmin = false;
		} else {
			throw new CTKException(Yii::t("common","Can not manage the type ").$memberType);
		}

		//The member does not exist we have to create a new member
		if ($memberId == "" && ( isset($_POST['memberName']) || isset($_POST['memberName']) ) ) 
		{
			$member = array(
				 'invitedBy'=>Yii::app()->session["userId"]
			);			
			
			if(isset($_POST['memberName']))
				$member["name"] = $_POST['memberName'];
			if(isset($_POST['memberEmail']))
				$member["email"] = $_POST['memberEmail'];

			//Type d'organization
			if ($memberType == Organization::COLLECTION) { 
				$member["type"] = (isset($_POST["organizationType"])) ? $_POST["organizationType"] : "";
			}

			//create an entry in the right type collection
			$result = $class::createAndInvite($member);
			if ($result["result"]) 
				$memberId = $result["id"];
			else 
				return Rest::json($result);
		}

		if(isset($_POST["memberRoles"]))
		{
			if (gettype($_POST['memberRoles']) == "array") {
				$roles = $_POST['memberRoles'];
			} else if (gettype($_POST['memberRoles']) == "string") {
				$roles = explode(",", $_POST['memberRoles']);
			}
			$rolesOrgTab = array();
			if(isset($organization["roles"])){
				$rolesOrgTab = $organization["roles"];
			}
			foreach ($roles as $value) {
				if(!in_array($value, $rolesOrgTab)){
					array_push($rolesOrgTab, $value);
				}
			}

			//Role::setRoles($rolesOrgTab, $memberOfId, Organization::COLLECTION);
		}

		try {
			$res = Link::addMember($memberOfId, $memberOfType, $memberId, $memberType, Yii::app()->session["userId"], $isAdmin, $roles );
			Notification::actionOnPerson ( ActStr::VERB_JOIN, ActStr::ICON_SHARE, $memberId,$memberType, Yii::app()->session['user']['name'], Organization::COLLECTION, $memberOfId,$organization["name"] ) ;
			$res["member"] = $class::getById($memberId);
		} catch (CommunecterException $e) {
			$res = array( "result" => false , "msg" => $e->getMessage() );
		}

		return Rest::json($res);
	}
}