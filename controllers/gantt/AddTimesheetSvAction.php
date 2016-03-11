<?php
class AddTimesheetSvAction extends CAction
{
    public function run($id = null, $type = null){

    	$controller=$this->getController();
    	//$params = array();
    	//$params["countries"] = OpenData::getCountriesList();

            $tasks = str_replace('\"','"',$_GET["tasks"]);
            //echo $tasks;
			$params["tasks"] = unserialize(base64_decode($tasks));
            $params["project"]  = Project::getPublicData($id);

        if(Yii::app()->request->isAjaxRequest){
			echo $controller->renderPartial("addTimesheetSV", $params, true);

		}
    }
}