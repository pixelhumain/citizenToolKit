<?php
/**
* upon Registration a email is send to the new user's email 
* he must click it to activate his account
* This is cleared by removing the tobeactivated field in the pixelactifs collection
*/
class TelegramAction extends CAction
{
    public function run($id) {
    	
        $controller=$this->getController();
        $user = Person::getById($id);
        $params = array("pseudo"=>$user["socialNetwork"]["telegram"]);
        $controller->renderPartial( "telegram" , $params );
    }

    
}