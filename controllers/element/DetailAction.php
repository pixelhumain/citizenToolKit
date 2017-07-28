<?php

class DetailAction extends CAction {
/**
* Dashboard Organization
*/
    public function run($type, $id, $view=null, $networkParams=null) { 
    	$controller=$this->getController();
		$members=array();
		//$list = Lists::get(array("eventTypes"));
		$events=array();
		$projects=array();
		$needs=array();
		$elementAuthorizationId=$id;
		$elementAuthorizationType=$type;
		if($type != Person::COLLECTION){
			$listsToRetrieveOrga = array("public", "typeIntervention", "organisationTypes", "NGOCategories", "localBusinessCategories");
			$listsOrga = Lists::get($listsToRetrieveOrga);

			$listsToRetrieveEvent = array("eventTypes");
			$listsEvent = Lists::get($listsToRetrieveEvent);
		}
		


		if($type == Organization::COLLECTION){
			$element = Organization::getById($id);
			if (empty($element)) throw new CHttpException(404,Yii::t("organization","The organization you are looking for has been moved or deleted !"));
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
	           		 if(!empty($event))
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
				foreach ($element["links"]["needs"] as $keyNeed => $value){
					$need = Need::getSimpleNeedById($keyNeed);
	           		$needs[$keyNeed] = $need;
				}
			}
			

		} else if ($type == Project::COLLECTION){
			$element = Project::getById($id);
			if (empty($element)) throw new CHttpException(404,Yii::t("projet","The project you are looking for has been moved or deleted !"));
			$params["eventTypes"] = $listsEvent["eventTypes"];
			$params["listTypes"] = @$listsEvent["eventTypes"];
			$connectType = "contributors";
			// Link with events
			if(isset($element["links"]["events"])){
				foreach ($element["links"]["events"] as $keyEv => $valueEv) {
					 $event = Event::getSimpleEventById($keyEv);
					 if(!empty($event))
		           		 $events[$keyEv] = $event;
				}
			}

			if(isset($element["links"]["needs"])){
				foreach ($element["links"]["needs"] as $keyNeed => $value){
					error_log("getting needs : ".$keyNeed);
					$need = Need::getSimpleNeedById($keyNeed);
	           		$needs[$keyNeed] = $need;
				}
			}

		} else if ($type == Event::COLLECTION){
			$element = Event::getById($id);
			if (empty($element)) throw new CHttpException(404,Yii::t("event","The event you are looking for has been moved or deleted !"));
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
						$organizer["typeOrga"]=@$organizerInfo["type"];              
            		}
					else{
						$iconNav="fa-user";
		                $urlType="person";	
		                $organizerInfo = Person::getSimpleUserById($uid);  
						$organizer["type"]=$urlType;
					}
            		$organizer["id"] = $uid;
            		$organizer["name"] = @$organizerInfo["name"];
            		$organizer["profilImageUrl"] = @$organizerInfo["profilImageUrl"];
            		$organizer["profilThumbImageUrl"] = @$organizerInfo["profilThumbImageUrl"];
          		}
		  		$params["organizer"] = $organizer;
              		
            }
			//events can have sub events
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
			if (empty($element)) throw new CHttpException(404,Yii::t("person","The person you are looking for has been moved or deleted !"));
			// Link with projects
			if(isset($element["links"]["projects"])){
				foreach ($element["links"]["projects"] as $keyProj => $valueProj) {
					 $project = Project::getPublicData($keyProj);
	           		 $projects[$keyProj] = $project;
				}
			}

			$connectType = "attendees";
		} else if ($type == Place::COLLECTION){
			$element = Place::getById($id);
			if (empty($element)) throw new CHttpException(404,Yii::t("poi","The poi you are looking for has been moved or deleted !"));
			
		}
		else if ($type == Poi::COLLECTION){
			$element = Poi::getById($id);
			if (empty($element)) throw new CHttpException(404,Yii::t("poi","The poi you are looking for has been moved or deleted !"));
			$connectType = "attendees";
			$elementAuthorizationId=$element["parentId"];
			$elementAuthorizationType=$element["parentType"];
			if($element["parentType"]==Organization::COLLECTION){
				$params["parent"] = Organization::getSimpleOrganizationById($element["parentId"]);
			}else{
				$params["parent"] = Project::getSimpleProjectById($element["parentId"]); 
			}
		}
		$params["controller"] = Element::getControlerByCollection($type);
		if(	@$element["links"] ) {
			if(isset($element["links"][$connectType])){
				$countStrongLinks=0;//count($element["links"][$connectType]);
				$nbMembers=0;
				$invitedNumber=0;
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
							//if(!@$aMember["isInviting"]){
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
										if(@$aMember["isInviting"]){
											$newCitoyen["isInviting"]=true;
										}		
					  				
									}
									$newCitoyen["type"]=Person::COLLECTION;
									//array_push($contextMap["people"], $newCitoyen);
									//array_push($members, $newCitoyen);
									$members[$key] = $newCitoyen ;
									$nbMembers++;
								}
							//}
						}
					} 
					if(!@$aMember["isInviting"])
						$countStrongLinks++;
					else{
		  				if(@Yii::app()->session["userId"] && $key==Yii::app()->session["userId"])
		  					$params["invitedMe"]=array("invitorId"=>$aMember["invitorId"],"invitorName"=>$aMember["invitorName"]);
						$invitedNumber++;
					}
					//else {
						//break;
					//}
				}
			}
		}
		if(!@$element["disabled"]){
	        //if((@$config["connectLink"] && $config["connectLink"]) || empty($config)){ TODO CONFIG MUTUALIZE WITH NETWORK AND OTHER PLATFORM
        	if((!@$element["links"][$connectType][Yii::app()->session["userId"]] || (@$element["links"][$connectType][Yii::app()->session["userId"]] && @$element["links"][$connectType][Yii::app()->session["userId"]][Link::TO_BE_VALIDATED])) && @Yii ::app()->session["userId"] && ($type != Person::COLLECTION || $element["_id"] != Yii::app()->session["userId"])){
        		$params["linksBtn"]["followBtn"]=true;
                if (@$element["links"]["followers"][Yii::app()->session["userId"]])
                    	$params["linksBtn"]["isFollowing"]=true;
                 else if(!@$element["links"]["followers"][Yii::app()->session["userId"]]     
                    && $type != Event::COLLECTION)   
                    	$params["linksBtn"]["isFollowing"]=false; 	               
            }
            // Add member , contributor, attendee
            if($type == Organization::COLLECTION)
               $connectAs="member";
            else if($type == Project::COLLECTION)
                $connectAs="contributor";
            else if($type == Event::COLLECTION)
                $connectAs="attendee";
            else if($type==Person::COLLECTION)
            	$connectAs="friend";
           $params["linksBtn"]["connectAs"]=$connectAs;
           $params["linksBtn"]["connectType"]=$connectType;
            if( @Yii::app()->session["userId"] && $type!= Person::COLLECTION && !@$element["links"][$connectType][Yii::app()->session["userId"]]){
            	$params["linksBtn"]["communityBn"]=true;	            	
            	$params["linksBtn"]["isMember"]=false;
            }else if($type != Person::COLLECTION  && @Yii::app()->session["userId"]){
                //Ask Admin button
                $connectAs="admin";
                $params["linksBtn"]["communityBn"]=true;
               	$params["linksBtn"]["isMember"]=true;
               	if(@$element["links"][$connectType][Yii::app()->session["userId"]][Link::TO_BE_VALIDATED])
               		$params["linksBtn"][Link::TO_BE_VALIDATED]=true;
               	$params["linksBtn"]["isAdmin"]=true;
               	if(@$element["links"][$connectType][Yii::app()->session["userId"]][Link::IS_ADMIN_PENDING])
               		$params["linksBtn"][Link::IS_ADMIN_PENDING]=true;
                //Test if user has already asked to become an admin
                if(!in_array(Yii::app()->session["userId"], Authorisation::listAdmins($id, $type,true)))
                	$params["linksBtn"]["isAdmin"]=false;              
            }
        }
		//$lists = Lists::get($listsToRetrieve);
		//$params["eventTypes"] = $list["eventTypes"];
		$params["subview"]=$view;
		$params["tags"] = array("TODO : écrire la liste de suggestion de tags"); Tags::getActiveTags();
		$params["element"] = $element;
		$params["members"] = $members;
		$params["type"] = $type;
		$params["events"]=$events;
		$params["projects"]=$projects;
		$params["needs"]=$needs;
		$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $elementAuthorizationType, $elementAuthorizationId);
		$params["openEdition"] = Authorisation::isOpenEdition($elementAuthorizationId, $elementAuthorizationType, @$element["preferences"]);
		
		if(@Yii::app()->session["network"]){
			$params["openEdition"] = false;
			$params["edit"] = false;
		}

		$params["isLinked"] = Link::isLinked($elementAuthorizationId,$elementAuthorizationType, 
									Yii::app()->session['userId'], 
									@$element["links"]);

		if($params["isLinked"]==true)
			$params["countNotifElement"]=ActivityStream::countUnseenNotifications(Yii::app()->session["userId"], $elementAuthorizationType, $elementAuthorizationId);
		if($type==Event::COLLECTION){
			$params["countStrongLinks"]= @$attendeeNumber;
			//$params["countLowLinks"] = @$invitedNumber;
		}
		else{
			$params["countStrongLinks"]= @$countStrongLinks;
			$params["countLowLinks"] = count(@$element["links"]["followers"]);
		}
		$params["countInvitations"]=@$invitedNumber;
		$params["countries"] = OpenData::getCountriesList();

		if(@$_POST["modeEdit"]){
			$params["modeEdit"]=$_POST["modeEdit"];
		}

		//manage delete in progress status
		$params["deletePending"] = Element::isElementStatusDeletePending($type, $id);
		
		if(@$_GET["network"])
			$params["networkJson"]=Network::getNetworkJson($_GET["network"]);
		$page = "detail";

		if(@$_GET["tpl"] == "detail")
				$page = "detail";
		
		if(@$_GET["tpl"] == "onepage")
				$page = "onepage";
			
		if(@$_GET["tpl"] == "profilSocial")
				$page = "profilSocial";

		if(@$_GET["tpl"] == "ficheInfoElement")
				$page = "ficheInfoElement";
		
		if( in_array( Yii::app()->theme->name, array("notragora") ) )
				$page = Yii::app()->theme->name."/detail";
		
		//var_dump($params); //exit;
		//$page = "onepage";
		$params["params"] = $params;
		/*if(Yii::app()->request->isAjaxRequest)
          echo $controller->renderPartial($page,$params,true);
        else 
			$controller->render( $page , $params );*/
    }
}
