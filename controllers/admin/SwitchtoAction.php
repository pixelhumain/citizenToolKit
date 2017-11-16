<?php

class SwitchtoAction extends CAction
{
    public function run($uid)
    {
    	
        $controller=$this->getController();
        $res = array( "result" => false );
        if(@Yii::app()->session["userId"] &&  @Yii::app()->session["userIsAdmin"]){
            $person = Person::getById($uid,false);
            
            if( $person ){
                Person::saveUserSessionData($person);
                $res['result'] = true;
                $res['id']=(string)$person["_id"]
            }
        } 
        Rest::json($res);
    }
}