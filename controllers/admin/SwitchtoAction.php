<?php

class SwitchtoAction extends CAction
{
    public function run($uid)
    {
    	
        $controller=$this->getController();
        $person = Person::getById($uid);
        $res = array( "result" => false );
        if( $person ){
            Person::saveUserSessionData($person);
            $res['result'] = true;
        }
        Rest::json($res);
    }
}