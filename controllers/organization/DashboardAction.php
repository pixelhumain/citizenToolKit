<?php

class DashboardAction extends CAction
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
		$events = Organization::listEventsPublicAgenda($id);
		$projects = Organization::listProjects($id);
		$members = array(
		  "citoyens"=> array(),
		  "organizations"=>array()
		);

		$controller->title = (isset($organization["name"])) ? $organization["name"] : "";
		$controller->subTitle = (isset($organization["shortDescripion"])) ? $organization["shortDescripion"] : "";
		$controller->pageTitle = "Organization ".$controller->title." - ".$controller->subTitle;


		if( !isset( $organization["disabled"] ) ){
			if(isset($organization["_id"]) && isset(Yii::app()->session["userId"]) && Link::isLinked((string)$organization["_id"], Organization::COLLECTION , Yii::app()->session["userId"]))
				$controller->toolbarMBZ = array("<li id='linkBtns'><a href='javascript:;' class='removeMemberBtn text-red tooltips' data-name='".$organization["name"]."' data-memberof-id='".$organization["_id"]."' data-member-type='".Person::COLLECTION."' data-member-id='".Yii::app()->session["userId"]."' data-placement='top' data-original-title='Remove from my Organizations' ><i class='disconnectBtnIcon fa fa-unlink'></i>NOT MEMBER</a></li>" );
			else
				$controller->toolbarMBZ = array("<li id='linkBtns'><a href='javascript:;' class='connectBtn tooltips ' id='addMeAsMemberInfo' data-placement='top' data-original-title='I'm member of this organization' ><i class=' connectBtnIcon fa fa-link '></i>I'M MEMBER</a></li>");
		} else 
			$controller->toolbarMBZ = array();
			//$controller->toolbarMBZ = array("<li id='linkBtns'><a href='javascript:;' class='tooltips ' data-placement='top' data-original-title='This Organization is disabled' ><i class='text-red fa fa-times '></i>DISABLED</a></li>");
		array_push($controller->toolbarMBZ, "<a href='".Yii::app()->createUrl("/".$controller->module->id."/survey/index/type/".Organization::COLLECTION."/id/".$id)."'><i class='fa fa-legal'></i>SURVEYS</a>");
		array_push($controller->toolbarMBZ, "<a href='".Yii::app()->createUrl("/".$controller->module->id."/news/index/type/".Organization::COLLECTION."/id/".$id)."'><i class='fa fa-rss'></i>TIMELINE</a>");
		
		$contentKeyBase = Yii::app()->controller->id.".".Yii::app()->controller->action->id;
		$limit = array(Document::IMG_PROFIL => 1, Document::IMG_MEDIA => 5);
		$images = Document::getListDocumentsURLByContentKey($id, $contentKeyBase, Document::DOC_TYPE_IMAGE, $limit);

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
			if( $key == Yii::app()->session['userId'] )
				array_push($controller->toolbarMBZ, "<a href='#' class='new-news' data-id='".$id."' data-type='".Organization::COLLECTION."'><i class='fa fa-comment'></i>MESSAGE</a>");
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

		$params["members"] = $members;
		$params["projects"] = $projects;
		$params["contextMap"] = $contextMap;
		//list
		$params["tags"] = Tags::getActiveTags();
		$lists = Lists::get(array("public", "typeIntervention", "organisationTypes"));
		$params["public"] = $lists["public"];
		$params["organizationTypes"] = $lists["organisationTypes"];
		$params["countries"] = OpenData::getCountriesList();
		$controller->title = (isset($organization["name"])) ? $organization["name"] : "";
		$controller->render( "dashboard", $params );
    }
}
