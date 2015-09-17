<?php
class DashboardAction extends CAction
{
    public function run($id)
    {
        $controller=$this->getController();
        $event = Event::getPublicData($id);

        $controller->sidebar1 = array(
          array('label' => "ACCUEIL", "key"=>"home","iconClass"=>"fa fa-home","href"=>$controller->module->id."/event/dashboard/id/".$id),
        );

        $controller->title = (isset($event["name"])) ? $event["name"] : "";
        $controller->subTitle = mb_strimwidth(strip_tags(@$event["description"]), 0, 130, "...");
        $controller->pageTitle = ucfirst($controller->module->id)." - ".Yii::t("event","Event's informations")." ".$controller->title;

        $contentKeyBase = $controller->id.".".$controller->action->id;
        $images = Document::getListDocumentsURLByContentKey($id, $contentKeyBase, Document::DOC_TYPE_IMAGE);

        $organizer = array();

        $people = array();
        //$admins = array();
        $attending =array();
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
                $organization = Organization::getBYId($id);
                $organizer["id"] = $id;
                $organizer["type"] = "organization";
                $organizer["name"] = $organization["name"];
              }
            }else if(isset($event["links"]["creator"])){
              foreach ($event["links"]["creator"] as $id => $e) {
                $citoyen = Person::getBYId($id);
                $organizer["id"] = $id;
                $organizer["type"] = "person";
                $organizer["name"] = $citoyen["name"];
              }
            }
          }
        }

        if(isset($event["_id"]) && isset(Yii::app()->session["userId"]) && Link::isLinked($event["_id"] , Event::COLLECTION , Yii::app()->session['userId']))
			$controller->toolbarMBZ = array("<li id='linkBtns'><a href='javascript:;' class='disconnectBtn text-red tooltips' data-name='".$event["name"]."' data-id='".$event["_id"]."' data-type='".Event::COLLECTION."' data-member-id='".Yii::app()->session["userId"]."' data-ownerlink='".Link::person2events."' data-targetlink='".Link::event2person."' data-placement='top' data-original-title='No more Attendee' ><i class='disconnectBtnIcon fa fa-unlink'></i>NO ATTENDING</a></li>" );
		else
			$controller->toolbarMBZ = array("<li id='linkBtns'><a href='javascript:;' class='connectBtn tooltips ' id='addKnowsRelation' data-placement='top' data-ownerlink='".Link::person2events."' data-targetlink='".Link::event2person."' data-original-title='I know this person' ><i class=' connectBtnIcon fa fa-link '></i>ATTENDING</a></li>");


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
