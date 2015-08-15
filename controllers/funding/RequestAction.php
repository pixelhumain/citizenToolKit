<?php
class RequestAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $controller->layout = "//layouts/mainSimple";
        /*if(Yii::app()->session["userId"]) 
          $controller->redirect(Yii::app()->homeUrl);
        else
          $detect = new Mobile_Detect;*/
        $controller->render( "request" );
    }
}