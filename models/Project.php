<?php 
class Project {

	const COLLECTION = "projects";
	const CONTROLLER = "project";
	const ICON = "fa-lightbulb-o";
	
	//From Post/Form name to database field name
	private static $dataBinding = array(
	    "name" => array("name" => "name", "rules" => array("required")),
	    "address" => array("name" => "address"),
	    "postalCode" => array("name" => "address.postalCode"),
	    "city" => array("name" => "address.codeInsee"),
	    "addressCountry" => array("name" => "address.addressCountry"),
	    "description" => array("name" => "description"),
	    "startDate" => array("name" => "startDate", "rules" => array("projectStartDate")),
	    "endDate" => array("name" => "endDate", "rules" => array("projectEndDate")),
	    "tags" => array("name" => "tags"),
	    "url" => array("name" => "url"),
	    "licence" => array("name" => "licence"),
	    "avancement" => array("name" => "properties.avancement"),
	);

	private static function getCollectionFieldNameAndValidate($projectFieldName, $projectFieldValue, $projectId) {
		return DataValidator::getCollectionFieldNameAndValidate(self::$dataBinding, $projectFieldName, $projectFieldValue, $projectId);
	}

	/**
	 * get an project By Id
	 * @param type $id : is the mongoId of the project
	 * @return type
	 */
	public static function getById($id) {
	  	$project = PHDB::findOne( self::COLLECTION,array("_id"=>new MongoId($id)));
	  	if (!empty($project["startDate"]) && !empty($project["endDate"])) {
			if (gettype($project["startDate"]) == "object" && gettype($project["endDate"]) == "object") {
				//Set TZ to UTC in order to be the same than Mongo
				date_default_timezone_set('UTC');
				$project["startDate"] = date('Y-m-d H:i:s', $project["startDate"]->sec);
				$project["endDate"] = date('Y-m-d H:i:s', $project["endDate"]->sec);	
			} else {
				//Manage old date with string on date project
				$now = time();
				$yesterday = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
				$yester2day = mktime(0, 0, 0, date("m")  , date("d")-2, date("Y"));
				$project["endDate"] = date('Y-m-d H:i:s', $yesterday);
				$project["startDate"] = date('Y-m-d H:i:s',$yester2day);;
			}
		}

		if (!empty($project)) {
			$project = array_merge($project, Document::retrieveAllImagesUrl($id, self::COLLECTION));
		}


	  	return $project;
	}

	/**
	 * Retrieve a simple project (id, name, profilImageUrl) by id from DB
	 * @param String $id of the project
	 * @return array with data id, name, profilImageUrl
	 */
	public static function getSimpleProjectById($id) {
		
		$simpleProject = array();
		$project = PHDB::findOneById( self::COLLECTION ,$id, array("id" => 1, "name" => 1, "address" => 1) );

		$simpleProject["id"] = $id;
		$simpleProject["name"] = @$project["name"];
		$simpleProject = array_merge($simpleProject, Document::retrieveAllImagesUrl($id, self::COLLECTION));
		$simpleProject["address"] = empty($project["address"]) ? array("addressLocality" => "Unknown") : $project["address"];

		return $simpleProject;
	}
	
	//TODO SBAR => should be private ?
	public static function getWhere($params) {
	  	return PHDB::findAndSort( self::COLLECTION, $params, array("created"),null);
	}
	/**
	 * Get an project from an id and return filter data in order to return only public data
	 * @param type $id 
	 * @return project structure
	 */
	public static function getPublicData($id) {
		//Public datas 
		$publicData = array ();

		//TODO SBAR = filter data to retrieve only publi data	
		$project = Project::getById($id);
		if (empty($project)) {
			//throw new CommunecterException("The project id is unknown ! Check your URL");
		}

		return $project;
	}

	/**
	 * Apply project checks and business rules before inserting
	 * @param array $project : array with the data of the project to check
	 * @return array Project well format : ready to be inserted
	 */
	public static function getAndCheckProject($project, $userId) {

		if (empty($project['name'])) {
			throw new CTKException(Yii::t("project","You have to fill a name for your project"));
		}
		
		// Is There a association with the same name ?
	    $projectSameName = PHDB::findOne(self::COLLECTION ,array( "name" => $_POST['name']));
	    if($projectSameName) { 
	      throw new CTKException(Yii::t("project","A project with the same name already exist in the plateform"));
	    }

	    if(empty($project['startDate']) || empty($project['endDate'])) {
			throw new CTKException("The start and end date of an event are required.");
		}

		//The end datetime must be after start datetime
		$startDate = strtotime($project['startDate']);
		$endDate = strtotime($project['endDate']);
		if ($startDate > $endDate) {
			throw new CTKException("The start date must be before the end date.");
		}

		$newProject = array(
			"name" => $project['name'],
			'startDate' => new MongoDate($startDate),
			'endDate' => new MongoDate($endDate),
			'creator' => $userId,
			'created' => new MongoDate(time())
	    );
				  
		if(!empty($project['postalCode'])) {
			if (!empty($project['city'])) {
				$insee = $project['city'];
				$address = SIG::getAdressSchemaLikeByCodeInsee($insee);
				$newProject["address"] = $address;
				//$newProject["geo"] = SIG::getGeoPositionByInseeCode($insee);
			}
		} else {
			throw new CTKException(Yii::t("project","Please fill the postal code of the project to communect it"));
		}

		if(!empty($project['geoPosLatitude']) && !empty($project["geoPosLongitude"])){
			
			$newProject["geo"] = 	array(	"@type"=>"GeoCoordinates",
						"latitude" => $project['geoPosLatitude'],
						"longitude" => $project['geoPosLongitude']);

			$newProject["geoPosition"] = 
				array(	"type"=>"point",
						"coordinates" =>
							array($project['geoPosLatitude'],
					 	  		  $project['geoPosLongitude']));
		}else
		{
			$newProject["geo"] = SIG::getGeoPositionByInseeCode($insee);
		}
		
		//No mandotory fields 
		if (!empty($project['description']))
			$newProject["description"] = $project['description'];
		
		if (!empty($project['url']))
			$newProject["url"] = $project['url'];

		if (!empty($project['licence']))
			$newProject["licence"] = $project['licence'];

		//Tags
		if (isset($project['tags'])) {
			if ( gettype($project['tags']) == "array" ) {
				$tags = $project['tags'];
			} else if ( gettype($project['tags']) == "string" ) {
				$tags = explode(",", $project['tags']);
			}
			$newProject["tags"] = $tags;
		}

		return $newProject;
	}

	/**
	 * Insert a new project, checking if the project is well formated
	 * @param array $params Array with all fields for a project
	 * @param string $userId UserId doing the insertion
	 * @return array as result type
	 */
	public static function insert($params, $parentId,$parentType){
	    if ( $parentType== "citoyen"){
		    $type= Person::COLLECTION;
	    }
	    else {
		    $type= Organization::COLLECTION;
	    }

	    $newProject = self::getAndCheckProject($params, $parentId);
	    if (isset($newProject["tags"]))
			$newProject["tags"] = Tags::filterAndSaveNewTags($newProject["tags"]);

	    // TODO SBAR - If a Link::connect is used why add a link hard coded
	    $newProject["links"] = array( "contributors" => 
	    								array($parentId =>array("type" => $type,"isAdmin" => true)));

	    PHDB::insert(self::COLLECTION,$newProject);

	    Link::connect($parentId, $type, $newProject["_id"], self::COLLECTION, $parentId, "projects", true );

	    Notification::createdObjectAsParam(Person::COLLECTION,Yii::app() -> session["userId"],Project::COLLECTION, $newProject["_id"], $type, $parentId, $newProject["geo"], $newProject["tags"],$newProject["address"]["codeInsee"]);
	    return array("result"=>true, "msg"=>"Votre projet est communecté.", "id" => $newProject["_id"]);	
	}

	public static function removeProject($projectId, $userId) {
	    $type = Person::COLLECTION;

	    if (! Authorisation::canEditItem($userId, self::COLLECTION, $projectId)) {
			throw new CTKException("You can't remove this project : you are not admin of it");
		}

	    //0. Remove the links
        Link::disconnect($userId, $type, $projectId, PHType::TYPE_PROJECTS, $userId, "projects" );
        //1. Remove project's sheet corresponding to $projectId _id
        PHDB::remove(self::COLLECTION,array("_id" => new MongoId($projectId)));

        return array("result"=>true, "msg"=>"The project has been removed with success", "projectid"=>$projectId);
    }
	/*public static function removeTask($projectId,$taskId,$userId){
		//echo $projectId." ".$taskId;
		//PHDB::remove(self::COLLECTION,array("_id" => new MongoId($projectId), "task"=> $taskId));
		$res = PHDB::update( self::COLLECTION, 
                       array("_id" => new MongoId($projectId)) , 
                       array('$unset' => array("tasks.".$taskId => 1)));
        return array("result"=>true, "msg"=>$res);
	}*/
    public static function saveChart($idProject,$properties){
	    //TODO SABR - Check the properties before inserting
	    PHDB::update(self::COLLECTION,
			array("_id" => new MongoId($idProject)),
            array('$set' => array("properties.chart"=> $properties))
        );
        return true;
    }
	public static function removeChart($idProject){
		PHDB::update(self::COLLECTION, 
            array("_id" => new MongoId($idProject)) , 
            array('$unset' => array("properties.chart" => 1))
        );
        return true;	
	}
    /**
	 * Update a project field value
	 * @param String $projectId The person Id to update
	 * @param String $projectFieldName The name of the field to update
	 * @param String $projectFieldValue 
	 * @param String $isAdmin or $isModerate (including after)
	 * @return boolean True if the update has been done correctly. Can throw CTKException on error.
	 */
	public static function updateProjectField($projectId, $projectFieldName, $projectFieldValue, $userId) {  
		if (!Authorisation::canEditItem($userId, self::COLLECTION, $projectId)) {
			throw new CTKException(Yii::t("project", "Can not update this project : you are not authorized to update that project !"));	
		}
		$dataFieldName = self::getCollectionFieldNameAndValidate($projectFieldName, $projectFieldValue, $projectId);
	
		//Specific case : 
		//Tags
		if ($dataFieldName == "tags") {
			$projectFieldValue = Tags::filterAndSaveNewTags($projectFieldValue);
		}

		//address
		if ($dataFieldName == "address") {
			if(!empty($projectFieldValue["postalCode"]) && !empty($projectFieldValue["codeInsee"])) {
				$insee = $projectFieldValue["codeInsee"];
				$address = SIG::getAdressSchemaLikeByCodeInsee($insee);
				$set = array("address" => $address, "geo" => SIG::getGeoPositionByInseeCode($insee));
			} else {
				throw new CTKException("Error updating the Project : address is not well formated !");			
			}
		//Start Date - End Date
		} else if ($dataFieldName == "startDate" || $dataFieldName == "endDate") {
			date_default_timezone_set('UTC');
			$dt = DateTime::createFromFormat('Y-m-d H:i', $projectFieldValue);
			if (empty($dt)) {
				$dt = DateTime::createFromFormat('Y-m-d', $projectFieldValue);
			}
			$newMongoDate = new MongoDate($dt->getTimestamp());
			$set = array($dataFieldName => $newMongoDate);	
		}
		else {
			$set = array($dataFieldName => $projectFieldValue);	
		}

		//update the project
		PHDB::update( self::COLLECTION, array("_id" => new MongoId($projectId)), 
		                          array('$set' => $set));
	                  
	    return array("result"=>true, "msg"=>"Votre projet a été modifié avec succes", "id"=>$projectId);
	}

 	/**
    *	- check if project exists
	*   - according to type of added contributor : Person or Organization
	*   - check existence based on new contributor Id 
		* 	- if exist 
		*		- if not allready member
		*			Link connect member to project as contributor
		*	- else : create and invite the member 
		*		Link connect the member
		*	 	send Notifications
    * @return [json Map] list
    */
	public static function addContributor( $projectId )
	{
	    $res = array( "result" => false , "content" => Yii::t("common", "Something went wrong!") );
		if(isset( $projectId) )
		{
			$project = PHDB::findOne( PHType::TYPE_PROJECTS,array("_id"=>new MongoId($projectId)));
			if($project)
			{
				if(preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#',$_POST['email']))
				{
					if($_POST['type'] == "citoyens"){
						$params = ( !isset( $_POST['contribId'] ) || $_POST['contribId'] == ""  ) ? array( "email" => $_POST['email'] ) 
																  : array( "_id" => new MongoId( $_POST['contribId'] ));
						$member = PHDB::findOne( Person::COLLECTION , $params);
						$memberType = Person::COLLECTION;
					}
					else
					{
						$member = PHDB::findOne( Organization::COLLECTION , array("_id"=> new MongoId( $_POST['contribId'] )));
						$memberType = Organization::COLLECTION;
					}

					if( !$member )
					{
						if($_POST['type'] == "citoyens")
						{
							$member = array(
								'name'=>$_POST['name'],
								'email'=>$_POST['email'],
								'invitedBy'=>Yii::app()->session["userId"],
								'created' => time()
						 	);
						 	$memberId = Person::createAndInvite($member);
						 	$isAdmin = (isset($_POST["contributorIsAdmin"])) ? $_POST["contributorIsAdmin"] : false;
						  	if ($isAdmin == "1") {
								$isAdmin = true;
							} else {
								$isAdmin = false;
							}
					 	} else {
							$member = array(
								'name'=>$_POST['name'],
								'email'=>$_POST['email'],
								'invitedBy'=>Yii::app()->session["userId"],
								'created' => time(),
								'type'=> $_POST["organizationType"]
							);

							$memberId = Organization::createAndInvite($member);
							$isAdmin = false;
						}
						$member["id"] = $memberId["id"];
						Link::connect($memberId["id"], $memberType,$projectId, PHType::TYPE_PROJECTS, Yii::app()->session["userId"], "projects",$isAdmin);		
						Link::connect($projectId, PHType::TYPE_PROJECTS,$memberId["id"], $memberType, Yii::app()->session["userId"], "contributors",$isAdmin );
						$res = array("result"=>true,"msg"=>Yii::t("common", "Your data has been saved"),"member"=>$member, "reload"=>true);
					}else{
						if( isset($project['links']["contributors"]) && isset( $project['links']["contributors"][(string)$member["_id"]] ))
							$res = array( "result" => false , "content" => "member allready exists" );
						else {
							$isAdmin = (isset($_POST["contributorIsAdmin"])) ? $_POST["contributorIsAdmin"] : false;
							if ($isAdmin == "1") {
								$isAdmin = true;
							} else {
								$isAdmin = false;
							}
							Link::connect($member["_id"], $memberType, $projectId, PHType::TYPE_PROJECTS, Yii::app()->session["userId"], "projects",$isAdmin);
							Link::connect($projectId, PHType::TYPE_PROJECTS, $member["_id"], $memberType, Yii::app()->session["userId"], "contributors",$isAdmin);

							//add notification 
							Notification::invited2Project($memberType, $member["_id"], $projectId, $project["name"]);
							$res = array("result"=>true,"msg"=>Yii::t("common", "Your data has been saved"),"member"=>$member,"reload"=>true);
						}
					}
				}else
					$res = array( "result" => false , "content" => "email must be valid" );
			}
		}
		return $res;
	}
	/**
	 * get contributors for a Project By an project Id
	 * @param String $id : is the mongoId (String) of the project
	 * @param String $type : can be used to filter the contributor by type (all (default), person, project)
	 * @return arrays of contributors (links.contributors)
	 */
	public static function getContributorsByProjectId($id, $type="all",$role=null) {
	  	$res = array();
	  	$project = project::getById($id);
	  	
	  	if (empty($project)) {
            throw new CTKException(Yii::t("project", "The project id is unkown : contact your admin"));
        }
	  	
	  	if ( isset($project) && isset( $project["links"] ) && isset( $project["links"]["contributors"] ) ) 
	  	{
	  		$contributors = $project["links"]["contributors"];
	  		//No filter needed
	  		if ($type == "all") {
	  			return $contributors;
	  		} else {
	  			foreach ($project["links"]["contributors"] as $key => $contributor) {
		            if ($contributor['type'] == $type ) {
		                $res[$key] = $contributor;
		            }
		            if ( $role && @$contributor[$role] == true ) {
		                $res[$key] = $contributor;
		            }
	        	}
	  		}
	  	}
	  	return $res;
	}
	/**
	 * List all the event of a project and his members (if can edit member)
	 * @param String $organisationId : is the mongoId of the organisation
	 * @return all the event link with the organization
	 */
	//TODO SBAR : Refactor using a startDate in order to not retrieve all the database
	public static function listEventsPublicAgenda($projectId)
	{
		$events = array();
		$project = Organization::getById($projectId);
		
		if(isset($project["links"]["events"])){
			foreach ($project["links"]["events"] as $keyEv => $valueEv) {
				 $event = Event::getPublicData($keyEv);
           		 $events[$keyEv] = $event;
			}
		}
		foreach ($events as $key => $value) {
        	$profil = Document::getLastImageByKey($key, PHType::TYPE_EVENTS, Document::IMG_PROFIL);
        	if($profil!="")
        		$value['imagePath']=$profil;
        }
		return $events;
	}
}
?>