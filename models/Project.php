<?php 
class Project {

	const COLLECTION = projects;
	/**
	 * get an project By Id
	 * @param type $id : is the mongoId of the project
	 * @return type
	 */
	public static function getById($id) {
	  	return PHDB::findOne( PHType::TYPE_PROJECTS,array("_id"=>new MongoId($id)));
	}
	
	/**
	 * Get an project from an id and return filter data in order to return only public data
	 * @param type $id 
	 * @return project structure
	 */
	public static function getPublicData($id) {
		//Public datas 
		$publicData = array (
		);

		//TODO SBAR = filter data to retrieve only publi data	
		$project = Project::getById($id);
		if (empty($project)) {
			//throw new CommunecterException("The project id is unknown ! Check your URL");
		}

		return $project;
	}

	public static function saveProject($params){
		$id = Yii::app()->session["userId"];
	    $type = PHType::TYPE_CITOYEN;
	    $newProject = array(
			"name" => $params['title'],
			'url' => $params['url'],
			//'version' => $params['version'],
			'licence' => $params['licence'],
			'description' => $params['description'],
			'startDate' => $params['start'],
			'endDate' => $params['end'],
			'created' => time(),
			"links" => array( 
				"contributors" => array( (string)$id =>array("type" => $type,"isAdmin" => true)), 
			),
			"properties" => array (
							"gouvernance" => $params["gouvernance"],
							"local" => $params["local"],	
							"partenaire" => $params["partenaire"],
							"partage" => $params["partage"],
							"solidaire" => $params["solidaire"],
							"avancement" => $params["avancement"],
			),
	    );
	    if(!empty($params['postalCode'])) {
			if (!empty($params['city'])) {
				$insee = $params['city'];
				$address = SIG::getAdressSchemaLikeByCodeInsee($insee);
				$newProject["address"] = $address;
				$newProject["geo"] = SIG::getGeoPositionByInseeCode($insee);
			}
		}
	    PHDB::insert(PHType::TYPE_PROJECTS,$newProject);
	    Link::connect($id, $type, $newProject["_id"], PHType::TYPE_PROJECTS, $id, "projects" );
	    return array("result"=>true, "msg"=>"Votre projet est communecté.", "id"=>$newProject["_id"]);	
	}
	public static function removeProject($projectId) {
		$id = Yii::app()->session["userId"];
	    $type = PHType::TYPE_CITOYEN;
	    //0. Remove the links
        Link::disconnect($id, $type, $projectId, PHType::TYPE_PROJECTS, $id, "projects" );
        //1. Remove project's sheet corresponding to $projectId _id
        PHDB::remove(PHType::TYPE_PROJECTS,array("_id" => new MongoId($projectId)));
        return array("result"=>true, "msg"=>"The project has been removed with success", "projectid"=>$projectId);
    }
    public static function saveChart($properties){
	    //print_r($properties);
	    //$member["_id"], $memberType, Yii::app()->session["userId"],
	    $propertiesList=array(
							"gouvernance" => $properties["gouvernance"],
							"local" => $properties["local"],	
							"partenaire" => $properties["partenaire"],
							"partage" => $properties["partage"],
							"solidaire" => $properties["solidaire"],
							"avancement" => $properties["avancement"],
		);
	    PHDB::update(PHType::TYPE_PROJECTS,
			array("_id" => new MongoId($properties["projectID"])),
            array('$set' => array("properties"=> $propertiesList))
        );
        return true;
    }

}
?>