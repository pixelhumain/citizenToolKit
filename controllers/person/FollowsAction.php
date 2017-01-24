<?php

//Connect someone (person) to your network
//2 cases : the person exists in the db or he has to be invited
class FollowsAction extends CTKAction {
    public function run($id = null,$type = null, $ownerLink = null, $targetLink = null) {

    	/*$invitedUserId = "";

        if (! $this->userLogguedAndValid()) {
        	Rest::json(array("result" => false, "msg" => "The current user is not valid : please login."));
        	die();
        }
        
        //Case spécial : Vérifie si l'email existe et retourne l'id de l'utilisateur
        if (!empty($_POST["invitedUserEmail"]))
        	$invitedUserId = Person::getPersonIdByEmail($_POST["invitedUserEmail"]) ;

        //Case 1 : the person invited exists in the db
        if (!empty($_POST["connectUserId"]) || !empty($invitedUserId)) {
        	if (!empty($_POST["connectUserId"]))
        		$invitedUserId = $_POST["connectUserId"];

        	$child["childId"] = $this->currentUserId ;
        	$child["childType"] = Person::COLLECTION;

        	$res = Link::follow($invitedUserId, Person::COLLECTION, $child);
            $actionType = ActStr::VERB_FOLLOW;
		//Case 2 : the person invited does not exist in the db
		} else if (empty($_POST["invitedUserId"])) {
			$newPerson = array("name" => $_POST["invitedUserName"], "email" => $_POST["invitedUserEmail"], "invitedBy" => $this->currentUserId);
			
			if(!empty($_POST["msgEmail"]))
				$res = Person::createAndInvite($newPerson, $_POST["msgEmail"]);
			else
				$res = Person::createAndInvite($newPerson);

            $actionType = ActStr::VERB_INVITE;
            if ($res["result"]) {
            	$invitedUserId = $res["id"];
                $child["childId"] = $this->currentUserId;
    			$child["childType"] = Person::COLLECTION;
                $res = Link::follow($invitedUserId, Person::COLLECTION, $child);
            }
		}
		
        if (@$res["result"] == true) {
            $person = Person::getSimpleUserById($invitedUserId);
            $res = array("result" => true, "invitedUser" => $person);
        } else {
            $res = array("result" => false, "msg" => $res["msg"]);
        }*/

        if(empty($_POST["listMails"]))
            $res = Element::followPerson($_POST);
        else
            $res = Element::followPersonByListMails($_POST["listMails"], $_POST["msgEmail"], (empty($_POST["gmail"])?false:true));
        

		Rest::json($res);
    }
}

