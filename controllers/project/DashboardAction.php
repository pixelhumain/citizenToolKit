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
	    $controller->toolbarMBZ = array(
	    	"<a href='".Yii::app()->createUrl("/".$controller->module->id."/news/index/type/projects/id/".$id)."'><i class='fa fa-rss fa-2x'></i>TIMELINE</a>",
	    	"<a href='".Yii::app()->createUrl("/".$controller->module->id."/discuss/index/type/projects/id/".$id)."'><i class='fa fa-comments-o fa-2x'></i>DISCUSS</a>"
	    	);
	    
	    $controller->subTitle = (isset($project["description"])) ? $project["description"] : "";
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
	  				}else if($e["type"]== PHType::TYPE_CITOYEN){
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
	  		if (isset($project["properties"])){
		  		$properties=$project["properties"];
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