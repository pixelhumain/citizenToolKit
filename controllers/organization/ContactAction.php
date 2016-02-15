<?php

class ContactAction extends CAction
{
	/**
	* Dashboard Organization
	*/
    public function run($id) { 
    	$controller=$this->getController();
		if (empty($id)) {
		  throw new CTKException(Yii::t("organization","The organization id is mandatory to retrieve the organization !"));
		}

		$organization = Organization::getPublicData($id);
		
		//Build the title/subtitle
		//Special Granddir
		$controller->title = "CONTACTEZ GRANDDIR";
		$controller->pageTitle = "GRANDDIR - Contactez le réseau GRANDDIR";

		$params["organization"] = $organization;
		$contextMap = array();
		$contextMap["organization"] = $organization;
		$contextMap["organizations"] = array();
		$params["contextMap"] = $contextMap;
		$params["members"] = array();


		$params["countries"] = OpenData::getCountriesList();

		$controller->render( "contact", $params);
    }
}
