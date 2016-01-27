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
          if(!empty($project)){
            // Get people or orga who contribute to the project 
            // Get image for each contributors                                                                                                                                                                               
            if(isset($project["links"])){
              foreach ($project["links"]["contributors"] as $uid => $e) {
                if($e["type"]== Organization::COLLECTION){
                  $organization = Organization::getPublicData($uid);
                  if (!empty($organization)) {
	                   if(@$e["isAdmin"] && $e["isAdmin"]==1)
                    	$organization["isAdmin"]= $e["isAdmin"];

                    array_push($organizations, $organization);
                    $organization["type"]="organization";
                    $profil = Document::getLastImageByKey($uid, Organization::COLLECTION, Document::IMG_PROFIL);
                    if($profil !="")
                    $organization["imagePath"]= $profil;
                    if(@$e["isAdmin"] && $e["isAdmin"]==1)
                    	$organization["isAdmin"]= $e["isAdmin"];
                    array_push($contributors, $organization);
                  }
                }else if($e["type"]== Person::COLLECTION){
                  $citoyen = Person::getSimpleUserById($uid);
                  if(!empty($citoyen)){
	                   if(@$e["isAdmin"] && $e["isAdmin"]==1)
                    		$citoyen["isAdmin"]= $e["isAdmin"];
						if(@$e["isAdminPending"]){
							$citoyen["isAdminPending"]=$e["isAdminPending"];
            			} 
            			if(@$citoyen["roles"]["toBeValidated"]){
							$citoyen["toBeValidated"]=$citoyen["roles"]["toBeValidated"];
            			} 
                    array_push($people, $citoyen);
                    $citoyen["type"]="citoyen";
                    $profil = Document::getLastImageByKey($uid, Person::COLLECTION, Document::IMG_PROFIL);
                    if($profil !="")
                    	$citoyen["imagePath"]= $profil;
                    array_push($contributors, $citoyen);
                    if( $uid == Yii::app()->session['userId'] )
                      Menu::add2MBZ( array('position' => 'right', 
                                    'tooltip' => Yii::t("common", "Send a message to this Project"), 
                                    "label" => Yii::t("common", "Contact"), 
                                    "iconClass"=>"fa fa-envelope-o",
                                    "href"=>"<a href='#' class='new-news tooltips btn btn-default' data-id='".$id."' data-type='".Project::COLLECTION."' data-name='".$project['name']."'") );
                  }
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
        if(isset($project["_id"]) && isset(Yii::app()->session["userId"]) && Link::isLinked($project["_id"] , Project::COLLECTION , Yii::app()->session['userId']))
            $htmlFollowBtn = array('position' => 'right',
                                   'tooltip' => Yii::t("common", "Stop contributing to this Project"), 
                                   "parent"=>"span",
                                   "parentId"=>"linkBtns",
                                   "label"=>Yii::t("common", "Stop contributing"), 
                                   "iconClass"=>"disconnectBtnIcon fa fa-unlink",
                                   "href"=>"<a href='javascript:;' class='disconnectBtn text-red tooltips btn btn-default' data-name='".$project["name"]."' data-id='".$project["_id"]."' data-type='".Project::COLLECTION."' data-member-id='".Yii::app()->session["userId"]."' data-ownerlink='".Link::person2projects."' data-targetlink='".Link::project2person."'");
        else
            $htmlFollowBtn = array('position' => 'right',
                                   'tooltip' => Yii::t("common", "I want to contribute to this Project"), 
                                   "parent"=>"span",
                                   "parentId"=>"linkBtns",
                                   "label"=>Yii::t("common", "Start contributing"),
                                   "iconClass"=>"connectBtnIcon fa fa-link",
                                   "href"=>"<a href='javascript:;' class='connectBtn tooltips btn btn-default' id='addKnowsRelation' data-ownerlink='".Link::person2projects."' data-targetlink='".Link::project2person."'");
        Menu::add2MBZ($htmlFollowBtn);

        $params["organizations"] = $organizations;
        $params["events"] = $events;
        $params["people"] = $people;
        $params["contributors"] = $contributors;

        $page = "../default/directory";
        if( isset($_GET[ "tpl" ]) )
          $page = "../default/".$_GET[ "tpl" ];
        if(Yii::app()->request->isAjaxRequest){
            echo $controller->renderPartial($page,$params,true);
        }
        else {
            $controller->render($page,$params);
        }
    }
}
