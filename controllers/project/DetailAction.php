<?php

class DetailAction extends CAction
{
	/**
	* Dashboard Organization
	*/
    public function run($id) { 
    	$controller=$this->getController();
		
		$project = Project::getPublicData($id);
	
	  	$controller->sidebar1 = array(
	      array('label' => "ACCUEIL", "key"=>"home","iconClass"=>"fa fa-home","href"=>"communecter/project/dashboard/id/".$id),
	    );
	
	    $controller->title = (isset($project["name"])) ? $project["name"] : "";

		$roomCount = PHDB::count(ActionRoom::COLLECTION, array("parentType"=>Project::COLLECTION , "parentId"=>$id));
	    
	    Menu::project($project);
	    //$controller->subTitle = ( isset($project["description"])) ? ( ( strlen( $project["description"] ) > 120 ) ? substr($project["description"], 0, 120)."..." : $project["description"]) : "";
	    //$controller->pageTitle = "Communecter - Informations sur le projet ".$controller->title;
	  	$organizations = array();
	  	$people = array();
	  	$contributors =array();
	  	$followers = 0;
	  	$properties = array();
	  	$tasks = array();
	  	$needs = array();
	  	$events=array();
	  	$needs = Need::listNeeds($id, Project::COLLECTION);
	  //	$contentKeyBase = "Yii::app()->controller->id.".".dashboard";
		$limit = array(Document::IMG_PROFIL => 1);
		$images = Document::getImagesByKey((string)$project["_id"], Project::COLLECTION, $limit);
	  	/*$contentKeyBase = Yii::app()->controller->id.".dashboard";
	  	$limit = array(Document::IMG_PROFIL => 1, Document::IMG_MEDIA => 5);
	  	$images = Document::getListDocumentsURLByContentKey((string)$project["_id"], $contentKeyBase, Document::DOC_TYPE_IMAGE,$limit);*/
	  	if(!empty($project)){
	  		$params = array();
	  		// Get people or orga who contribute to the project 
	  		// Get image for each contributors														
	  		if(isset($project["links"]) && isset($project["links"]["contributors"])){
		  		$countStrongLinks=count($project["links"]["contributors"]);
		  		$nbContributors=0;
	  			foreach ($project["links"]["contributors"] as $uid => $e) {
		  			if($nbContributors < 11){
		  				if($e["type"]== Organization::COLLECTION){
		  					$organization = Organization::getSimpleOrganizationById($uid);
		  					if (!empty($organization)) {
		  						array_push($organizations, $organization);
		  						$organization["type"]=Organization::COLLECTION;
								if(@$e["isAdmin"]){
			  						$organization["isAdmin"]=true;  				
		  						}
		  						array_push($contributors, $organization);
		  					}
		  				}else if($e["type"]== Person::COLLECTION){
		  					$citoyen = Person::getSimpleUserById($uid);
		  					if(!empty($citoyen)){
		  						array_push($people, $citoyen);
		  						$citoyen["type"]=Person::COLLECTION;
								if(@$e["isAdmin"]){
									if(@$e["isAdminPending"])
										$citoyen["isAdminPending"]=true;
			  						$citoyen["isAdmin"]=true;  				
		  						}
		  						if(@$e["toBeValidated"]){
		  							$citoyen["toBeValidated"]=true;  
								}	
	
		  						array_push($contributors, $citoyen);
		  					}
		  				}
		  				$nbContributors++;
		  			} else {
						break;
					}
	  			}
	  		}
	  		
	  		if( isset($project["links"]["events"])) {
	    		foreach ($project["links"]["events"] as $key => $event) {
	    			$newEvent = Event::getSimpleEventById( $key );
	            	if (!empty($newEvent)) {
		            	array_push($events, $newEvent);
		            }
		    	}
		    }
			if (isset($project["links"]["followers"])){
				$followers = count($project["links"]["followers"]);
			}
	  		// Properties defines the chart of the Project
	  		if (isset($project["properties"]["chart"])){
		  		$properties=$project["properties"]["chart"];
	  		}
	  		//Tasks will provide the GANTT of the project
	  		if (isset($project["tasks"])){
		  		$tasks=$project["tasks"];
	  		}
	  		// Link with needs
			if(isset($project["links"]["needs"])){
				foreach ($project["links"]["needs"] as $key => $value){
					$need = Need::getSimpleNeedById($key);
	           		$needs[$key] = $need;
				}
			}
	  	}
	  	//Gestion de l'admin - true or false
	  	// First find if user session is directly link to project
	  	// Second if not, find if user belong to an organization admin of the project
	  	// return true or false
	  	$isProjectAdmin = false;
	  	$admins = array();
	  	$isProjectAdmin=Authorisation::canEditItem(Yii::app()->session["userId"], Project::COLLECTION, $project["_id"]);
		//$isProjectAdmin=Authorisation::isProjectAdmin($project["_id"], Yii::app()->session["userId"]);
	  	$lists = Lists::get(array("organisationTypes"));
	  	$params["countries"] = OpenData::getCountriesList();
	  	$params["tags"] = Tags::getActiveTags();
		$params["organizationTypes"] = $lists["organisationTypes"];
	  	$params["images"] = $images;
	  	$params["contributors"] = $contributors;
	  	$params["countStrongLinks"]= @$countStrongLinks;
	  	$params["countLowLinks"] = $followers;
	  	$params["project"] = $project;
	  	$params["organizations"] = $organizations;
	  	$listEvent = Lists::get(array("eventTypes"));
        $params["eventTypes"] = $listEvent["eventTypes"];
	  	$params["events"] = $events;
	  	$params["needs"] = $needs;
	  	$params["people"] = $people;
	  	$params["properties"] = $properties;
	  	$params["tasks"]=$tasks;
	  	//$params["needs"]=$needs;
	  	$params["admin"]=$isProjectAdmin;
	  	$params["admins"]=$admins;
	  	//Preferences
		//$params["openEdition"] = Preference::isOpenEdition(@$project["preferences"]);
		$params["openEdition"] = Authorisation::isOpenEdition((string)$project["_id"], Project::COLLECTION, @$project["preferences"]);

		//Display different for simplyDirectory
		if($controller->action->id == 'simply'){
			$page = "simplyDetail";
		}else{
			$page = "detail";
		}


		if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial($page,$params,true);
        else 
			$controller->render( $page , $params );
    }
}
