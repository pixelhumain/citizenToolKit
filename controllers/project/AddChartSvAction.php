<?php
class AddChartSvAction extends CAction
{
    public function run($id=null){

    	$controller=$this->getController();
    	//$params = array();
    	//$params["countries"] = OpenData::getCountriesList();
            $properties = str_replace('\"','"',$_GET["properties"]);
            //echo $tasks;
			$params["properties"] = unserialize(base64_decode($properties));
			$params["itemId"] = $_GET["id"];
			$params["project"] = Project::getPublicData($id);
			//print_r($_GET["tasks"]);
        if(Yii::app()->request->isAjaxRequest){
			echo $controller->renderPartial("addChartSV", $params, true);

		}
    }
}