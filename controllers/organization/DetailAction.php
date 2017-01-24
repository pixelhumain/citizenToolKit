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

		$organization = Organization::getById($id);
		$events = array();
		$projects = array();
		$needs = array(); 
		$members = array();
		$limit = array(Document::IMG_PROFIL => 1);
		$images = Document::getImagesByKey((string)$organization["_id"],Organization::COLLECTION, $limit);
		$params = array( "organization" => $organization);
		$params["images"] = $images;
		$list = Lists::get(array("eventTypes"));
        $params["eventTypes"] = $list["eventTypes"];
		$contextMap = array();
		$contextMap["organization"] = array($organization);
		$contextMap["people"] = array();
		$contextMap["organizations"] = array();
		$contextMap["projects"] = array();
		$contextMap["events"] = array();
		
		if(isset($organization["links"]["members"])){
			$countStrongLinks=count($organization["links"]["members"]);
			$nbMembers=0;
			foreach ($organization["links"]["members"] as $key => $aMember) {
				if($nbMembers < 11){
					if($aMember["type"]==Organization::COLLECTION){
						$newOrga = Organization::getSimpleOrganizationById($key);
						if(!empty($newOrga)){
							if ($aMember["type"] == Organization::COLLECTION && @$aMember["isAdmin"]){
								$newOrga["isAdmin"]=true;  				
							}
							$newOrga["type"]=Organization::COLLECTION;
							array_push($contextMap["organizations"], $newOrga);
							array_push($members, $newOrga);
						}
					} else if($aMember["type"]==Person::COLLECTION){
						$newCitoyen = Person::getSimpleUserById($key);
						if (!empty($newCitoyen)) {
							if (@$aMember["type"] == Person::COLLECTION) {
								if(@$aMember["isAdmin"]){
									if(@$aMember["isAdminPending"])
										$newCitoyen["isAdminPending"]=true;  
										$newCitoyen["isAdmin"]=true;  	
								}			
								if(@$aMember["toBeValidated"]){
									$newCitoyen["toBeValidated"]=true;  
								}		
			  				
							}
							$newCitoyen["type"]=Person::COLLECTION;
							array_push($contextMap["people"], $newCitoyen);
							array_push($members, $newCitoyen);
						}
					}
					$nbMembers++;
				} else {
					break;
				}
			}
		}
		// Link with events
		if(isset($organization["links"]["events"])){
			foreach ($organization["links"]["events"] as $keyEv => $valueEv) {
				 $event = Event::getSimpleEventById($keyEv);
				 array_push($contextMap["events"], $event);
           		 $events[$keyEv] = $event;
			}
		}

		// Link with projects
		if(isset($organization["links"]["projects"])){
			foreach ($organization["links"]["projects"] as $keyProj => $valueProj) {
				 $project = Project::getPublicData($keyProj);
           		 $projects[$keyProj] = $project;
           		 array_push($contextMap["projects"], $project);
			}
		}
		// Link with needs
		if(isset($organization["links"]["needs"])){
			foreach ($organization["links"]["needs"] as $key => $value){
				$need = Need::getSimpleNeedById($key);
           		$needs[$key] = $need;
			}
		}

		$params["organization"] = $organization;
		$params["members"] = $members;
		$params["projects"] = $projects;
		$params["events"] = $events;
		$params["contextMap"] = $contextMap;
		$params["needs"] = $needs;
		$params["countStrongLinks"]= @$countStrongLinks;
		$params["countLowLinks"] = count(@$organization["links"]["followers"]);
		//Preferences
		//$params["openEdition"] = Preference::isOpenEdition($organization["preferences"]);
		$params["openEdition"] = Authorisation::isOpenEdition((string)$organization["_id"], Organization::COLLECTION, @$organization["preferences"]);
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
