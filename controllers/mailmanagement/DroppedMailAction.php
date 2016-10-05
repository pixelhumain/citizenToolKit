<?php

class DroppedMailAction extends CAction {
    
    public function run() {
        $controller=$this->getController();
        error_log(explode($_POST));

        $email = @$_POST["recipient"];
        if (!empty($email)) {
            $account = PHDB::findOne(array(Person::COLLECTION,array("email"=>$email)));
            if (!empty($account)) {
                //Set invalid flag on the person
                Person::updatePersonField($account["id"],"isNotValidEmail", false);
            } else {
                error_log("Webhook : unknown user with that email : ".$email);
            }
        }
    }
}