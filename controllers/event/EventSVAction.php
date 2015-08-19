<?php
class EventSVAction extends CAction
{
    public function run($id=null, $type=null){

    	$controller = $this->getController();
    	$params = array();
    	
    	if( $type == Organization::COLLECTION )
    		$params["organizationId"] = $id;

    	$lists = Lists::get(array("eventTypes"));
    	$params["lists"] = $lists;
        if( isset($_GET["isNotSV"])) 
            $params["isNotSV"] = true;
    	if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial("eventSV", $params,true);
    }
}