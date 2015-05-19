<?php
class EventSVAction extends CAction
{
    public function run(){
    	$controller=$this->getController();
    	$params = array();
    	if(Yii::app()->request->isAjaxRequest)
		        echo $controller->renderPartial("eventSV", $params,true);
    }
}