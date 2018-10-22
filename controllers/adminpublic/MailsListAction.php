<?php

class MailsListAction extends CAction
{
    public function run() {
        $controller = $this->getController();
    	$params = array();
    	$params["results"] = Cron::getCron();
    	//$params["city"] = json_encode($city) ;
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("mailslist",$params,true);
        else 
            $controller->render("mailslist",$params);
    }
}

?>