<?php

class UpdateToPendingAction extends CAction {
    
    public function run() {
        $controller=$this->getController();
        try {
            $res = Cron::processUpdateToPending();
        } catch (CTKException $e) {
            Rest::sendResponse(450, "Webhook : ".$e->getMessage());
            die;
        }
        Rest::sendResponse(200, "Ok : webhook handdled");
    }
}