<?php
/**
* Check if the username is unique in the db.
*/
class CheckUsernameAction extends CAction {
    
    public function run() {
    	$username = @$_POST["username"];
        assert('!empty($username); //The username is mandatory');
    	
        //TODO SBAR - return a list of username available
        Rest::json(Person::isUniqueUsername($username));
    }
}