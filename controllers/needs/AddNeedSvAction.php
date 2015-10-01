<?php
class AddNeedSvAction extends CAction
{
    public function run(){

    	$controller=$this->getController();
    	$params = array();
    	
    	//$params["countries"] = OpenData::getCountriesList();
    	if( isset($_GET["isNotSV"])) {
            $params["isNotSV"] = true;
//            $params["parentName"] = $_GET["parentName"];
        }
        if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial("addNeedSV", $params, true);
    }
}