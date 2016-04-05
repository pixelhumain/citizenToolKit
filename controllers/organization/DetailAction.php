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
		$needs = Need::listNeeds($id, Organization::COLLECTION);
		$members = array(
		  //"citoyens"=> array(),
		  //"organizations"=>array()
		);

		$controller->title = (isset($organization["name"])) ? $organization["name"] : "";
		$controller->subTitle = (isset($organization["shortDescripion"])) ? $organization["shortDescripion"] : "";
		$controller->pageTitle = "Organization ".$controller->title." - ".$controller->subTitle;
		$contentKeyBase = "Yii::app()->controller->id.".".dashboard";
		$limit = array(Document::IMG_PROFIL => 1, Document::IMG_MEDIA => 5);
		$images = Document::getImagesByKey((string)$organization["_id"],Organization::COLLECTION, $limit);
		/*Vérifier son comportement si réintégration du slider
		$contentKeyBase = Yii::app()->controller->id.".dashboard";
		$limit = array(Document::IMG_PROFIL => 1, Document::IMG_MEDIA => 5);
		$images = Document::getListDocumentsURLByContentKey((string)$organization["_id"], $contentKeyBase, Document::DOC_TYPE_IMAGE, $limit);*/
		$params = array( "organization" => $organization);
		$params["contentKeyBase"] = $contentKeyBase;
		$params["images"] = $images;
		$list = Lists::get(array("eventTypes"));
        $params["eventTypes"] = $list["eventTypes"];
		$params["events"] = $events;
		$params["needs"] = $needs;
		$contextMap = array();
		$contextMap["organization"] = array($organization);
		$contextMap["people"] = array();
		$contextMap["organizations"] = array();
		$contextMap["projects"] = array();
		$contextMap["events"] = array();
		
		$organizations = Organization::getMembersByOrganizationId($id, Organization::COLLECTION);
		$people = Organization::getMembersByOrganizationId($id, Person::COLLECTION);
		$followers = Organization::getFollowersByOrganizationId($id);
		foreach ($organizations as $key => $value) {
			$newOrga = Organization::getSimpleOrganizationById($key);
			if(!empty($newOrga)){
				if (@$organization["links"]["members"][$key] && $organization["links"]["members"][$key]["type"] == Organization::COLLECTION && @$organization["links"]["members"][$key]["isAdmin"]){
					$newOrga["isAdmin"]=true;  				
				}
				$newOrga["type"]=Organization::COLLECTION;
				array_push($contextMap["organizations"], $newOrga);
				array_push($members, $newOrga);
			}
		}
		foreach ($events as $key => $value) {
			$newEvent = Event::getById($key);
			array_push($contextMap["events"], $newEvent);
		}
		
		foreach ($projects as $key => $value) {
			$newProject = Project::getById($key);
			array_push($contextMap["projects"], $newProject);
		}
		
		foreach ($people as $key => $value) {
			$newCitoyen = Person::getSimpleUserById($key);
			if (!empty($newCitoyen)) {
				if (@$organization["links"]["members"][$key] && $organization["links"]["members"][$key]["type"] == Person::COLLECTION) {
					if(@$organization["links"]["members"][$key]["isAdmin"]){
						if(@$organization["links"]["members"][$key]["isAdminPending"])
							$newCitoyen["isAdminPending"]=true;  
							$newCitoyen["isAdmin"]=true;  	
					}			
					if(@$organization["links"]["members"][$key]["toBeValidated"]){
						$newCitoyen["toBeValidated"]=true;  
					}		
  				
				}
				$newCitoyen["type"]=Person::COLLECTION;
				array_push($contextMap["people"], $newCitoyen);
				array_push($members, $newCitoyen);
			}
		}

	    $params["organization"] = $organization;
		$params["members"] = $members;
		$params["projects"] = $projects;
		$params["contextMap"] = $contextMap;
		$params["followers"] = count($followers);
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
