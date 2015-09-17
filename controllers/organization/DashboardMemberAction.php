<?php

class DashboardMemberAction extends CAction
{
	/**
	* Dashboard Organization
	*/
    public function run($id) {
    	$controller=$this->getController();
    	$controller->title = "BIENVENUE";
		$controller->subTitle = "Découvrez le réseau Granddir et son actualité";
		$controller->pageTitle = "Granddir - Ensemble pour agir durablement à la Réunion";

		//get The organization Id
		if (empty($id)) {
		  throw new CTKException(Yii::t("organization","The organization id is mandatory to retrieve the organization !"));
		}

		$organization = Organization::getPublicData($id);
		$params = array( "organization" => $organization);

		//Same content Key base as the dashboard
		$contentKeyBase = Yii::app()->controller->id.".dashboard";
		$params["contentKeyBase"] = $contentKeyBase;

		if( isset($organization["links"]) && isset($organization["links"]["members"])) {
			
			$memberData;
			$subOrganizationIds = array();
			$members = array( 
			  "citoyens"=> array(),
			  "organizations"=>array()
			);
			
			foreach ($organization["links"]["members"] as $key => $member) {
			  
				if( $member['type'] == Organization::COLLECTION )
				{
					array_push($subOrganizationIds, $key);
					$memberData = Organization::getPublicData( $key );
					array_push( $members[Organization::COLLECTION], $memberData );
				}
				elseif($member['type'] == Person::COLLECTION )
				{
					$memberData = Person::getPublicData( $key );
					array_push( $members[Person::COLLECTION], $memberData );
				}
			}

			if (count($subOrganizationIds) != 0 ) {
				$randomOrganizationId = array_rand($subOrganizationIds);
				$randomOrganization = Organization::getById($subOrganizationIds[$randomOrganizationId]);
				
				//Load the images
				$limit = array(Document::IMG_PROFIL => 1, Document::IMG_LOGO => 1);
				$images = Document::getListDocumentsURLByContentKey((String) $randomOrganization["_id"], $contentKeyBase, Document::DOC_TYPE_IMAGE, $limit);
				$randomOrganization["images"] = $images;

				$params["randomOrganization"] = $randomOrganization;
			} 
			$params["members"] = $members;
		}
		
		$limit = array(Document::IMG_PROFIL => 1, Document::IMG_MEDIA => 5);
		$images = Document::getListDocumentsURLByContentKey($id, $contentKeyBase, Document::DOC_TYPE_IMAGE, $limit);
		$params["images"] = $images;

		$events = Organization::listEventsPublicAgenda($id);
		$params["events"] = $events;

		$lists = Lists::get(array("organisationTypes"));
		$params["organizationTypes"] = $lists["organisationTypes"];
		
		$contextMap = array();
		$contextMap["organization"] = $organization;
		$contextMap["events"] = array();
		$contextMap["organizations"] = array();
		$contextMap["people"] = array();
		$organizations = Organization::getMembersByOrganizationId($id, Organization::COLLECTION);
		$people = Organization::getMembersByOrganizationId($id, Person::COLLECTION);
		foreach ($organizations as $key => $value) {
			$newOrga = Organization::getById($key);
			array_push($contextMap["organizations"], $newOrga);
		}

		foreach ($events as $key => $value) {
			$newEvent = Event::getById($key);
			array_push($contextMap["events"], $newEvent);
		}
		foreach ($people as $key => $value) {
			$newCitoyen = Person::getById($key);
			array_push($contextMap["people"], $newCitoyen);
		}
		$params["contextMap"] = $contextMap;

		$controller->render( "dashboardMember", $params );
    }
}