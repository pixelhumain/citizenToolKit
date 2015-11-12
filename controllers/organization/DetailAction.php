<?php

class DetailAction extends CAction
{
	/**
	* Dashboard Organization
	*/
    public function run($id, $alone=false) { //alone = no toolbar, no moduleLabel in view
    	$controller=$this->getController();
		if (empty($id)) {
		  throw new CTKException(Yii::t("organization","The organization id is mandatory to retrieve the organization !"));
		}

		$organization = Organization::getPublicData($id);
		$events = Organization::listEventsPublicAgenda($id);
		$projects = Organization::listProjects($id);
		$members = array(
		  "citoyens"=> array(),
		  "organizations"=>array()
		);

		$controller->title = (isset($organization["name"])) ? $organization["name"] : "";
		$controller->subTitle = (isset($organization["shortDescripion"])) ? $organization["shortDescripion"] : "";
		$controller->pageTitle = "Organization ".$controller->title." - ".$controller->subTitle;

		$contentKeyBase = Yii::app()->controller->id.".dashboard";
		$limit = array(Document::IMG_PROFIL => 1, Document::IMG_MEDIA => 5);
		$images = Document::getListDocumentsURLByContentKey((string)$organization["_id"], $contentKeyBase, Document::DOC_TYPE_IMAGE, $limit);
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
			array_push($contextMap["organizations"], $newOrga);
			array_push($members["organizations"], $newOrga);

		}

		foreach ($events as $key => $value) {
			$newEvent = Event::getById($key);
			array_push($contextMap["events"], $newEvent);
		}
		
		foreach ($people as $key => $value) {
			$newCitoyen = Person::getById($key);
			if (!empty($newCitoyen)) {
				$profil = Document::getLastImageByKey($key, Person::COLLECTION, Document::IMG_PROFIL);
				if($profil !="")
					$newCitoyen["imagePath"] = $profil;
				array_push($contextMap["people"], $newCitoyen);
				array_push($members["citoyens"], $newCitoyen);
			}
		}

		/*$projects = array();
	    if(isset($organizations["links"]["projects"])){
	    	foreach ($organizations["links"]["projects"] as $key => $value) {
	  			$project = Project::getPublicData($key);
	  			if (! empty($project)) {
	  				array_push($projects, $project);
	  			}
	  		}
	    }*/
	    
	    $params["organization"] = $organization;
		$params["members"] = $members;
		$params["projects"] = $projects;
		$params["contextMap"] = $contextMap;
		//list
		$params["tags"] = Tags::getActiveTags();
		$listsToRetrieve = array("public", "typeIntervention", "organisationTypes", "NGOCategories", "localBusinessCategories");
		$lists = Lists::get($listsToRetrieve);
		$params["public"] 			 = isset($lists["public"]) 			  ? $lists["public"] : null;
		$params["organizationTypes"] = isset($lists["organisationTypes"]) ? $lists["organisationTypes"] : null;
		$params["typeIntervention"]  = isset($lists["typeIntervention"])  ? $lists["typeIntervention"] : null;
		$params["NGOCategories"] 	 = isset($lists["NGOCategories"]) 	  ? $lists["NGOCategories"] : null;
		$params["localBusinessCategories"] = isset($lists["localBusinessCategories"]) ? $lists["localBusinessCategories"] : null;
		
		$params["countries"] = OpenData::getCountriesList();
		//Plaquette de présentation
		$listPlaquette = Document::listDocumentByCategory($id, Organization::COLLECTION, Document::CATEGORY_PLAQUETTE, array( 'created' => 1 ));
		$params["plaquette"] = reset($listPlaquette);
		
		$params["alone"] = (isset($alone) && $alone == "true");

		$controller->title = (isset($organization["name"])) ? $organization["name"] : "";
		$page = "detail";
		

		if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial($page,$params,true);
        else 
			$controller->render( $page , $params );
    }
}
