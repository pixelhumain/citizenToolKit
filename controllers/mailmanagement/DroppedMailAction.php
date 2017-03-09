<?php

class DroppedMailAction extends CAction {
    
    public function run() {
        $controller=$this->getController();
        try {
            $mailError = new MailError($_POST);
        } catch (CTKException $e) {
            Rest::sendResponse(450, "Webhook : ".$e->getMessage());
            die;
        }
        $mailError->actionOnEvent();

        Rest::sendResponse(200, "Ok : webhook handdled");
    }
}