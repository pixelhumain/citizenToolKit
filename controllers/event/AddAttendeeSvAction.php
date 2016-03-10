<?php
class AddAttendeeSvAction extends CAction
{
    public function run(){

    	$controller=$this->getController();
    	$params = array();
    	
    	//$params["countries"] = OpenData::getCountriesList();
		$params["id"]=$_GET["eventId"];
		$params["event"]=Event::getById($_GET["eventId"]);
        if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial("addAttendeesSV", $params, true);
    }
}