<?php

class AddMemberAction extends CAction
{
    public function run($id=null) {

		$controller=$this->getController();
		$organization = Organization::getPublicData($id);
		$params = array( "organization" => $organization);
		$lists = Lists::get(array("public", "typeIntervention", "organisationTypes"));
		$params["organizationTypes"] = $lists["organisationTypes"];
		$params["typeIntervention"] = $lists["typeIntervention"];
		if (@Yii::app()->params['betaTest']) { 
			$user = Person::getSimpleUserById(Yii::app()->session["userId"]);
			$params['numberOfInvit'] = $user["numberOfInvit"];
		}
		$controller->renderPartial( "addMembers" , $params );
    }
}