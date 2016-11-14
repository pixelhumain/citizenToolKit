<?php

class AddMembersAction extends CAction
{
    public function run($id=null, $type=null) {

		$controller=$this->getController();
		$params=array("type"=>$type, "id"=>$id);
		if($type==Organization::COLLECTION){
			$element = Organization::getPublicData($id);
		}else if($type==Project::COLLECTION){
			$element = Project::getPublicData($id);
		} else if ($type==Event::COLLECTION){
			$element = Event::getPublicData($id);
		}
		$params["element"]=$element;
		$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
		$params["openEdition"] = Authorisation::isOpenEdition($id, $type, @$element["preferences"]);

		//$params = array( "organization" => $organization);
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