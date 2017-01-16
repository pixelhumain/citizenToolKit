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
          $organization = Organization::getPublicData($id);

          $controller->title = ((isset($organization["name"])) ? $organization["name"] : "")."'s Directory";
          $controller->subTitle = (isset($organization["description"])) ? $organization["description"] : "";
          $controller->pageTitle = ucfirst($controller->module->id)." - ".$controller->title;

          $params = array(
            "organizations" => array(),
            "events" => array(),
            "people" => array(),
            "projects" => array(),
            "followers" => array()
            );

          /* **************************************
          *  EVENTS
          ***************************************** */
        $events = Authorisation::listEventsIamAdminOf($id);
        foreach ($events as $key => $value) {
          $newEvent = Event::getById($key);
          array_push($params["events"], $newEvent);
        }

          /* **************************************
          *  ORGANIZATIONS
          ***************************************** */
        $organizations = Organization::getMembersByOrganizationId($id, Organization::COLLECTION);
        foreach ($organizations as $key => $value) {
        	$newOrga = Organization::getById($key);
          if(@$value["isAdmin"] && $value["isAdmin"]==1){
	            $newOrga["isAdmin"]=$value["isAdmin"];
        	} 
          //Do not add organization if it is disabled
          if (!@$newOrga["disabled"]) {
            array_push($params["organizations"], $newOrga);
          }
        }

        /* **************************************
        *  PEOPLE
        ***************************************** */
        $people = Organization::getMembersByOrganizationId($id, Person::COLLECTION);
        foreach ($people as $key => $value) 
        {
            $newCitoyen = Person::getSimpleUserById($key);
            if(@$value["isAdmin"] && $value["isAdmin"]==1){
	            $newCitoyen["isAdmin"]=$value["isAdmin"];
            } 
      			if(@$value["toBeValidated"]){
	            $newCitoyen["toBeValidated"]=$value["toBeValidated"];
            } 
      			if(@$value["isAdminPending"]){
	            $newCitoyen["isAdminPending"]=$value["isAdminPending"];
            }

            array_push($params["people"], $newCitoyen);
        }
        
		    $followers = Organization::getFollowersByOrganizationId($id);
        foreach ($followers as $key => $value) 
        {
            $newCitoyen = Person::getSimpleUserById($key);
            array_push($params["followers"], $newCitoyen);
        }

        /* **************************************
        *  PROJECTS
        ***************************************** */
        $projects = array();
        if(isset($organization["links"]["projects"])){
            foreach ($organization["links"]["projects"] as $key => $value) {
              $project = Project::getPublicData($key);
              array_push( $params["projects"], $project );
            }
        }

            
        $params["organization"] = $organization;
        $params["type"] = Organization::CONTROLLER;
    	  $page = "../default/directory";
        if( isset($_GET[ "tpl" ]) )
          $page = "../default/".$_GET[ "tpl" ];
        if(Yii::app()->request->isAjaxRequest){
            if(@$_GET[ "tpl" ] == "json"){
              $context = array("name"=>$params["organization"]["name"]);
              unset($params["organization"]);
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
