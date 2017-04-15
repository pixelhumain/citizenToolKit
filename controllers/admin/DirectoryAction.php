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
      //$person = Person::getPublicData($id);

      $superAdmin = Role::isSuperAdmin(Role::getRolesUserId($id)) ;

      /* **************************************
      *  EVENTS
      ***************************************** */
      $events = array();
      

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

      $params["organizations"] = $organizations;
      $params["projects"] = $projects;
      $params["events"] = $events;
      $params["people"] = $people;
      $params["people"] = $people;
      $params["superAdmin"] = $superAdmin ;
      //$params["path"] = "../default/";

		  $page = "directoryTable";

      if(Yii::app()->request->isAjaxRequest){
        echo $controller->renderPartial($page,$params,true);
      }
      else {
        $controller->render($page,$params);
      }
    }
}
