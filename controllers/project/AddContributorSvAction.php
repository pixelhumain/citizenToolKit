<?php
class AddContributorSvAction extends CAction
{
    public function run($id=null,$type=null){

    	$controller=$this->getController();
    	$params = array();
    	
        $params["isNotSV"] = true;
		$lists = Lists::get(array("organisationTypes"));
		$params["organizationTypes"]= $lists["organisationTypes"];
		$params["id"]=$_GET["projectId"];
		$params["project"]=Project::getPublicData($_GET["projectId"]);
        if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial("addContributorSV", $params, true);
    }
}