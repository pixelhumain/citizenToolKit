<?php

class AddOrganizationFormAction extends CAction
{
    public function run($type=null,$id=null) {
		$controller=$this->getController();
		$organization = null;
		if(isset($id)) {
			$organization = Organization::getById($id);
			//make sure conected user is the owner
			if( $organization["email"] != Yii::app()->session["userEmail"] || ( isset($organization["ph:owner"]) && $organization["ph:owner"] != Yii::app()->session["userEmail"] ) ) {
				$organization = null;
			}
	  	}
		$types = PHDB::findOne ( PHType::TYPE_LISTS,array("name"=>"organisationTypes"), array('list'));
		$tags = Tags::getActiveTags();
	  
		$detect = new Mobile_Detect;
		$isMobile = $detect->isMobile();
	  
		$params = array( 
			"organization" => $organization,'type'=>$type,
			'types'=>$types['list'],
			'tags'=>json_encode($tags));
		if( isset($_GET["isNotSV"])) 
            $params["isNotSV"] = true;
		if($isMobile) {
			$controller->layout = "//layouts/mainSimple";
			$controller->render( "addOrganizationMobile" , $params );
		}
		else {
   			$controller->renderPartial( "addOrganizationSV" , $params );
	  	}
    }
}