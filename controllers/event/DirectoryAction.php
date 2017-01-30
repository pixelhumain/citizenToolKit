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
          
          $params = array(
            "event" => $event,
            "type" => Event::CONTROLLER,
            "attendees" => array(),
            "guests" => array(),
            );

         
        //$admins = array();
        if(!empty($event)){

          /*$params["subEvents"] = PHDB::find(Event::COLLECTION,array( "parentId" => $id));
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
          }*/

          if(@$event["links"]["attendees"]){
            foreach ($event["links"]["attendees"] as $id => $e) {

              $citoyen = Person::getSimpleUserById($id);
              if(!empty($citoyen)){
                if(@$event["links"]["attendees"][$id]["invitorId"])  
                	array_push($params["guests"], $citoyen);
                else{
                  if(@$e["isAdmin"]){
                    if(@$e["isAdminPending"])
                      $citoyen["isAdminPending"]=true;
                    $citoyen["isAdmin"]=true;         
                  }
                  array_push($params["attendees"], $citoyen);
                }
                
              }
              /*if(isset($e["isAdmin"]) && $e["isAdmin"]==true){
                array_push($admins, $e);
              }*/
            }

           /* if(isset($event["links"]["organizer"])){
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
            }*/
          }
        }


            

      $page = "../default/directory";
      if( isset($_GET[ "tpl" ]) )
          $page = "../default/".$_GET[ "tpl" ];
        if(Yii::app()->request->isAjaxRequest){
          if(@$_GET[ "tpl" ] == "json"){
            $context = array("name"=>$params["event"]["name"]);
            unset($params["event"]);
            foreach ($params as $key => $value) {
              if(!is_array($value))
                unset($params[$key]);
            }
            echo Rest::json( array( "list" => $params,"context"=>$context) );
          }
          else
            echo $controller->renderPartial($page,$params,true);
        }
        else {
            $controller->render($page,$params);
        }
    }
}
