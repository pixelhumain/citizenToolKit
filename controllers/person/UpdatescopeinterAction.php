<?php
/**
* Update an information field for a person
*/
class UpdatescopeinterAction extends CAction
{
    public function run()
    {   
        $controller=$this->getController();
        if(isset(Yii::app()->session["userId"]) && !empty(Yii::app()->session["userId"])){
            $res = Person::updateScopeInter(Yii::app()->session['userId']);
            return Rest::json($res);
        }else{
            return Rest::json(array("result" => false , "msg"=>"You are not connected"));
        }
    }
}

?>