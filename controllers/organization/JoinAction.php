<?php

class JoinAction extends CAction
{
	/**
	 * 
	 */
    public function run($id) {
    	$controller=$this->getController();
    	$params = array();
		//get The organization Id
		if (empty($id)) {
			throw new CTKException(Yii::t("organization","The Parent organization doesn't exist !"));
		}
		
		$params["parentOrganization"] = Organization::getPublicData($id);
		
		$lists = Lists::get(array("organisationTypes","typeIntervention","public"));
		
		if ( !isset($lists["organisationTypes"]) || !isset($lists["typeIntervention"]) || !isset($lists["public"]) ) {
			throw new CTKException(Yii::t("organization",Yii::t("organization", "Missing List data in 'lists' collection, must have organisationTypes, typeIntervention, public")));
		}

		$params["types"] = $lists["organisationTypes"];
		$params["listTypeIntervention"] = $lists["typeIntervention"];
		$params["listPublic"] = $lists["public"];
		
		$params["tags"] = Tags::getActiveTags();

		$controller->layout = "//layouts/mainSimple";
		$controller->render("join", $params);
    }

}