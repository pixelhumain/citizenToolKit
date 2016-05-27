<?php

class DetailAction extends CAction {
/**
* Dashboard Organization
*/
    public function run($id) { 
    	$controller=$this->getController();
		$event = Event::getPublicData($id);
        Menu::event($event);
        $controller->title = (isset($event["name"])) ? $event["name"] : "";
        $controller->subTitle = (isset($event["description"])) ? $event["description"] : "";
        $controller->pageTitle = ucfirst($controller->module->id)." - ".Yii::t("event","Event's informations")." ".$controller->title;
		$contentKeyBase = "Yii::app()->controller->id.".".dashboard";
		$limit = array(Document::IMG_PROFIL => 1, Document::IMG_MEDIA => 5);
		$images = Document::getImagesByKey((string)$event["_id"], Event::COLLECTION, $limit);
        /* Vérifier comportement si réintégration
	        $contentKeyBase = $controller->id.".dashboard";
        $images = Document::getListDocumentsURLByContentKey((string)$event["_id"], $contentKeyBase, Document::DOC_TYPE_IMAGE);*/

        $organizer = array();
        $people = array();
        $attending =array();

        if(!empty($event)){
			$params = array();
			if(isset($event["links"])){
            	foreach ($event["links"]["attendees"] as $uid => $e) {
					$citoyen = Person::getPublicData($uid);
					if(!empty($citoyen)){
						$citoyen["type"]=Person::COLLECTION;
						array_push($people, $citoyen);
						array_push($attending, $citoyen);
					}
            	}
				if(isset($event["links"]["organizer"])){
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
						else{
							$iconNav="fa-user";
			                $urlType="person";	
			                $organizerInfo = Person::getSimpleUserById($uid);  
							$organizer["type"]=$urlType;
						}
                		$organizer["id"] = $uid;
                		$organizer["name"] = $organizerInfo["name"];
                		$organizer["profilImageUrl"] = $organizerInfo["profilImageUrl"];
                		array_push($controller->toolbarMBZ, array('position' => 'right', 
                                                          'label'=> Yii::t("common","Organizator detail"), 
                                                          'tooltip' => Yii::t("common","Back to")." ".$urlType, 
                                                          "iconClass"=>"fa ".$iconNav,
														  "parent"=>"span",
                                                          "href"=>'<a href="javascript:;" onclick="loadByHash( \'#'.$urlType.'.detail.id.'.$uid.'\')" class="tooltips btn btn-default"'));
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

        $params["images"] = $images;
        $params["contentKeyBase"] = $contentKeyBase;
        $params["attending"] = $attending;
        $params["event"] = $event;
        $params["organizer"] = $organizer;
        $params["people"] = $people;
        $params["countries"] = OpenData::getCountriesList();

        $list = Lists::get(array("eventTypes"));
        $params["eventTypes"] = $list["eventTypes"];
        
		$page = "detail";
		    if(Yii::app()->request->isAjaxRequest)
          echo $controller->renderPartial($page,$params,true);
        else 
			    $controller->render( $page , $params );
    }
}
