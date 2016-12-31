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
          $project = Project::getPublicData($id);

          $controller->title = ((isset($project["name"])) ? $project["name"] : "")."'s Directory";
          $controller->subTitle = (isset($project["description"])) ? $project["description"] : "";
          $controller->pageTitle = ucfirst($controller->module->id)." - ".$controller->title;
          Menu::project($project);

          $params = array(
            "project" => $project,
            "type" => Project::CONTROLLER,
            "organizations" => array(),
            "events" => array(),
            "people" => array(),
            "projects" => array()
            );
          $organizations = array();
            $people = array();
            $contributors =array();
            $events =array();
            $followers = array();
          if(!empty($project)){
            // Get people or orga who contribute to the project 
            // Get image for each contributors                                                                                                                                                                               
            if(isset($project["links"])){
				if (isset($project["links"]["contributors"])){
					foreach ($project["links"]["contributors"] as $uid => $e) {
						if($e["type"]== Organization::COLLECTION){
						  $organization = Organization::getPublicData($uid);
						  if (!empty($organization)) {
						    if(@$e["isAdmin"] && $e["isAdmin"]==1)
						    	$organization["isAdmin"]= $e["isAdmin"];
						    if (!@$organization["disabled"]) {
						      array_push($organizations, $organization);
                }
						    $organization["type"]="organization";
						    if(@$e["isAdmin"] && $e["isAdmin"]==1)
						    	$organization["isAdmin"]= $e["isAdmin"];
                if (!@$organization["disabled"]) {
						      array_push($contributors, $organization);
                }
						  }
						}else if($e["type"]== Person::COLLECTION){
						  	$citoyen = Person::getSimpleUserById($uid);
							if(!empty($citoyen)){
							   if(@$e["isAdmin"] && $e["isAdmin"]==1)
									$citoyen["isAdmin"]= $e["isAdmin"];
								if(@$e["isAdminPending"]){
									$citoyen["isAdminPending"]=$e["isAdminPending"];
								} 
								if(@$e["toBeValidated"]){
									$citoyen["toBeValidated"]=$e["toBeValidated"];
								} 
								array_push($people, $citoyen);
								$citoyen["type"]="citoyen";
								array_push($contributors, $citoyen);
							}
						}
					}
				}
            }
            if( isset($project["links"]["followers"])) {
            	foreach ($project["links"]["followers"] as $uid => $e){
    				$citoyen = Person::getSimpleUserById($uid);
					if(!empty($citoyen)){
						$citoyen["type"]="citoyen";
						array_push($followers, $citoyen);
					}
				}
			}
            if( isset($project["links"]["events"])) {
              foreach ($project["links"]["events"] as $key => $event) {
                $event = Event::getById( $key );
                    if (!empty($event)) {
                      array_push($events, $event);
                    }
              }
            }
        }
        
        $params["organizations"] = $organizations;
        $params["events"] = $events;
        $params["people"] = $people;
        $params["contributors"] = $contributors;
		$params["followers"] = $followers;
        $page = "../default/directory";
        if( isset($_GET[ "tpl" ]) )
          $page = "../default/".$_GET[ "tpl" ];
        if(Yii::app()->request->isAjaxRequest){
          if(@$_GET[ "tpl" ] == "json"){
            $context = array("name"=>$params["project"]["name"]);
            unset($params["project"]);
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
