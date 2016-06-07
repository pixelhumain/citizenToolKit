<?php 
 /**
  * Display the dashboard of the person
  * @param String $id Not mandatory : if specify, look for the person with this Id. 
  * Else will get the id of the person logged
  * @return type
  */
class DirectoryAction extends CAction
{
    public function run( $id=null )
    {
        $controller = $this->getController();


          /* **************************************
          *  PERSON
          ***************************************** */
          $event = Event::getPublicData($id);
          Menu::event($event,true);
          $params = array(
            "event" => $event,
            "type" => Event::CONTROLLER,
            "events" => array(),
            "people" => array(),
            "organizations" => array()
            );

         
        //$admins = array();
        if(!empty($event)){

          $params["subEvents"] = PHDB::find(Event::COLLECTION,array( "parentId" => $id));
          if(@$params["subEvents"]){
            foreach ($params["subEvents"] as $key => $value) {
              array_push($params["events"], $value);
              if( @$value["links"]["organizer"] )
              {
                foreach ($value["links"]["organizer"] as $organiserId => $organiser) {
                  if( @$organiser["type"] == Organization::COLLECTION )
                    array_push($params["organizations"], Element::getInfos( $organiser["type"], $organiserId));
                  else if( @$organiser["type"] == Person::COLLECTION )
                    array_push($params["people"], Element::getInfos( $organiser["type"], $organiserId));
                }
              }
            }
          }

          if(isset($event["links"])){
            foreach ($event["links"]["attendees"] as $id => $e) {

              $citoyen = Person::getPublicData($id);
              if(!empty($citoyen)){
                array_push($params["people"], $citoyen);
              }

              /*if(isset($e["isAdmin"]) && $e["isAdmin"]==true){
                array_push($admins, $e);
              }*/
            }

            if(isset($event["links"]["organizer"])){
              foreach ($event["links"]["organizer"] as $id => $e) {
                $organization = Organization::getById($id);
                array_push($params["organizations"], $organization);
              }
            }
            else if(isset($event["links"]["creator"])){
              foreach ($event["links"]["creator"] as $id => $e) {
                $citoyen = Person::getById($id);
                array_push($params["people"], $citoyen);
              }
            }
          }
        }


            

      $page = "../default/directory";
      if( isset($_GET[ "tpl" ]) )
          $page = "../default/".$_GET[ "tpl" ];
        if(Yii::app()->request->isAjaxRequest){
            echo $controller->renderPartial($page,$params,true);
        }
        else {
            $controller->render($page,$params);
        }
    }
}
