<?php
class LoginAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $controller->layout = "//layouts/mainSimple";
        if(Yii::app()->session["userId"]) 
          $controller->redirect(Yii::app()->homeUrl);
        else
          $detect = new Mobile_Detect;
        
        $isMobile = $detect->isMobile();
        
        if($isMobile) {
           $controller->render( "loginMobile" );
        }
        else {
           $controller->render( "login" );
        }
    }
}