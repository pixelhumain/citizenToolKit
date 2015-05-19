<?php

class Dashboard1Action extends CAction
{
	/**
	* Dashboard Organization
	*/
    public function run($id) {
    	$controller=$this->getController();
		if (empty($id)) {
		  throw new CTKException("The organization id is mandatory to retrieve the organization !");
		}

		$organization = Organization::getPublicData($id);
		$events = Organization::listEventsPublicAgenda($id);
		
		
		$params = array( "organization" => $organization);
		$params["events"] = $events;
		
		//Same content Key base as the dashboard
		$contentKeyBase = Yii::app()->controller->id.".dashboard";
		$params["contentKeyBase"] = $contentKeyBase;
		
		$limit = array(Document::IMG_PROFIL => 1, Document::IMG_MEDIA => 5);
		$images = Document::getListDocumentsURLByContentKey($id, $contentKeyBase, Document::DOC_TYPE_IMAGE, $limit);
		$params["images"] = $images;

		$documents = Document::getWhere( array( "type" => Organization::COLLECTION , "id" => $id) );
		$params["documents"] = $documents;
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
		
		$lists = Lists::get(array("organisationTypes"));
		$params["organizationTypes"] = $lists["organisationTypes"];

		$controller->title = (isset($organization["name"])) ? $organization["name"] : "";
		$controller->render( "dashboard1", $params );
    }
}