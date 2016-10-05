<?php

class DroppedMailAction extends CAction {
    
    public function run() {
        $controller=$this->getController();
        $mailError = new MailError($_POST);

        if (!empty($mailError->email)) {
            $account = PHDB::findOne(array(Person::COLLECTION,array("email"=>$email)));
            if (!empty($account)) {
                //Set invalid flag on the person
                Person::updatePersonField($account["id"],"isNotValidEmail", false);
                $mailError->save();
            } else {
                $this->_sendResponse(500, CJSON::encode("Webhook : unknown user with that email : ".$email));
            }
        } else {
            $this->_sendResponse(500, CJSON::encode("No email specified on the post !"));
        }
    }
}