<?php

class DetailAction extends CAction
{
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
        //$admins = array();
        $attending =array();
        //$controller->toolbarMBZ = array();
        if(!empty($event)){
          $params = array();
          if(isset($event["links"])){
            foreach ($event["links"]["attendees"] as $uid => $e) {

              $citoyen = Person::getPublicData($uid);
              if(!empty($citoyen)){
	            $citoyen["type"]=Person::COLLECTION;
	            $profil = Document::getLastImageByKey($uid, Person::COLLECTION, Document::IMG_PROFIL);
	  			if($profil !="")
					$citoyen["imagePath"]= $profil;
                array_push($people, $citoyen);
                array_push($attending, $citoyen);

                /*if( $uid == Yii::app()->session['userId'] )
                    array_push($controller->toolbarMBZ, array('position'=>'right', 
                                                              'label'=> Yii::t("common","Contact"), 
                                                              'tooltip' => Yii::t("common","Send a message to this Event"),
                                                              "iconClass"=>"fa fa-envelope-o",
                                                              "href"=>"<a href='#' class='new-news tooltips btn btn-default' data-id='".$id."' data-type='".Event::COLLECTION."' data-name='".$event['name']."'") );*/
              }

              /*if(isset($e["isAdmin"]) && $e["isAdmin"]==true){
                array_push($admins, $e);
              }*/
            }
            if(isset($event["links"]["organizer"])){
              foreach ($event["links"]["organizer"] as $uid => $e) 
              {
	            $organizer["type"] = $e["type"];
	            if($organizer["type"] == Project::COLLECTION ){
	                $iconNav="fa-lightbulb-o";
	                $urlType="project";
	                $organizerInfo = Project::getById($uid);
	                $organizer["type"]=$urlType;
                }
                else{
	                $iconNav="fa-group";
	                $urlType="organization";	
	                $organizerInfo = Organization::getById($uid);  
					$organizer["type"]=$urlType;
					$organizer["typeOrga"]=$organizerInfo["type"];              
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
            }else if(isset($event["links"]["creator"]))
            {
                foreach ($event["links"]["creator"] as $uid => $e)
                {
                    $citoyen = Person::getById($uid);
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
