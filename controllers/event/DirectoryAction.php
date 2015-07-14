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

          $controller->title = ((isset($event["name"])) ? $event["name"] : "")."'s Directory";
          $controller->subTitle = (isset($event["description"])) ? $event["description"] : "";
          $controller->pageTitle = ucfirst($controller->module->id)." - ".$controller->title;

          $params = array(
            "events" => array(),
            "people" => array(),
            "organizations" => array()
            );

         
          /* **************************************
          *  PEOPLE
          ***************************************** */
       $people = array();
        //$admins = array();
        if(!empty($event)){
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
                $organization = Organization::getBYId($id);
                array_push($params["organizations"], $organization);
              }
            }else if(isset($event["links"]["creator"])){
              foreach ($event["links"]["creator"] as $id => $e) {
                $citoyen = Person::getBYId($id);
                array_push($params["people"], $citoyen);
              }
            }
          }
        }

          /* **************************************
          *  PROJECTS
          ***************************************** */
        /*$projects = array();
        if(isset($organization["links"]["projects"])){
            foreach ($organization["links"]["projects"] as $key => $value) {
              $project = Project::getPublicData($key);
              array_push( $params["projects"], $project );
            }
        }*/

            

      $page = "../default/directory";

        if(Yii::app()->request->isAjaxRequest){
            echo $controller->renderPartial($page,$params,true);
        }
        else {
            $controller->render($page,$params);
        }
    }
}
