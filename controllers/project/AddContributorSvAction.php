<?php
class AddContributorSvAction extends CAction
{
    public function run(){

    	$controller=$this->getController();
    	$params = array();
    	
    	//$params["countries"] = OpenData::getCountriesList();
    	if( isset($_GET["isNotSV"])) {
            $params["isNotSV"] = true;
			$lists = Lists::get(array("organisationTypes"));
			$params["organizationTypes"]= $lists["organisationTypes"];
			$params["id"]=$_GET["projectId"];
			$params["projectName"]=$_GET["projectName"];
        }
        if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial("addContributorSV", $params, true);
    }
}