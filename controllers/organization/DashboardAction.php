<?php

class DashboardAction extends CAction
{
	/**
	* Dashboard Organization
	*/
    public function run($id) {
    	$controller=$this->getController();
		if (empty($id)) {
		  throw new CommunecterException("The organization id is mandatory to retrieve the organization !");
		}

		$organization = Organization::getPublicData($id);
		$events = Organization::listEventsPublicAgenda($id);
		$members = array( 
		  "citoyens"=> array(),
		  "organizations"=>array()
		);

		$contentKeyBase = Yii::app()->controller->id.".".Yii::app()->controller->action->id;
		$images = Document::listMyDocumentByType($id, Organization::COLLECTION, $contentKeyBase , array( 'created' => 1 ));
		
		$params = array( "organization" => $organization);
		$params["contentKeyBase"] = $contentKeyBase;
		$params["images"] = $images;
		$params["events"] = $events;
		$contextMap = array();
		$contextMap["organization"] = $organization;
		$contextMap["events"] = array();
		$contextMap["organizations"] = array();
		$contextMap["people"] = array();
		$organizations = Organization::getMembersByOrganizationId($id, Organization::COLLECTION);
		$people = Organization::getMembersByOrganizationId($id, Person::COLLECTION);
		foreach ($organizations as $key => $value) {
			$newOrga = Organization::getById($key);
			$profil = Document::getLastImageByKey($key, Organization::COLLECTION, Document::IMG_PROFIL);
			if($profil !="")
				$newOrga["imagePath"]= $profil;
			array_push($contextMap["organizations"], $newOrga);
			array_push($members["organizations"], $newOrga);

		}

		foreach ($events as $key => $value) {
			$newEvent = Event::getById($key);
			array_push($contextMap["events"], $newEvent);
		}
		foreach ($people as $key => $value) {
			$newCitoyen = Person::getById($key);
			$profil = Document::getLastImageByKey($key, Person::COLLECTION, Document::IMG_PROFIL);
			if($profil !="")
				$newCitoyen["imagePath"] = $profil;
			array_push($contextMap["people"], $newCitoyen);
			array_push($members["citoyens"], $newCitoyen);
		}
		$params["members"] = $members;
		$params["contextMap"] = $contextMap;
		//list
		$params["tags"] = Tags::getActiveTags();
		$lists = Lists::get(array("public", "typeIntervention", "organisationTypes"));
		$params["public"] = $lists["public"];
		$params["organizationTypes"] = $lists["organisationTypes"];
		$params["typeIntervention"] = $lists["typeIntervention"];
		$params["countries"] = OpenData::getCountriesList();

		//Plaquette de présentation
		$listPlaquette = Document::listDocumentByCategory($id, Organization::COLLECTION, Document::CATEGORY_PLAQUETTE, array( 'created' => 1 ));
		$params["plaquette"] = reset($listPlaquette);
		$controller->title = (isset($organization["name"])) ? $organization["name"] : "";
		$controller->render( "dashboard", $params );
    }
}