<?php
class EventSVAction extends CAction
{
    public function run($id, $type){

    	$controller=$this->getController();
    	$params = array();
    	
    	if($type==Organization::COLLECTION){
    		$params["organizationId"] = $id;
    	}

    	if(Yii::app()->request->isAjaxRequest)
		        echo $controller->renderPartial("eventSV", $params,true);
    }
}