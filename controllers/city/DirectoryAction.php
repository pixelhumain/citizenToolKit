<?php 
 /**
  * Display the dashboard of the person
  * @param String $id Not mandatory : if specify, look for the person with this Id. 
  * Else will get the id of the person logged
  * @return type
  */
class DirectoryAction extends CAction
{
    public function run( $insee=null )
    {
      $controller=$this->getController();


      $city = PHDB::findOne( City::COLLECTION , array( "insee" => $insee ) );
      $name = (isset($city["name"])) ? $city["name"] : "";
      $name2 = (isset($city["alternateName"])) ? $city["alternateName"] : "";
      $name .= ", ".$name2; 
      $controller->title = ( (!empty($name)) ? $name : "City : ".$insee)."'s Directory";
      $controller->subTitle = (isset($city["description"])) ? $city["description"] : "";
      $controller->pageTitle = ucfirst($controller->module->id)." - ".$controller->title;
      

      $projectsBd = PHDB::find(Project::COLLECTION, array( "address.codeInsee" => $insee ) );
      $projects = array();
      foreach ($projectsBd as $key => $project) {
          $project = Project::getPublicData((string)$project["_id"]);
          array_push($projects, $project);
      }
      
      $eventsBd = PHDB::find(Event::COLLECTION, array( "address.codeInsee" => $insee ) );
      $events = array();
      foreach ($eventsBd as $key => $event) {
          $event = Event::getPublicData((string)$event["_id"]);
          array_push($events, $event);
      }
      
      $organizationsBd = PHDB::find(Organization::COLLECTION, array( "address.codeInsee" => $insee ) );
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
      $people = PHDB::find(Person::COLLECTION, array( "address.codeInsee" => $insee ) );
      
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
