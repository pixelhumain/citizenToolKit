<?php
class AddContributorSvAction extends CAction
{
    public function run($id=null,$type=null){

    	$controller=$this->getController();
    	$params = array();
    	

		$lists = Lists::get(array("organisationTypes"));
		$params["organizationTypes"]= $lists["organisationTypes"];
		$params["id"]=$_GET["projectId"];
		$params["project"]=Project::getPublicData($_GET["projectId"]);
		if (@Yii::app()->params['betaTest']) { 
			$user=Person::getSimpleUserById(Yii::app()->session["userId"]);
			$params['numberOfInvit'] = $user["numberOfInvit"];
		}
		$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], Project::COLLECTION, $_GET["projectId"]);
		$params["openEdition"] = Authorisation::isOpenEdition($_GET["projectId"], Project::COLLECTION, @$params["project"]["preferences"]);

        if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial("addContributorSV", $params, true);
    }
}