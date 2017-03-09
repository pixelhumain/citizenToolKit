<?php 
 /**
  * Display the directory of back office
  * @param String $id Not mandatory : if specify, look for the person with this Id. 
  * Else will get the id of the person logged
  * @return type
  */
class DirectoryAction extends CAction
{
    public function run( $id=null )
    {
        $controller = $this->getController();

      //get The person Id
      if (empty($id)) {
          if ( empty( Yii::app()->session["userId"] ) ) {
              $controller->redirect(Yii::app()->homeUrl);
          } else {
              $id = Yii::app()->session["userId"];
          }
      }

      /* **************************************
      *  PERSON
      ***************************************** */
      $person = Person::getPublicData($id);

      $controller->title = "Admin Directory Restricted Zone";
      $controller->subTitle = "This is a restricted zone";
      $controller->pageTitle = ucfirst($controller->module->id)." - ".$controller->title;

      /* **************************************
      *  EVENTS
      ***************************************** */
      $events = array();
      /*Authorisation::listEventsIamAdminOf($id);
      $eventsAttending = Event::listEventAttending($id);
      foreach ($eventsAttending as $key => $value) {
        $eventId = (string)$value["_id"];
        if(!isset($events[$eventId])){
          $events[$eventId] = $value;
        }
      }*/

      //TODO - SBAR : Pour le dashboard person, affiche t-on les événements des associations dont je suis memebre ?
      //Get the organization where i am member of;

      /* **************************************
      *  ORGANIZATIONS
      ***************************************** */
      $organizations = Organization::getWhere(array());

      /* **************************************
      *  PEOPLE
      ***************************************** */
      //$people = Person::getWhere(array( "roles.tobeactivated"=> array('$exists'=>1)));
      $people = Person::getWhere(array( "roles"=> array('$exists'=>1)));

      /* **************************************
      *  PROJECTS
      ***************************************** */
      $projects = array();
      /*if(isset($person["links"]["projects"])){
        foreach ($person["links"]["projects"] as $key => $value) {
          $project = Project::getPublicData($key);
          array_push( $projects, $project );
        }
      }*/

      $params["organizations"] = $organizations;
      $params["projects"] = $projects;
      $params["events"] = $events;
      $params["people"] = $people;
      $params["path"] = "../default/";

		  $page = $params["path"]."directoryTable";

      if(Yii::app()->request->isAjaxRequest){
        echo $controller->renderPartial($page,$params,true);
      }
      else {
        $controller->render($page,$params);
      }
    }
}
