<?php
class EventSVAction extends CAction
{
    public function run($id, $type){

    	$controller=$this->getController();
    	$params = array();
    	
    	if($type==Organization::COLLECTION){
    		$params["organizationId"] = $id;
    	}

    	$lists = Lists::get(array("eventTypes"));
    	$params["lists"] = $lists;
    	if(Yii::app()->request->isAjaxRequest)
		        echo $controller->renderPartial("eventSV", $params,true);
    }
}