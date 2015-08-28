<?php

//Connect someone (person) to your network
//2 cases : the person exists in the db or he has to be invited
class ConnectAction extends CTKAction {
    public function run() {

    	$res = array("result" => false);

        if (! $this->userLogguedAndValid()) {
        	return Rest::json(array("result" => false, "The current user is not valid : please login."));
        }

        //Case 1 : the person invited exists in the db
        if (!empty($_POST["connectUserId"])) {
        	$invitedUserId = $_POST["connectUserId"];
        	Link::connect($this->currentUserId, Person::COLLECTION, $_POST["connectUserId"], Person::COLLECTION, $this->currentUserId, Link::person2person);
        
		//Case 2 : the person invited does not exist in the db
		} else if (empty($_POST["invitedUserId"])) {
			$newPerson = array("name" => $_POST["invitedUserName"], "email" => $_POST["invitedUserEmail"]);;
			$res = Person::createAndInvite($newPerson, true);
		}

		return $res;
    }
}