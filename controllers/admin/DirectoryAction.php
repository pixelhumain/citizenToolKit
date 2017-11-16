<?php 
 /**
  * Display the directory of back office
  * @param String $id Not mandatory : if specify, look for the person with this Id. 
  * Else will get the id of the person logged
  * @return type
  */
class DirectoryAction extends CAction
{
    public function run( $tpl=null )
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
    //  $events = array();
      

      //TODO - SBAR : Pour le dashboard person, affiche t-on les événements des associations dont je suis memebre ?
      //Get the organization where i am member of;

      /* **************************************
      *  ORGANIZATIONS
      ***************************************** */
      //$organizations = Organization::getWhere(array());

      /* **************************************
      *  PEOPLE
      ***************************************** */
      //$people = Person::getWhere(array( "roles.tobeactivated"=> array('$exists'=>1)));
      $limitMin=0;
      $stepLim=100;
      if(@$_POST["page"]){
        $limitMin=$limitMin+(100*$_POST["page"]);
        $stepLim=$stepLim+(100*$_POST["page"]);
      }
      $searchLocality = isset($_POST['locality']) ? $_POST['locality'] : null;
       //$localities = isset($post['localities']) ? $post['localities'] : null;
     // $searchType = isset($post['searchType']) ? $post['searchType'] : null;
      $searchTags = isset($_POST['searchTag']) ? $_POST['searchTag'] : null;
      $country = isset($_POST['country']) ? $_POST['country'] : "";
      $search="";
      if(@$_POST["value"] && !empty($_POST["value"])){
        $search = trim(urldecode($_POST['value']));
      }
      $query = array();
      $query = Search::searchString($search, $query);
      if( /*!empty($searchTags)*/ count($searchTags) > 1  || count($searchTags) == 1 && $searchTags[0] != "" ){
        if( (strcmp($filter, Classified::COLLECTION) != 0 && self::typeWanted(Classified::COLLECTION, $searchType)) ||
          (strcmp($filter, Place::COLLECTION) != 0 && self::typeWanted(Place::COLLECTION, $searchType)) ){
            $queryTags =  Search::searchTags($searchTags, '$all') ;
        }
        else 
          $queryTags =  Search::searchTags($searchTags) ;
        if(!empty($queryTags))
          $query = array('$and' => array( $query , $queryTags) );
      }
      if(!empty($searchLocality))
        $query = Search::searchLocality($searchLocality, $query);
      
       //:::::::::::::://////CITOYENS///////////////////////////////////
        $res = array();
        $allCitoyen = PHDB::findAndLimitAndIndex ( Person::COLLECTION , $query, $stepLim, $limitMin);
        $countAllCitoyen = PHDB::count( Person::COLLECTION , $query);
      ///////////////////////////////END CITOYENS //////////////////////////////////////////////

    //  $people = Person::getWhereByLimit(array( "roles"=> array('$exists'=>1)));
      //$counẗPeople=Person::countByWhere(array( "roles"=> array('$exists'=>1)));
      /* **************************************
      *  PROJECTS
      ***************************************** */
      $projects = array();

      $params["results"]["organizations"] = array();//$organizations;
      $params["results"]["projects"] = array();//$projects;
      $params["results"]["events"] = array();//$events;
      $params["results"]["countPeople"]=$countAllCitoyen;
      $params["results"][Person::COLLECTION] = $allCitoyen;
      //$params["people"] = $people;
      $params["results"]["superAdmin"] = $superAdmin ;
      //$params["path"] = "../default/";

		  $page = "directoryTable";
      if($tpl=="json")
        Rest::json( $params );
      else{
        if(Yii::app()->request->isAjaxRequest){
          echo $controller->renderPartial($page,$params,true);
        }
        else {
          $controller->render($page,$params);
        }
      }
    }
}
