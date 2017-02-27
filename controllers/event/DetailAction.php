<?php

class DetailAction extends CAction {
/**
* Dashboard Organization
*/
    public function run($id) { 
    	$controller=$this->getController();
		$event = Event::getPublicData($id);

		if( !is_array( Yii::app()->controller->toolbarMBZ ))
            Yii::app()->controller->toolbarMBZ = array();
        $contentKeyBase = "Yii::app()->controller->id.".".dashboard";
		$limit = array(Document::IMG_PROFIL => 1, Document::IMG_MEDIA => 5);
		$images = Document::getImagesByKey((string)$event["_id"], Event::COLLECTION, $limit);    
		   
        $organizer = array();
        $people = array();
        $attending =array();
		//$openEdition = Preference::isOpenEdition($event["preferences"]);
		$openEdition = Authorisation::isOpenEdition((string)$event["_id"], Event::COLLECTION, @$event["preferences"]);
		$invitedNumber=0;
		$attendeeNumber=0;
        if(!empty($event)){
			$params = array();
			if(isset($event["links"])){
				if(@$event["links"]["attendees"]){
	            	foreach ($event["links"]["attendees"] as $uid => $e) {
						$citoyen = Person::getSimpleUserById($uid);
						if(!empty($citoyen)){
							$citoyen["type"]=Person::COLLECTION;
							if(@$e["isAdmin"]){
								if(@$e["isAdminPending"])
									$citoyen["isAdminPending"]=true;
		  						$citoyen["isAdmin"]=true;  				
	  						}
	  						if(@$e["invitorId"]){
		  						if($uid==Yii::app()->session["userId"])
		  							$params["invitedMe"]=array("invitorId"=>$e["invitorId"],"invitorName"=>$e["invitorName"]);
		  						$invitedNumber++;
		  						$citoyen["invitorId"]=$e["invitorId"]; 
		  						$citoyen["invitorName"]=$e["invitorName"];  				
	  						} else
	  							$attendeeNumber++;
							array_push($people, $citoyen);
							array_push($attending, $citoyen);
						}
            		}
            	}
				if(@$event["links"]["organizer"]){
					//$openEdition=false;
					foreach ($event["links"]["organizer"] as $uid => $e) {
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
						else {
							$iconNav="fa-user";
			                $urlType="person";	
			                $organizerInfo = Person::getSimpleUserById($uid);  
							$organizer["type"]=$urlType;
						}
                		$organizer["id"] = $uid;
                		$organizer["name"] = @$organizerInfo["name"];
                		$organizer["profilImageUrl"] = @$organizerInfo["profilImageUrl"];
                		/*array_push($controller->toolbarMBZ, array('position' => 'right', 
                                                          'label'=> Yii::t("common","Organizator detail"), 
                                                          'tooltip' => Yii::t("common","Back to")." ".$urlType, 
                                                          "iconClass"=>"fa ".$iconNav,
														  "parent"=>"span",
                                                          "href"=>'<a href="javascript:;" onclick="url.loadByHash( \'#'.$urlType.'.detail.id.'.$uid.'\')" class="tooltips btn btn-default"'));*/
              		}
            	} else if(isset($event["links"]["creator"])) {
	                foreach ($event["links"]["creator"] as $uid => $e) {
	                    $citoyen = Person::getSimpleUserById($uid);
	                    $organizer["id"] = $uid;
	                    $organizer["type"] = "person";
	                    $organizer["name"] = $citoyen["name"];
	                }
            	}
          	}
        }
        //events can have sub events
        $params["subEvents"] = PHDB::find(Event::COLLECTION,array("parentId"=>$id));
        $params["subEventsOrganiser"] = array();
        $hasSubEvents = false;
        if(@$params["subEvents"]){
        	$hasSubEvents = true;
        	foreach ($params["subEvents"] as $idSubEvent => $subEvent) {
        		if( @$subEvent["links"]["organizer"] )
        		{
	        		foreach ($subEvent["links"]["organizer"] as $idOrganizer => $value) {
	        			if( !@$params["subEventsOrganiser"][$idOrganizer])
	        				$params["subEventsOrganiser"][$idOrganizer] = Element::getInfos( $value["type"], $idOrganizer);
	        		}
        		}
        	}
        }
        Menu::event($event,$hasSubEvents);

        $admin = false;
		if(isset(Yii::app()->session["userId"]) && isset($id)){
			$admin = Authorisation::canEditItem(Yii::app()->session["userId"], Event::COLLECTION, (string)$event["_id"]);
		}

		$params["tags"] = Tags::getActiveTags();
        $params["invitedNumber"]=$invitedNumber;
        $params["attendeeNumber"]= $attendeeNumber;
        $params["images"] = $images;
        $params["contentKeyBase"] = $contentKeyBase;
        $params["attending"] = $attending;
        $params["event"] = $event;
        $params["organizer"] = $organizer;
        $params["people"] = $people;
        $params["openEdition"] = $openEdition;
        $params["countries"] = OpenData::getCountriesList();
		$params["admin"] = $admin ;


        $list = Lists::get(array("eventTypes"));
        $params["eventTypes"] = $list["eventTypes"];
        

		$page = "detail";
		    if(Yii::app()->request->isAjaxRequest)
          echo $controller->renderPartial($page,$params,true);
        else 
			    $controller->render( $page , $params );
    }
}
