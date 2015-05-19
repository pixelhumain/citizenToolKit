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
        $controller->subTitle = (isset($event["description"])) ? $event["description"] : "";
        $controller->pageTitle = ucfirst($controller->module->id)." - Informations sur l'evenement ".$controller->title;

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
        
        $params["images"] = $images;
        $params["contentKeyBase"] = $contentKeyBase;
        $params["attending"] = $attending;
        $params["event"] = $event;
        $params["organizer"] = $organizer;
        $params["people"] = $people;
        //$params["admins"] = $admins;
        $controller->render( "dashboard", $params );
    }
}