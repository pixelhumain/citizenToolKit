<?php
class EditAction extends CAction
{
    public function run($id) {
		$controller=$this->getController();
		$project = Project::getById($id);
        $citoyens = array();
		$organizations = array();
		if (isset($project['links']["contributors"]) && !empty($project['links']["contributors"])) 
		{
		  foreach ($project['links']["contributors"] as $id => $e) 
		  {
		  	
		  	if (!empty($project)) {
		  		if($e["type"] == "citoyens"){
		  			$citoyen = PHDB::findOne( Person::COLLECTION, array( "_id" => new MongoId($id)));
		  			array_push($citoyens, $citoyen);
		  		}else if($e["type"] == "organizations"){
		      		$organization = PHDB::findOne( Organization::COLLECTION, array( "_id" => new MongoId($id)));
		      		array_push($organizations, $organization);
		  		}
		    } else {
		     // throw new CommunecterException("Données inconsistentes pour le citoyen : ".Yii::app()->session["userId"]);
		    }  	
		  }
		}
        $controller->render("edit",array('project'=>$project, 'organizations'=>$organizations, 'citoyens'=>$citoyens));
	}
}