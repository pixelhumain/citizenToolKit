<?php 
 /**
  * Display the dashboard of the person
  * @param String $id Not mandatory : if specify, look for the person with this Id. 
  * Else will get the id of the person logged
  * @return type
  */
class DirectoryAction extends CAction
{

    public function run( $insee=null, $postalCode=null ) {
      $controller=$this->getController();

      /*$where = array( "insee" => $insee );

      if(!empty($postalCode))
        $where["postalCodes.postalCode"] = array('$in' => array($postalCode) ) ; 

      $city = PHDB::findOne( City::COLLECTION , $where);*/
      $city = City::getCityByInseeCp($insee, $postalCode);
      
      //if(isset($city["postalCodes"])) $city["cp"] = $postalCode;
      
      /*$name = (isset($city["name"])) ? $city["name"] : "";
      $name2 = (isset($city["alternateName"])) ? $city["alternateName"] : "";
      $name .= ", ".$name2; */
     
      $name = ((!empty($postalCode)) ? $city["namePc"]." ".Yii::t("common", "town of")." ".$city["name"] : $city["name"]) ; 
            

      $controller->title = ( (!empty($name)) ? $name : "City : ".$insee)."'s Directory";
      $controller->subTitle = (isset($city["description"])) ? $city["description"] : "";
      $controller->pageTitle = ucfirst($controller->module->id)." - ".$controller->title;
      
      $whereGetElement = array( "address.codeInsee" => $insee );

      if(!empty($postalCode))
        $whereGetElement["address.postalCode"] = $postalCode; 

      $projectsBd = PHDB::find(Project::COLLECTION, $whereGetElement);
      $projects = array();
      foreach ($projectsBd as $key => $project) {
          $project = Project::getPublicData((string)$project["_id"]);
          array_push($projects, $project);
      }
      
      $eventsBd = PHDB::find(Event::COLLECTION, $whereGetElement );
      $events = array();
      foreach ($eventsBd as $key => $event) {
          $event = Event::getPublicData((string)$event["_id"]);
          array_push($events, $event);
      }
      
      $organizationsBd = PHDB::find(Organization::COLLECTION, $whereGetElement /*array( "address.codeInsee" => $insee, "address.postalCode" => $postalCode )*/ );
      $organizations = array();
      foreach ($organizationsBd as $key => $orga) {
          $orga = Organization::getPublicData((string)$orga["_id"]);
          $profil = Document::getLastImageByKey((string)$orga["_id"], Organization::COLLECTION, Document::IMG_PROFIL);
          if($profil !="")
              $orga["imagePath"]= $profil;
          if (!@$orga["disabled"]) {
            array_push($organizations, $orga);
          }
      }
      
      $allPeople = array();
      $people = PHDB::find(Person::COLLECTION, $whereGetElement /*array( "address.codeInsee" => $insee/*, "address.postalCode" => $postalCode )*/ );
      
     // if( isset($person["links"]) && isset($person["links"]["knows"])) {
          foreach ($people as $key => $onePerson) {
              $citoyen = Person::getPublicData( $key );
              $profil = Document::getLastImageByKey($key, Person::COLLECTION, Document::IMG_PROFIL);
              if($profil !="")
                 $citoyen["imagePath"]= $profil;
              array_push($allPeople, $citoyen);
              
          }
      /*
  		$where = array("address.codeInsee"=>$insee);
  		$params["events"] = Event::getWhere( $where );
  		$params["organizations"] = Organization::getWhere( $where );
  		$params["people"] = Person::getWhere( $where );
      $params["projects"] = Project::getWhere( $where );
      $params["type"] = City::CONTROLLER;
      $params["city"] = $city;
      */
      $params["organizations"] = $organizations;
      $params["projects"] = $projects;
      $params["events"] = $events;
      $params["people"] = $allPeople;
      $params["type"] = City::CONTROLLER;
      $params["city"] = $city;
       if(!empty($postalCode))
            $params["cityGlobal"] = false;
        else
            $params["cityGlobal"] = true;

      $page = "../default/directory";
        if( isset($_GET[ "tpl" ]) )
          $page = "../default/".$_GET[ "tpl" ];
        if(Yii::app()->request->isAjaxRequest){
            echo $controller->renderPartial($page,$params,true);
        } else {
            $controller->render($page,$params);
        }
    }
}
