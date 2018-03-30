<?php 
class Admin {

	public static function directory($id = null, $tpl=null, $view=null){
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

      $superAdmin = Role::isSuperAdmin(Role::getRolesUserId($id)) ;
      $limitMin=0;
      $stepLim=100;
      if(@$_POST["page"]){
        $limitMin=$limitMin+(100*$_POST["page"]);
      }
      $searchLocality = isset($_POST['locality']) ? $_POST['locality'] : null;
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
        if(@$_POST["initType"]){
          $params["typeDirectory"]=$_POST["initType"];
          foreach($_POST["initType"] as $data){
            $params["results"][$data] = PHDB::findAndLimitAndIndex ( $data , $query, $stepLim, $limitMin);
            $params["results"]["count"][$data] = PHDB::count( $data , $query);
          }
          if($tpl!="json" || $search != ""){
            foreach($_POST["initType"] as $data){
            $params["results"]["count"][$data] = PHDB::count( $data , $query);
            }
          }
        }else{
          $params["typeDirectory"]=[Person::COLLECTION,Project::COLLECTION,Organization::COLLECTION,Event::COLLECTION];
          if(!@$_POST["type"] || $_POST["type"]==Person::COLLECTION){
            $params["results"][Person::COLLECTION] = PHDB::findAndLimitAndIndex ( Person::COLLECTION , $query, $stepLim, $limitMin);
            $params["results"]["count"]["citoyens"] = PHDB::count( Person::COLLECTION , $query);
          }
          else if(@$_POST["type"]){
            $params["results"][$_POST["type"]] = PHDB::findAndLimitAndIndex ( $_POST["type"] , $query, $stepLim, $limitMin);
            $params["results"]["count"][$_POST["type"]] = PHDB::count( $_POST["type"] , $query);
          }
          if($tpl!="json" || $search != ""){
            $params["results"]["count"]["citoyens"] = PHDB::count( Person::COLLECTION , $query);
            $params["results"]["count"]["organizations"] = PHDB::count( Organization::COLLECTION , $query);
            $params["results"]["count"]["events"] = PHDB::count( Event::COLLECTION , $query);
            $params["results"]["count"]["projects"] = PHDB::count( Project::COLLECTION , $query);
        }
      }
		$params["results"]["superAdmin"] = $superAdmin ;
		return $params ;
	}
}