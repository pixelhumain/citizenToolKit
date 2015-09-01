<?php

//Connect someone (person) to your network
//2 cases : the person exists in the db or he has to be invited
class ConnectAction extends CTKAction {
    public function run() {

    	$invitedUserId = "";

        if (! $this->userLogguedAndValid()) {
        	return Rest::json(array("result" => false, "msg" => "The current user is not valid : please login."));
        }

        //Case 1 : the person invited exists in the db
        if (!empty($_POST["connectUserId"])) {
        	$invitedUserId = $_POST["connectUserId"];
        	$res = Link::connect($this->currentUserId, Person::COLLECTION, $_POST["connectUserId"], Person::COLLECTION, $this->currentUserId, Link::person2person);
        
		//Case 2 : the person invited does not exist in the db
		} else if (empty($_POST["invitedUserId"])) {
			$newPerson = array("name" => $_POST["invitedUserName"], "email" => $_POST["invitedUserEmail"], "invitedBy" => $this->currentUserId);
			$res = Person::createAndInvite($newPerson);
            if ($res["result"]) {
                $invitedUserId = $res["id"];
                $res = Link::connect($this->currentUserId, Person::COLLECTION, $invitedUserId, Person::COLLECTION, $this->currentUserId, Link::person2person);
            } 
		}

        if (@$res["result"] == true) {
            $person = Person::getSimpleUserById($invitedUserId);
            $res = array("result" => true, "invitedUser" => $person);
        } else {
            $res = array("result" => false, "msg" => $res["msg"]);
        }

		return Rest::json($res);
    }
}