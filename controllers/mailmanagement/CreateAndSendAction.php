<?php

class CreateAndSendAction extends CAction {
    
    public function run() {
        $controller=$this->getController();
        try {
            $res = Mail::createAndSend($_POST);
        } catch (CTKException $e) {
            Rest::sendResponse(450, "Webhook : ".$e->getMessage());
            die;
        }
        Rest::sendResponse(200, "Ok : webhook handdled");
    }
}