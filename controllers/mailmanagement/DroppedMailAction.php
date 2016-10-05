<?php

class DroppedMailAction extends CAction {
    
    public function run() {
        $controller=$this->getController();
        $mailError = new MailError($_POST);

        if (!empty($mailError->recipient)) {
            $account = PHDB::findOne(Person::COLLECTION,array("email"=>$mailError->recipient));
            if (!empty($account)) {
                //Set invalid email flag on the person
                PHDB::update( Person::COLLECTION, array("_id" => $account["_id"]), array('$set' => array("isNotValidEmail" => false)));
                $mailError->save();
            } else {
                throw new CHttpException(450, "Webhook : unknown user with that email : ".$mailError->recipient);
            }
        } else {
            throw new CHttpException(450, "No email specified on the post !");
        }
    }
}