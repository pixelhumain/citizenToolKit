<?php

class DetailAction extends CAction
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

		
	  	$controller->toolbarMBZ = array();
		//$controller->toolbarMBZ = array("<li id='linkBtns'><a href='javascript:;' class='tooltips ' data-placement='top' data-original-title='This Organization is disabled' ><i class='text-red fa fa-times '></i>DISABLED</a></li>");
		
		array_push( $controller->toolbarMBZ, array('tooltip' => "SURVEYS : Organization Action Room","iconClass"=>"fa fa-legal","href"=>"<a class='tooltips btn btn-default' href='".Yii::app()->createUrl("/".$controller->module->id."/survey/index/type/".Organization::COLLECTION."/id/".$id)."'") );
		$onclick = "showAjaxPanel( baseUrl+'/'+moduleId+'/news/index/type/".Organization::COLLECTION."/id/".$id."', 'ORGANIZATION ACTIVITY ','rss' )";
	  	array_push( $controller->toolbarMBZ, array('tooltip' => "TIMELINE : Organization Activity","iconClass"=>"fa fa-rss","href"=>"<a  class='tooltips btn btn-default' href='#' onclick=\"".$onclick."\"") );
	  	$onclick = "showAjaxPanel( baseUrl+'/'+moduleId+'/news/index/type/".Organization::COLLECTION."/id/".$id."', 'ORGANIZATION ACTIVITY ','rss' )";
	  	array_push( $controller->toolbarMBZ, array('tooltip' => "MEMBERS : Organization participants","iconClass"=>"fa fa-users","href"=>"<a  class='tooltips btn btn-default' href='#' onclick=\"".$onclick."\"") );

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
			if( $key == Yii::app()->session['userId'] )
				array_push($controller->toolbarMBZ, array('tooltip' => "Send a message to this Organization","iconClass"=>"fa fa-envelope-o","href"=>"<a href='#' class='new-news tooltips btn btn-default' data-id='".$id."' data-type='".Organization::COLLECTION."' data-name='".$organization['name']."'") );
			$newCitoyen = Person::getById($key);
			if (!empty($newCitoyen)) {
				$profil = Document::getLastImageByKey($key, Person::COLLECTION, Document::IMG_PROFIL);
				if($profil !="")
					$newCitoyen["imagePath"] = $profil;
				array_push($contextMap["people"], $newCitoyen);
				array_push($members["citoyens"], $newCitoyen);
			}
		}

		if( !isset( $organization["disabled"] ) ){
			//Link button
			if(isset($organization["_id"]) && isset(Yii::app()->session["userId"]) && Link::isLinked((string)$organization["_id"], Organization::COLLECTION , Yii::app()->session["userId"]))
				$htmlFollowBtn = array('tooltip' => "leave this Organization", "iconClass"=>"disconnectBtnIcon fa fa-unlink",
					"href"=>"<a href='#' class='removeMemberBtn text-red tooltips btn btn-default' data-name='".$organization["name"]."' data-memberof-id='".$organization["_id"]."' data-member-type='".Person::COLLECTION."' data-member-id='".Yii::app()->session["userId"]."'");
			else
				$htmlFollowBtn = array('tooltip' => "join this Organization", "iconClass"=>"connectBtnIcon fa fa-unlink","href"=>"<a href='javascript:;' class='connectBtn tooltips btn btn-default ' id='addMeAsMemberInfo'");
	  		array_push($controller->toolbarMBZ, $htmlFollowBtn);
	  		
	  		//Ask Admin button
	  		if (! Authorisation::isOrganizationAdmin(Yii::app()->session["userId"], $id)) {
	  			array_push($controller->toolbarMBZ, array('tooltip' => "Declare me as admin of this organization","iconClass"=>"fa fa-user-plus","href"=>"<a href='#' class='declare-me-admin tooltips btn btn-default' data-id='".$id."' data-type='".Organization::COLLECTION."' data-name='".$organization['name']."'") );
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
		$params["typeIntervention"] = $lists["typeIntervention"];
		$params["countries"] = OpenData::getCountriesList();
		//Plaquette de présentation
		$listPlaquette = Document::listDocumentByCategory($id, Organization::COLLECTION, Document::CATEGORY_PLAQUETTE, array( 'created' => 1 ));
		$params["plaquette"] = reset($listPlaquette);
		$controller->title = (isset($organization["name"])) ? $organization["name"] : "";
		$page = "detail";
		if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial($page,$params,true);
        else 
			$controller->render( $page , $params );
    }
}
