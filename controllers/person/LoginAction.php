<?php
class LoginAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $controller->redirect( Yii::app()->createUrl($controller->module->id) );
        
    }
}