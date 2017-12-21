<?php 
 /**
  * Display the directory of back office
  * @param String $id Not mandatory : if specify, look for the person with this Id. 
  * Else will get the id of the person logged
  * @return type
  */
class DirectoryAction extends CAction
{
    public function run( $tpl=null, $view=null )
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
        //$stepLim=$stepLim+(100*$_POST["page"]);
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
      $queryNews=array();
      $query = Search::searchString($search, $query);
      $queryNews = Search::searchNewsString($search, $query);
      $queryNews = array('$and' => array( $queryNews , array("type"=>"news","scope.type"=>"public")) );
      //print_r($queryNews);
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
        if(!@$_POST["type"] || $_POST["type"]==Person::COLLECTION){
          $params["results"][Person::COLLECTION] = PHDB::findAndLimitAndIndex ( Person::COLLECTION , $query, $stepLim, $limitMin);
          $params["results"]["count"]["citoyens"] = PHDB::count( Person::COLLECTION , $query);
        }
        else if(@$_POST["type"] && $_POST["type"] != News::COLLECTION){
          $params["results"][$_POST["type"]] = PHDB::findAndLimitAndIndex ( $_POST["type"] , $query, $stepLim, $limitMin);
          $params["results"]["count"][$_POST["type"]] = PHDB::count( $_POST["type"] , $query);
        }else{
          $params["results"][$_POST["type"]] = PHDB::findAndLimitAndIndex ( $_POST["type"] , $queryNews, $stepLim, $limitMin);
          $params["results"]["count"][$_POST["type"]] = PHDB::count( $_POST["type"] , $queryNews);
        }
        if($tpl!="json" || ($search != "" || $search === true)){
          $params["results"]["count"]["citoyens"] = PHDB::count( Person::COLLECTION , $query);
          $params["results"]["count"]["organizations"] = PHDB::count( Organization::COLLECTION , $query);
          $params["results"]["count"]["events"] = PHDB::count( Event::COLLECTION , $query);
          $params["results"]["count"]["projects"] = PHDB::count( Project::COLLECTION , $query);
          $params["results"]["count"]["poi"] = PHDB::count( Poi::COLLECTION , $query);
          $params["results"]["count"]["classified"] = PHDB::count( Classified::COLLECTION , $query);
         // print_r($queryNews);
          $params["results"]["count"]["news"] = PHDB::count( News::COLLECTION , $queryNews);
      }
      ///////////////////////////////END CITOYENS //////////////////////////////////////////////

    //  $people = Person::getWhereByLimit(array( "roles"=> array('$exists'=>1)));
      //$counẗPeople=Person::countByWhere(array( "roles"=> array('$exists'=>1)));
      /* **************************************
      *  PROJECTS
      ***************************************** */
      //$projects = array();

     // $params["results"]["organizations"] = array();//$organizations;
      //$params["results"]["projects"] = array();//$projects;
      //$params["results"]["events"] = array();//$events;
      //$params["results"]["countPeople"]=$countAllCitoyen;
      
      //$params["people"] = $people;
      $params["results"]["superAdmin"] = $superAdmin ;
      //$params["path"] = "../default/";

		  $page = "directoryTable";
      if(@$view && $view=="innovation")
        $page = "territorialSearch";
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
