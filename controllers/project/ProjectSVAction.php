<?php
class ProjectSVAction extends CAction
{
    public function run(){

    	$controller=$this->getController();
    	$params = array();
    	
    	$params["countries"] = OpenData::getCountriesList();

        if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial("projectSV", $params, true);
    }
}