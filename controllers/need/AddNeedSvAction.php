<?php
class AddNeedSvAction extends CAction
{
    public function run($id=null,$type=null){

    	$controller=$this->getController();
    	$params = array();
    	
    	//$params["countries"] = OpenData::getCountriesList();

        if( $type == Project::COLLECTION )
                $params["project"]  = Project::getPublicData($id);
        else if( $type == Organization::COLLECTION )
                $params["organization"]  = Organization::getPublicData($id);
            //$params["parentName"] = $_GET["parentName"];
        if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial("addNeedSV", $params, true);
    }
}