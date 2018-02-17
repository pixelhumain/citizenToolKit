<?php
/**
* Check if the email is unique in the db.
*/
class CheckEmailAction extends CAction {
    
    public function run() {
    	$email = @$_POST["email"];
        assert('!empty($email); //The email is mandatory');
        Rest::json(array("res"=>Person::isUniqueEmail($email)));
    }
}