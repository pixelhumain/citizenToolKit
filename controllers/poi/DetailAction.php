<?php

class DetailAction extends CAction {
/**
* Dashboard Organization
*/
    public function run($type, $id) { 
    	$controller=$this->getController();
		$members=array();
		//$list = Lists::get(array("eventTypes"));
		$events=array();
		$projects=array();
		$needs=array();

		if($type != Person::COLLECTION){
			$listsToRetrieveOrga = array("public", "typeIntervention", "organisationTypes", "NGOCategories", "localBusinessCategories");
			$listsOrga = Lists::get($listsToRetrieveOrga);

			$listsToRetrieveEvent = array("eventTypes");
			$listsEvent = Lists::get($listsToRetrieveEvent);
		}
		


		if($type == Organization::COLLECTION){
			$element = Organization::getById($id);
			$params["listTypes"] = isset($listsOrga["organisationTypes"]) ? $listsOrga["organisationTypes"] : null;
			$params["public"] 			 = isset($listsOrga["public"]) 			  ? $listsOrga["public"] : null;
			$params["typeIntervention"]  = isset($listsOrga["typeIntervention"])  ? $listsOrga["typeIntervention"] : null;
			$params["NGOCategories"] 	 = isset($listsOrga["NGOCategories"]) 	  ? $listsOrga["NGOCategories"] : null;
			$params["localBusinessCategories"] = isset($listsOrga["localBusinessCategories"]) ? $listsOrga["localBusinessCategories"] : null;
			$connectType = "members";
			// Link with events
			if(isset($element["links"]["events"])){
				foreach ($element["links"]["events"] as $keyEv => $valueEv) {
					 $event = Event::getSimpleEventById($keyEv);
	           		 $events[$keyEv] = $event;
				}
			}
			
			// Link with projects
			if(isset($element["links"]["projects"])){
				foreach ($element["links"]["projects"] as $keyProj => $valueProj) {
					 $project = Project::getPublicData($keyProj);
	           		 $projects[$keyProj] = $project;
				}
			}
			
			// Link with needs
			if(isset($element["links"]["needs"])){
				foreach ($element["links"]["needs"] as $key => $value){
					$need = Need::getSimpleNeedById($key);
	           		$needs[$key] = $need;
				}
			}
			

		} else if ($type == Project::COLLECTION){
			$element = Project::getById($id);
			$params["eventTypes"] = $listsEvent["eventTypes"];
			$params["listTypes"] = @$listsEvent["eventTypes"];
			$connectType = "contributors";
			// Link with events
			if(isset($element["links"]["events"])){
				foreach ($element["links"]["events"] as $keyEv => $valueEv) {
					 $event = Event::getSimpleEventById($keyEv);
	           		 $events[$keyEv] = $event;
				}
			}

			if(isset($element["links"]["needs"])){
				foreach ($element["links"]["needs"] as $key => $value){
					$need = Need::getSimpleNeedById($key);
	           		$needs[$key] = $need;
				}
			}

		} else if ($type == Event::COLLECTION){
			$element = Event::getById($id);
			$params["listTypes"] = $listsEvent["eventTypes"];
			$connectType = "attendees";
			$invitedNumber=0;
			$attendeeNumber=0;
			if(@$element["links"][$connectType]){
				foreach ($element["links"][$connectType] as $uid => $e) {
					if(@$e["invitorId"]){
		  				if(@Yii::app()->session["userId"] && $uid==Yii::app()->session["userId"])
		  					$params["invitedMe"]=array("invitorId"=>$e["invitorId"],"invitorName"=>$e["invitorName"]);
		  				$invitedNumber++;
			  		} else
	  					$attendeeNumber++;

				}
			}
			//EventOrganizer
			if(@$element["links"]["organizer"]){
				foreach ($element["links"]["organizer"] as $uid => $e) {
            		$organizer["type"] = $e["type"];
            		if($organizer["type"] == Project::COLLECTION ){
                		$iconNav="fa-lightbulb-o";
                		$urlType="project";
                		$organizerInfo = Project::getSimpleProjectById($uid);
                		$organizer["type"]=$urlType;
            		}
            		else if($organizer["type"] == Organization::COLLECTION ){
		                $iconNav="fa-group";
		                $urlType="organization";	
		                $organizerInfo = Organization::getSimpleOrganizationById($uid);  
						$organizer["type"]=$urlType;
						$organizer["typeOrga"]=$organizerInfo["type"];              
            		}
					else{
						$iconNav="fa-user";
		                $urlType="person";	
		                $organizerInfo = Person::getSimpleUserById($uid);  
						$organizer["type"]=$urlType;
					}
            		$organizer["id"] = $uid;
            		$organizer["name"] = $organizerInfo["name"];
            		$organizer["profilImageUrl"] = $organizerInfo["profilImageUrl"];
          		}
		  		$params["organizer"] = $organizer;
              		
            }
			//events can have sub evnets

	        $params["subEvents"] = PHDB::find(Event::COLLECTION,array("parentId"=>$id));
	        $params["subEventsOrganiser"] = array();
	        $hasSubEvents = false;
	        if(@$params["subEvents"]){
	        	$hasSubEvents = true;
	        	foreach ($params["subEvents"] as $key => $value) {
	        		if( @$value["links"]["organizer"] ){
		        		foreach ($value["links"]["organizer"] as $key => $value) {
		        			if( !@$params["subEventsOrganiser"][$key])
		        				$params["subEventsOrganiser"][$key] = Element::getInfos( $value["type"], $key);
		        		}
	        		}
	        	}
	        }

		} else if ($type == Person::COLLECTION){
			$element = Person::getById($id);
			$connectType = "attendees";
		}
		$params["controller"] = Element::getControlerByCollection($type);
		if(	@$element["links"] ) {
			if(isset($element["links"][$connectType])){
				$countStrongLinks=count($element["links"][$connectType]);
				$nbMembers=0;
				foreach ($element["links"][$connectType] as $key => $aMember) {
					if($nbMembers < 11){
						if($aMember["type"]==Organization::COLLECTION){
							$newOrga = Organization::getSimpleOrganizationById($key);
							if(!empty($newOrga)){
								if ($aMember["type"] == Organization::COLLECTION && @$aMember["isAdmin"]){
									$newOrga["isAdmin"]=true;  				
								}
								$newOrga["type"]=Organization::COLLECTION;
								//array_push($contextMap["organizations"], $newOrga);
								//array_push($members, $newOrga);
								$members[$key] = $newOrga ;
							}
						} else if($aMember["type"]==Person::COLLECTION){
							if(!@$aMember["invitorId"]){
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
									//array_push($contextMap["people"], $newCitoyen);
									//array_push($members, $newCitoyen);
									$members[$key] = $newCitoyen ;
								}
							}
						}
						$nbMembers++;
					} else {
						break;
					}
				}
			}
		}
		//$lists = Lists::get($listsToRetrieve);
		//$params["eventTypes"] = $list["eventTypes"];
		$params["tags"] = Tags::getActiveTags();
		$params["element"] = $element;
		$params["members"] = $members;
		$params["type"] = $type;
		$params["events"]=$events;
		$params["projects"]=$projects;
		$params["needs"]=$needs;
		$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $element["_id"]);
		$params["openEdition"] = Authorisation::isOpenEdition($element["_id"], $type, @$element["preferences"]);
		$params["isLinked"] = Link::isLinked((string)$element["_id"],$type, Yii::app()->session['userId'], @$element["links"]);
		
		if($type==Event::COLLECTION){
			$params["countStrongLinks"]= @$attendeeNumber;
			$params["countLowLinks"] = @$invitedNumber;
		}
		else{
			$params["countStrongLinks"]= @$countStrongLinks;
			$params["countLowLinks"] = count(@$element["links"]["followers"]);
		}
		$params["countries"] = OpenData::getCountriesList();

		if(@$_POST["modeEdit"]){
			$params["modeEdit"]=$_POST["modeEdit"];
		}
		
		$page = "detail";
		if(Yii::app()->request->isAjaxRequest)
          echo $controller->renderPartial($page,$params,true);
        else 
			$controller->render( $page , $params );
    }
}
