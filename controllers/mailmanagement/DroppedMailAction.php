<?php

class DroppedMailAction extends CAction {
    
    public function run() {
        $controller=$this->getController();
        $mailError = new MailError($_POST);

        if (!empty($mailError->recipient)) {
            $account = PHDB::findOne(Person::COLLECTION,array("email"=>$mailError->recipient));
            if (!empty($account)) {
                //Set invalid flag on the person
                Person::updatePersonField($account["id"],"isNotValidEmail", false);
                $mailError->save();
            } else {
                throw new CHttpException(500, "Webhook : unknown user with that email : ".$mailError->recipient);
            }
        } else {
            throw new CHttpException(500, "No email specified on the post !");
        }
    }
}