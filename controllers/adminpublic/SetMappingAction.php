<?php
class SetMappingAction extends CAction
{    
    public function run(){

        $controller = $this->getController();
        $userid = Yii::app()->session["userId"];
        $params = Import::setMappings($userid,$_POST);

        Rest::json($params);
        //Yii::add()->end();
    }
}
?>