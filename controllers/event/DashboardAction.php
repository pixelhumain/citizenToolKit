<?php
class DashboardAction extends CAction
{
    public function run($id)
    {
        $controller=$this->getController();
        $event = Event::getPublicData($id);

        
        $controller->title = (isset($event["name"])) ? $event["name"] : "";
        $controller->subTitle = (isset($event["description"])) ? $event["description"] : "";
        $controller->pageTitle = ucfirst($controller->module->id)." - ".Yii::t("event","Event's informations")." ".$controller->title;

        $contentKeyBase = $controller->id.".".$controller->action->id;
        $images = Document::getListDocumentsURLByContentKey($id, $contentKeyBase, Document::DOC_TYPE_IMAGE);

        $organizer = array();

        $people = array();
        //$admins = array();
        $attending =array();
        $controller->toolbarMBZ = array();
        if(!empty($event)){
          $params = array();
          if(isset($event["links"])){
            foreach ($event["links"]["attendees"] as $id => $e) {

              $citoyen = Person::getPublicData($id);
              if(!empty($citoyen)){
                array_push($people, $citoyen);
                array_push($attending, $citoyen);
              }

              /*if(isset($e["isAdmin"]) && $e["isAdmin"]==true){
                array_push($admins, $e);
              }*/
            }
            if(isset($event["links"]["organizer"])){
              foreach ($event["links"]["organizer"] as $id => $e) {
	            $organizer["type"] = $e["type"];
	            if($organizer["type"]=="projects"){
	                $iconNav="fa-lightbulb-o";
	                $urlType="project";
	                $organizerInfo = Project::getById($id);
	                $organizer["type"]=$urlType;
                }
                else{
	                $iconNav="fa-group";
	                $urlType="organization";	
	                $organizerInfo = Organization::getById($id);  
					$organizer["type"]=$urlType;              
                }

                
                $organizer["id"] = $id;

                $organizer["name"] = $organizerInfo["name"];
                                array_push($controller->toolbarMBZ,"<a href='".Yii::app()->createUrl("/".$controller->module->id."/".$urlType."/dashboard/id/".$id)."'><i class='fa ".$iconNav."'></i>".$organizer["type"]."</a>");
              }
            }else if(isset($event["links"]["creator"])){
              foreach ($event["links"]["creator"] as $id => $e) {
                $citoyen = Person::getById($id);
                $organizer["id"] = $id;
                $organizer["type"] = "person";
                $organizer["name"] = $citoyen["name"];
              }
            }
          }
        }

        if(isset($event["_id"]) && isset(Yii::app()->session["userId"]) && Link::isLinked($event["_id"] , Event::COLLECTION , Yii::app()->session['userId']))
    			array_push($controller->toolbarMBZ,"<li id='linkBtns'><a href='javascript:;' class='disconnectBtn text-red tooltips' data-name='".$event["name"]."' data-id='".$event["_id"]."' data-type='".Event::COLLECTION."' data-member-id='".Yii::app()->session["userId"]."' data-ownerlink='".Link::person2events."' data-targetlink='".Link::event2person."' data-placement='top' data-original-title='No more Attendee' ><i class='disconnectBtnIcon fa fa-unlink'></i>NO ATTENDING</a></li>" );
    		else
    			array_push($controller->toolbarMBZ,"<li id='linkBtns'><a href='javascript:;' class='connectBtn tooltips ' id='addKnowsRelation' data-placement='top' data-ownerlink='".Link::person2events."' data-targetlink='".Link::event2person."' data-original-title='I know this person' ><i class=' connectBtnIcon fa fa-link '></i>ATTENDING</a></li>");

        $params["images"] = $images;
        $params["contentKeyBase"] = $contentKeyBase;
        $params["attending"] = $attending;
        $params["event"] = $event;
        $params["organizer"] = $organizer;
        $params["people"] = $people;
        $params["countries"] = OpenData::getCountriesList();

        $list = Lists::get(array("eventTypes"));
        $params["eventTypes"] = $list["eventTypes"];
        //$params["admins"] = $admins;
        $controller->render( "dashboard", $params );
    }
}
