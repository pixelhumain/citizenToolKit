<?php

class AdhererAction extends CAction
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
		$controller->title = "ADHERER A GRANDDIR";
		$controller->pageTitle = "GRANDDIR - Comment adherer";

		$params["organization"] = $organization;
		
		$controller->render( "adherer", $params);
    }
}
