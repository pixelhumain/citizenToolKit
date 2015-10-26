<?php
class AddNeedSvAction extends CAction
{
    public function run($id=null,$type=null){

    	$controller=$this->getController();
    	$params = array();
    	
    	//$params["countries"] = OpenData::getCountriesList();
    	if( isset($_GET["isNotSV"])) {
            $params["isNotSV"] = true;
            if( $type == Project::COLLECTION )
                $params["project"]  = Project::getPublicData($id);
            //$params["parentName"] = $_GET["parentName"];
        }
        if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial("addNeedSV", $params, true);
    }
}