<?php 
 /**
  * Display the dashboard of the person
  * @param String $id Not mandatory : if specify, look for the person with this Id. 
  * Else will get the id of the person logged
  * @return type
  */
class DirectoryAction extends CAction {

    public function run( $id=null ) {
      $controller = $this->getController();

      //get The person Id
      if (empty($id)) {
          if ( empty( Yii::app()->session["userId"] ) ) {
              $controller->redirect(Yii::app()->homeUrl);
          } else {
              $id = Yii::app()->session["userId"];
          }
      }

      /***************************************
      *  PERSON
      ***************************************** */
      $person = Person::getPublicData($id);

      $controller->title = ((isset($person["name"])) ? $person["name"] : "")."'s Directory";
      $controller->subTitle = (isset($person["description"])) ? $person["description"] : "";
      $controller->pageTitle = ucfirst($controller->module->id)." - ".$controller->title;

      /***************************************
      *  EVENTS
      ******************************************/
      $events=array();
      if(isset($person["links"]["events"]))
        {
              foreach ($person["links"]["events"] as $keyEv => $valueEv) 
              {
                $event = Event::getPublicData($keyEv);
                if(!empty($event)) {
                  $events[$keyEv] = $event; 
                }
              }
       }

      /* **************************************
      *  ORGANIZATIONS
      ***************************************** */
      $organizations = array();
      if( isset($person["links"]) && isset($person["links"]["memberOf"])) {
        foreach ($person["links"]["memberOf"] as $key => $member) {
          $organization;
          if( $member['type'] == Organization::COLLECTION ) {
              $organization = Organization::getPublicData( $key );
              if(!empty($organization)) {
                if (!@$organization["disabled"]) {
                  array_push($organizations, $organization );
                }
              }
          }
         
          if(isset($organization["links"]["events"])) {
            foreach ($organization["links"]["events"] as $keyEv => $valueEv) {
              $event = Event::getPublicData($keyEv);
              if(!empty($event)) {
                $events[$keyEv] = $event; 
              }
            }
          }
        }        
      }

      /* **************************************
      *  PROJECTS
      ***************************************** */
      $projects = array();
      if(isset($person["links"]["projects"])){
        foreach ($person["links"]["projects"] as $key => $value) {
          $project = Project::getPublicData($key);
          if(!empty($project)) {
            array_push( $projects, $project );
          }
        }
      }

       /* **************************************
      *  FOLLOWS
      ***************************************** */
      $follows = 
            array("citoyens"=>array(),
      					"projects"=>array(),
      					"organizations"=>array(),
      					"count" => 0
      			);

      $countFollows=0;

      if (@$person["links"]["follows"]) {
        foreach ( @$person["links"]["follows"] as $key => $member ) {
          if( $member['type'] == Person::COLLECTION ) {
            $citoyen = Person::getPublicData( $key );
  	        if(!empty($citoyen)) {
              array_push( $follows[Person::COLLECTION], $citoyen );
            }
          }
          if( $member['type'] == Organization::COLLECTION ) {
            $organization = Organization::getPublicData($key);
  		      if(!empty($organization)) {
              array_push($follows[Organization::COLLECTION], $organization );
            }
          }
          if( $member['type'] == Project::COLLECTION ) {
  		      $project = Project::getPublicData($key);
  		      if(!empty($project)) {
              array_push( $follows[Project::COLLECTION], $project );
            }
          }
          $countFollows++;
        }
      }
			
      $follows["count"]= $countFollows;
      
      /* **************************************
      *  FOLLOWERS
      ***************************************** */
      $followers = array();
      if (@$person["links"]["followers"]) {
  	    foreach ( @$person["links"]["followers"] as $key => $member ) {
          if( $member['type'] == Person::COLLECTION ) {
            $citoyen = Person::getPublicData( $key );
            if(!empty($citoyen)){
              array_push( $followers, $citoyen );
            }
          }
      	}
      }

      if(@$_GET[ "tpl" ] == "json"){
        if (@$follows[Organization::COLLECTION])
          $organizations = array_merge($organizations,$follows[Organization::COLLECTION]);
        uasort($organizations, array("DirectoryAction", 'compareByName'));
        $params["organizations"] = $organizations;

        if (@$follows[Project::COLLECTION])
          $projects = array_merge($projects,$follows[Project::COLLECTION]);
        uasort($projects, array("DirectoryAction", 'compareByName'));
        $params["projects"] = $projects;

        uasort($events, array("DirectoryAction", 'compareByName'));
        $params["events"] = $events;

        if (@$follows[Person::COLLECTION]){
          uasort($follows[Person::COLLECTION], array("DirectoryAction", 'compareByName'));
          $params[ "citoyens" ] = $follows[ Person::COLLECTION ];
        }
      } else {
        uasort($organizations, array("DirectoryAction", 'compareByName'));
        $params["organizations"] = $organizations;
        uasort($projects, array("DirectoryAction", 'compareByName'));
        $params["projects"] = $projects;
        uasort($events, array("DirectoryAction", 'compareByName'));
        $params["events"] = $events;
        $params["type"] = Person::CONTROLLER;
        $params["person"] = $person;
        uasort($followers, array("DirectoryAction", 'compareByName'));
        $params["followers"] = $followers;

        //Sort Follows
        if (@$follows[Person::COLLECTION])
          uasort($follows[Person::COLLECTION], array("DirectoryAction", 'compareByName'));
        if (@$follows[Organization::COLLECTION])
          uasort($follows[Organization::COLLECTION], array("DirectoryAction", 'compareByName'));
        if (@$follows[Project::COLLECTION])
          uasort($follows[Project::COLLECTION], array("DirectoryAction", 'compareByName'));
        $params["follows"] = $follows;
      }
		  $page = "../default/directory";
      if( isset($_GET[ "tpl" ]) )
        $page = "../default/".$_GET[ "tpl" ];
      
      if(Yii::app()->request->isAjaxRequest) {
        if(@$_GET[ "tpl" ] == "json"){
          $context = array("name"=>$person["name"]);
          echo Rest::json( array( "list" => $params,"context"=>$context) );
        }
        else
          echo $controller->renderPartial($page,$params,true);
      } else {
        $controller->render($page,$params);
      }
    }

    // Fonction de comparaison
    public static function compareByName($entityA, $entityB) {
        return strcasecmp(@$entityA["name"],@$entityB["name"]);
    }
}


