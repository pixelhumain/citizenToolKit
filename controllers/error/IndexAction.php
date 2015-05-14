<?php
class IndexAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $controller->pageTitle = "ERREUR";
	    if($error=Yii::app()->errorHandler->error)
	    {
	      if(Yii::app()->request->isAjaxRequest)
	        echo $error['message'];
	      else
	        $controller->render('error', array("error"=>$error));
	    }else 
	      $controller->render( "index");
    }
}