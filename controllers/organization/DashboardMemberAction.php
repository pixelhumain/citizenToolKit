<?php

class DashboardMemberAction extends CAction
{
	/**
	* Dashboard Organization
	*/
    public function run($id) {
    	$controller=$this->getController();
		//get The organization Id
		if (empty($id)) {
		  throw new CTKException("The organization id is mandatory to retrieve the organization !");
		}

		$organization = Organization::getPublicData($id);
		$params = array( "organization" => $organization);
		$controller->title = (isset($organization["name"])) ? $organization["name"] : "";
		$controller->subTitle = (isset($organization["description"])) ? $organization["description"] : "";
		$controller->pageTitle = ucfirst($controller->module->id)." - Informations publiques de ".$controller->title;

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
				elseif($member['type'] == PHType::TYPE_CITOYEN )
				{
					$memberData = Person::getPublicData( $key );
					array_push( $members[PHType::TYPE_CITOYEN], $memberData );
				}
			}

			if (count($subOrganizationIds) != 0 ) {
				$randomOrganizationId = array_rand($subOrganizationIds);
				$randomOrganization = Organization::getById( $subOrganizationIds[$randomOrganizationId] );
				$params["randomOrganization"] = $randomOrganization;
			} 
			$params["members"] = $members;
		}
		$contentKeyBase = Yii::app()->controller->id.".".Yii::app()->controller->action->id;
		$params["contentKeyBase"] = $contentKeyBase;
		$images = Document::listMyDocumentByType($id, Organization::COLLECTION, $contentKeyBase , array( 'created' => 1 ));
		
		$events = Organization::listEventsPublicAgenda($id);
		$params["events"] = $events;
		$params["images"] = $images;

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