<?php
class ApiAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $controller->pageTitle = "ERREUR";
        
        
       // $controller->layout = "//layouts/mainSimple";
	    if($error = Yii::app()->errorHandler->error )
	    {
	    	$controller->title = "ERREUR ".$error['code'];
        	$controller->pageTitle = $controller->title;
        	$controller->subTitle = $error['message'];

	      	if(Yii::app()->request->isAjaxRequest)
	        	echo $error['message'];
	      	else
	        	$controller->render('error', array("error"=>$error));
	    }else 
	      $controller->render( "index");
    }
}