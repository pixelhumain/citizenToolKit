<?php
class DashboardAction extends CAction
{
 	public function run($id){
	  	$controller=$this->getController();
	  	$project = Project::getPublicData($id);
	
	  	$controller->sidebar1 = array(
	      array('label' => "ACCUEIL", "key"=>"home","iconClass"=>"fa fa-home","href"=>"communecter/project/dashboard/id/".$id),
	    );
	
	    $controller->title = (isset($project["name"])) ? $project["name"] : "";

	    if(isset($project["_id"]) && isset(Yii::app()->session["userId"]) && Link::isLinked($project["_id"] , Project::COLLECTION , Yii::app()->session['userId']))
			$htmlFollowBtn = "<li id='linkBtns'><a href='javascript:;' class='disconnectBtn text-red tooltips' data-name='".$project["name"]."' data-id='".$project["_id"]."' data-type='".Project::COLLECTION."' data-member-id='".Yii::app()->session["userId"]."' data-ownerlink='".Link::person2projects."' data-targetlink='".Link::project2person."' data-placement='top' data-original-title='No more Attendee' ><i class='disconnectBtnIcon fa fa-unlink'></i>UNCONTRIBUTE</a></li>";
		else
			$htmlFollowBtn = "<li id='linkBtns'><a href='javascript:;' class='connectBtn tooltips ' id='addKnowsRelation' data-placement='top' data-ownerlink='".Link::person2projects."' data-targetlink='".Link::project2person."' data-original-title='I know this person' ><i class=' connectBtnIcon fa fa-link '></i>CONTRIBUTE</a></li>";

		$roomCount = PHDB::count(ActionRoom::COLLECTION, array("parentType"=>Project::COLLECTION , "parentId"=>$id));
	    $controller->toolbarMBZ = array(
	    	"<a href='".Yii::app()->createUrl("/".$controller->module->id."/news/index/type/projects/id/".$id)."'><i class='fa fa-rss fa-2x'></i>TIMELINE</a>",
	    	"<a href='".Yii::app()->createUrl("/".$controller->module->id."/rooms/index/type/projects/id/".$id)."'><span class='badge badge-danger animated bounceIn'>".$roomCount."</span><i class='fa fa-comments-o fa-2x'></i>DISCUSS</a>",
	    	$htmlFollowBtn
	    	);
	    
	    $controller->subTitle = ( isset($project["description"])) ? ( ( strlen( $project["description"] ) > 120 ) ? substr($project["description"], 0, 120)."..." : $project["description"]) : "";
	    $controller->pageTitle = "Communecter - Informations sur le projet ".$controller->title;
	
		
	  	$organizations = array();
	  	$people = array();
	  	//$admins = array();
	  	$contributors =array();
	  	$properties = array();
	  	$tasks = array();
	  	$contentKeyBase = $controller->id.".".$controller->action->id; 
	  	$images = Document::getListDocumentsURLByContentKey($id, $contentKeyBase, Document::DOC_TYPE_IMAGE);
	  	if(!empty($project)){
	  		$params = array();
	  		if(isset($project["links"])){
	  			foreach ($project["links"]["contributors"] as $id => $e) {
	  				if($e["type"]== Organization::COLLECTION){
	  					$organization = Organization::getPublicData($id);
	  					if (!empty($organization)) {
	  						array_push($organizations, $organization);
	  						$organization["type"]="organization";
	  						array_push($contributors, $organization);
	  					}
	  				}else if($e["type"]== Person::COLLECTION){
	  					$citoyen = Person::getPublicData($id);
	  					if(!empty($citoyen)){
	  						array_push($people, $citoyen);
	  						$citoyen["type"]="citoyen";
	  						array_push($contributors, $citoyen);
	  					}
	  				}
	  			}
	  		}
	  		// Properties defines the chart of the Project
	  		if (isset($project["properties"]["chart"])){
		  		$properties=$project["properties"]["chart"];
	  		}
	  		//Tasks will provide the GANTT of the project
	  		if (isset($project["tasks"])){
		  		$tasks=$project["tasks"];
	  		}
	  	}
	  	//Gestion de l'admin - true or false
	  	$isProjectAdmin= false;
    	if(isset($project["_id"]) && isset(Yii::app()->session["userId"])) {
    		$isProjectAdmin =  Authorisation::isProjectAdmin((String) $project["_id"],Yii::app()->session["userId"]);
		}

	  	$lists = Lists::get(array("organisationTypes"));
	  	$params["countries"] = OpenData::getCountriesList();
	  	$params["tags"] = Tags::getActiveTags();
		$params["organizationTypes"] = $lists["organisationTypes"];
	  	$params["images"] = $images;
	  	$params["contentKeyBase"] = $contentKeyBase;
	  	$params["contributors"] = $contributors;
	  	$params["project"] = $project;
	  	$params["organizations"] = $organizations;
	  	$params["people"] = $people;
	  	$params["properties"] = $properties;
	  	$params["tasks"]=$tasks;
	  	$params["admin"]=$isProjectAdmin;
	  	//$params["admins"] = $admins;
	  	$controller->render( "dashboard", $params );
	}
}