<?php 
class Project {

	const COLLECTION = "projects";
	const CONTROLLER = "project";
	const ICON = "fa-lightbulb-o";
	const COLOR = "#8C5AA1";
	
	//From Post/Form name to database field name
	public static $dataBinding = array(
	    "name" => array("name" => "name", "rules" => array("required")),
	    "address" => array("name" => "address", "rules" => array("addressValid")),
	    "addresses" => array("name" => "addresses"),
	    "streetAddress" => array("name" => "address.streetAddress"),
	    "postalCode" => array("name" => "address.postalCode"),
	    "city" => array("name" => "address.codeInsee"),
	    "addressCountry" => array("name" => "address.addressCountry"),
	    "geo" => array("name" => "geo", "rules" => array("geoValid")),
	    "geoPosition" => array("name" => "geoPosition", "rules" => array("geoPositionValid")),
	    "description" => array("name" => "description"),
	    "shortDescription" => array("name" => "shortDescription"),
	    "startDate" => array("name" => "startDate" ),
	    "endDate" => array("name" => "endDate"),
	    "tags" => array("name" => "tags"),
	    "url" => array("name" => "url"),
	    "licence" => array("name" => "licence"),
	    "avancement" => array("name" => "properties.avancement"),
	    "state" => array("name" => "state"),
	    "warnings" => array("name" => "warnings"),
	    "modules" => array("name" => "modules"),
	    "badges" => array("name" => "badges"),
	    "source" => array("name" => "source"),
	    "preferences" => array("name" => "preferences"),
	    "medias" => array("name" => "medias"),
	    "urls" => array("name" => "urls"),
	    "type" => array("name" => "type"),
	    "contacts" => array("name" => "contacts"),
		"parentId" => array("name" => "parentId"),
		"parentType" => array("name" => "parentType"),
		"modified" => array("name" => "modified"),
	    "updated" => array("name" => "updated"),
	    "creator" => array("name" => "creator"),
	    "created" => array("name" => "created"),
	    "locality" => array("name" => "address"),
	    "descriptionHTML" => array("name" => "descriptionHTML"),
	);

	public static $avancement = array(
        "idea" => "idea",
        "concept" => "concept",
        "started" => "started",
        "development" => "development",
        "testing" => "testing",
        "mature" => "mature"
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
	  	if ($project !=null) {
		  	if (!empty($project["startDate"]) || !empty($project["endDate"])) {
		  		$now = time();
		  		
		  		if(isset($project["startDate"])) {
					$yester2day = mktime(0, 0, 0, date("m")  , date("d")-2, date("Y"));
					if (gettype($project["startDate"]) == "object") {
						//Set TZ to UTC in order to be the same than Mongo
						date_default_timezone_set('UTC');
						if (!empty($project["startDate"]))
							$project["startDate"] = date('Y-m-d H:i:s', $project["startDate"]->sec);
					} else {
						$project["startDate"] = date('Y-m-d H:i:s',$yester2day);;
					}
				}

		  		if(isset($project["startDate"]) && isset($project["endDate"])) {
					$yesterday = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
					if (gettype($project["endDate"]) == "object") {
						date_default_timezone_set('UTC');
						if (!empty($project["endDate"]))
							$project["endDate"] = date('Y-m-d H:i:s', $project["endDate"]->sec);
					} else {
						$project["endDate"] = date('Y-m-d H:i:s', $yesterday);
					}
				}
			}
		}

		if (!empty($project)) {
			$project = array_merge($project, Document::retrieveAllImagesUrl($id, self::COLLECTION, null, $project));
			$project["typeSig"] = "projects";
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
		$project = PHDB::findOneById( self::COLLECTION ,$id, array("id" => 1, "name" => 1, "shortDescription" => 1, "description" => 1, "address" => 1, "geo" => 1, "tags" => 1, "profilImageUrl" => 1, "profilThumbImageUrl" => 1, "profilMarkerImageUrl" => 1, "profilMediumImageUrl" => 1, "addresses"=>1) );
		if(!empty($project)){
			$simpleProject["id"] = $id;
			$simpleProject["name"] = @$project["name"];
			$simpleProject = array_merge($simpleProject, Document::retrieveAllImagesUrl($id, self::COLLECTION, null, $project));
			$simpleProject["address"] = empty($project["address"]) ? array("addressLocality" => Yii::t("common","Unknown Locality")) : $project["address"];
			$simpleProject["addresses"] = @$project["addresses"];
			$simpleProject["geo"] = @$project["geo"];
			$simpleProject["tags"] = @$project["tags"];
			$simpleProject["shortDescription"] = @$project["shortDescription"];
			$simpleProject["description"] = @$project["description"];
			$simpleProject["typeSig"] = "projects";
		}

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
	public static function getAndCheckProject($project, $userId,$update=null) {
		
		$newProject = array();
		if (empty($project['name'])) {
			throw new CTKException(Yii::t("project","You have to fill a name for your project"));
		}else
			$newProject['name'] = $project['name'];
		
		// Is There a project with the same name ?
		if(!$update){
		   // $projectSameName = PHDB::findOne(self::COLLECTION ,array( "name" => $_POST['name']));
			$projectSameName = PHDB::findOne(self::COLLECTION ,array( "name" => $project['name']));
		    if($projectSameName) { 
		      throw new CTKException(Yii::t("project","A project with the same name already exist in the plateform"));
		    }
		}

		if(!$update){
			$newProject = array(
				"name" => $project['name'],
				'creator' => $userId,
				'created' => new MongoDate(time())
		    );
		}

		if(!empty($project['startDate']) )
			$newProject['startDate'] = new MongoDate( strtotime( $project['startDate'] ));//$project['startDate'];
			
		if(!empty($project['endDate'])) 
			$newProject['endDate'] = new MongoDate( strtotime( $project['endDate'] ));//$project['endDate']
				  
		if(!empty($project['postalCode'])) {
			if (!empty($project['city'])) {
				$insee = $project['city'];
				$cityName= $project['cityName'];
				$address = SIG::getAdressSchemaLikeByCodeInsee($insee,$project['postalCode'],$cityName);
				//$address["addressCountry"] = $project["addressCountry"];
				$newProject["address"] = $address;
				if( !empty($project['streetAddress'])) 
					$newProject["address"]["streetAddress"] = $project['streetAddress'];
				//$newProject["geo"] = SIG::getGeoPositionByInseeCode($insee);
			}
		}else if($project['address']){
			$newProject['address'] = $project['address'] ;
		} else if(!$update){
			throw new CTKException(Yii::t("project","Please fill the postal code of the project to communect it"));
		}
		if(!empty($project['geo']) && !empty($project["geoPosition"])){
			$newProject["geo"] = $project['geo'];
			$newProject["geoPosition"] = $project['geoPosition'];
		}
		else if(!empty($project['geoPosLatitude']) && !empty($project["geoPosLongitude"])){
			
			$newProject["geo"] = 	array(	"@type"=>"GeoCoordinates",
						"latitude" => $project['geoPosLatitude'],
						"longitude" => $project['geoPosLongitude']);

			$newProject["geoPosition"] = array("type"=>"Point",
													"coordinates" =>
														array(
															floatval($project['geoPosLongitude']),
															floatval($project['geoPosLatitude']))
												 	  	);
		}else if(!$update){
			if(!empty($insee))
				$newProject["geo"] = SIG::getGeoPositionByInseeCode($insee);
			else
				throw new CTKException(Yii::t("project","Please fill the insee code of the project to communect it"));
		}
		
		//No mandotory fields 
		if (!empty($project['description']))
			$newProject["description"] = $project['description'];
		
		if (!empty($project['url']))
			$newProject["url"] = $project['url'];

		if (!empty($project['licence']))
			$newProject["licence"] = $project['licence'];

		//Tags
		if (isset($project['tags']) ) {
			if ( is_array( $project['tags'] ) ) {
				$tags = $project['tags'];
			} else if ( is_string($project['tags']) ) {
				$tags = explode(",", $project['tags']);
			}
			$newProject["tags"] = $tags;
		}

		if(!empty($params['parentId']))
	        $newEvent["parentId"] = $params['parentId'];

	    if(!empty($params['parentType']))
	        $newEvent["parentType"] = $params['parentType'];

		return $newProject;
	}

	/**
	 * Insert a new project, checking if the project is well formated
	 * @param array $params Array with all fields for a project
	 * @param string $userId UserId doing the insertion
	 * @return array as result type
	 */
	public static function insert($params, $parentId,$parentType){
	    $newProject = self::getAndCheckProject($params, Yii::app() -> session["userId"]);
	    if (isset($newProject["tags"]))
			$newProject["tags"] = Tags::filterAndSaveNewTags($newProject["tags"]);

		if(empty($newProject["preferences"])){
			$newProject["preferences"] = array("publicFields" => array(), "privateFields" => array(), "isOpenData" => true, "isOpenEdition" => true);
		}
		$newProject["updated"] = time();
	    PHDB::insert(self::COLLECTION,$newProject);

	    Badge::addAndUpdateBadges("opendata",(String)$newProject["_id"], Project::COLLECTION);
		Link::addContributor(Yii::app() -> session["userId"],Person::COLLECTION,$parentId,$parentType,$newProject["_id"]);
	   // Link::connect($parentId, $parentType, $newProject["_id"], self::COLLECTION, $parentId, "projects", true );

	    Notification::createdObjectAsParam(Person::COLLECTION,Yii::app() -> session["userId"],Project::COLLECTION, (String)$newProject["_id"], $parentType, $parentId, $newProject["geo"], (isset($newProject["tags"])) ? $newProject["tags"]:null ,$newProject["address"]);
	    //ActivityStream::saveActivityHistory(ActStr::VERB_CREATE, (String)$newProject["_id"], Project::COLLECTION, "project", $newProject["name"]);
	    return array("result"=>true, "msg"=>"Votre projet est communecté.", "id" => $newProject["_id"]);	
	}

	public static function afterSave($params){
	    
	    Badge::addAndUpdateBadges("opendata",(String)$params["_id"], Project::COLLECTION);
	    if( !@$params['parentType'] && !@$params['parentId'] ){
			$params['parentType'] = Person::COLLECTION; 
			$params['parentId'] = Yii::app() -> session["userId"];
		}
		Link::addContributor(Yii::app() -> session["userId"],Person::COLLECTION,$params['parentId'], $params['parentType'],$params["_id"]);
	    Notification::createdObjectAsParam(Person::COLLECTION,Yii::app() -> session["userId"],Project::COLLECTION, (String)$params["_id"], $params['parentType'], $params['parentId'], @$params["geo"], @$params["tags"] ,@$params["address"]);
	    if($params["parentType"]==Organization::COLLECTION || $params["parentType"]==Project::COLLECTION)
	    	Notification::constructNotification(ActStr::VERB_ADD, array("id" => Yii::app()->session["userId"],"name"=> Yii::app()->session["user"]["name"]), array("type"=>$params["parentType"],"id"=> $params["parentId"]), array("id"=>(string)$params["_id"],"type"=> Project::COLLECTION), Project::COLLECTION);

	    //ActivityStream::saveActivityHistory(ActStr::VERB_CREATE, (String)$params["_id"], Project::COLLECTION, "project", $params["name"]);
	    return array("result"=>true, "msg"=>"Votre projet est communecté.", "id" => $params["_id"]);	
		
	}

	/**
	 * update an organization in database
	 * @param String $organizationId : 
	 * @param array $organization organization fields to update
	 * @param String $userId : the userId making the update
	 * @return array of result (result => boolean, msg => string)
	 */
	public static function update($projectId, $projectChangedFields, $userId) 
	{
		//Check if user is authorized to update
		if (! Authorisation::isProjectAdmin($projectId,$userId)) {
			return array("result"=>false, "msg"=>Yii::t("project", "Unauthorized Access."));
		}
		
		$project = self::getById( $projectId );
		foreach ($projectChangedFields as $fieldName => $fieldValue) {
			//if( $project[ $fieldName ] != $fieldValue)
				self::updateProjectField($projectId, $fieldName, $fieldValue, $userId);
		}

	    return array("result"=>true, "msg"=>Yii::t("project", "The project has been updated"), "id"=>$projectId);
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
		$pref = Preference::getPreferencesByTypeId($projectId, self::COLLECTION);
	 	$authorization = Preference::isOpenEdition($pref);
	 	if($authorization == false){
			$authorization = Authorisation::canEditItem($userId, self::COLLECTION, $projectId);
			if (!$authorization) {
				throw new CTKException(Yii::t("project", "Can not update this project : you are not authorized to update that project !"));	
			}
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
				$postalCode = $projectFieldValue["postalCode"];
				$cityName = $projectFieldValue["addressLocality"];
				$address = SIG::getAdressSchemaLikeByCodeInsee($insee,$postalCode,$cityName);
				$set = array("address" => $address, "geo" => SIG::getGeoPositionByInseeCode($insee,$postalCode,$cityName));
				if (!empty($projectFieldValue["streetAddress"]))
					$set["address"]["streetAddress"] = $projectFieldValue["streetAddress"];
			} else {
				throw new CTKException("Error updating the Project : address is not well formated !");			
			}
		//Start Date - End Date
		} else if ($dataFieldName == "startDate" || $dataFieldName == "endDate") {
			date_default_timezone_set('UTC');
			if( !is_string( $projectFieldValue ) && get_class( $projectFieldValue ) == "MongoDate"){
				$newMongoDate = $projectFieldValue;
			}else{
				$dt = DateTime::createFromFormat('Y-m-d H:i', $projectFieldValue);
				if (empty($dt)) {
					$dt = DateTime::createFromFormat('Y-m-d', $projectFieldValue);
				}

				$newMongoDate = new MongoDate($dt->getTimestamp());
			}
			$set = array($dataFieldName => $newMongoDate);	
		}
		else {
			$set = array($dataFieldName => $projectFieldValue);	
		}

		//update the project
		$set["modified"] = new MongoDate(time());
		$set["updated"] = time();
		PHDB::update( self::COLLECTION, array("_id" => new MongoId($projectId)), 
		                        array('$set' => $set));
	    if($authorization == "openEdition" && $dataFieldName != "badges"){
			// Add in activity to show each modification added to this entity
			//echo $dataFieldName;
			ActivityStream::saveActivityHistory(ActStr::VERB_UPDATE, $projectId, Project::COLLECTION, $dataFieldName, $projectFieldValue);
		}
	    return array("result"=>true, "msg"=>Yii::t("project","Your project is updated"), "id"=>$projectId);
	}

 	/** TODO CDA -- TO DELETE
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
	/*public static function addContributor( $projectId )
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
	}*/
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
	  		$contributors = array();
	  		foreach($project["links"]["contributors"] as $key => $contributor){
	  			if (!@$contributor["toBeValidated"] && !@$contributor["isInviting"])
	  				$contributors[$key]=$contributor;
	  		}
	  		//No filter needed
	  		if ($type == "all") {
	  			return $contributors;
	  		} else {
	  			foreach ($project["links"]["contributors"] as $key => $contributor) {
		            if ($contributor['type'] == $type ) {
		            	if (!@$contributor["toBeValidated"] && !@$contributor["isInviting"])
		            		$res[$key] = $contributor;
		            }
		            if ( $role && @$contributor[$role] == true ) {
		            	if ($role=="isAdmin"){
		            		if(!@$contributor["isAdminPending"])
		            			$res[$key] = $contributor;
		            	} else
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
		return $events;
	}




	/* 	Get state an event from an OpenAgenda ID 
	*	@param string OpenAgenda ID
	*	@param string Date Update openAgenda
	*   return String ("Add", "Update" or "Delete")
	*/
	public static function createProjectFromImportData($projectImportData, $key=null, $warnings = null) {
		
		if(!empty($projectImportData['name']))
			$newProject["name"] = $projectImportData["name"];

		if(!empty($projectImportData['startDate']))
			$newProject["startDate"] = $projectImportData["startDate"];

		if(!empty($projectImportData['created']))
			$newProject["created"] = $projectImportData["created"];

		if(!empty($projectImportData['endDate']))
			$newProject["endDate"] = $projectImportData["endDate"];

		if(!empty($projectImportData['description']))
			$newProject["description"] = $projectImportData["description"];

		if(!empty($projectImportData['tags']))
			$newProject["tags"] = $projectImportData["tags"];

		if(!empty($projectImportData['source'])){

			if(!empty($projectImportData['source']['id']))
				$newProject["source"]['id'] = $projectImportData["source"]['id'];
			if(!empty($projectImportData['source']['url']))
				$newProject["source"]['url'] = $projectImportData["source"]['url'];
			if(!empty($projectImportData['source']['key']))
				$newProject["source"]['key'] = $key;
		}

		if(!empty($projectImportData['warnings']))
			$newProject["warnings"] = $projectImportData["warnings"];

		$address = (empty($projectImportData['address']) ? null : $projectImportData['address']);
		$geo = (empty($projectImportData['geo']) ? null : $projectImportData['geo']);
		$details = Import::getAndCheckAddressForEntity($address, $geo, $warnings) ;
		$newProject['address'] = $details['address'];

		if(!empty($details['geo']))
			$newProject['geo'] = $details['geo'] ;

		if(!empty($newProject['warnings']))
			$newProject['warnings'] = array_merge($newProject['warnings'], $details['warnings']);
		else
			$newProject['warnings'] = $details['warnings'];
			
		return $newProject;
	}


	public static function getAndCheckProjectFromImportData($project, $userId,$insert=null, $update=null, $warnings = null) {
		//var_dump($project);

		$newProject = array();
		if (empty($project['name'])) {
			if($warnings)
				$newProject["warnings"][] = "001" ;
			else
				throw new CTKException(Yii::t("import","001"));
		}else
			$newProject['name'] = $project['name'];
		
		// Is There a project with the same name ?
		/*if(!$update){
		   	$projectSameName = PHDB::findOne(self::COLLECTION ,array( "name" => $project['name']));
		    if($projectSameName) { 
		      throw new CTKException(Yii::t("import","A project with the same name already exist in the plateform"));
		    }
		}*/

		if(!$update){
			$newProject = array(
				"name" => $project['name'],
				'creator' => $userId,
				'created' => new MongoDate(time())
			);
		}

		if(!empty($project['startDate']) )
		{	
			if(!$insert){	
				$newProject['startDate'] = $project['startDate'];
			}else{
				$t = strtotime($project['startDate']); 
				$m = new MongoDate(strtotime($project['startDate']));
				$newProject['startDate'] = $m;
			}
			
			
		}	
			
		if(!empty($project['endDate']))
		{
			if(!$insert){	
				$newProject['endDate'] = $project['endDate'];
			}else{
				$t = strtotime($project['endDate']); 
				$m = new MongoDate(strtotime($project['endDate']));
				$newProject['endDate'] = $m;
			}
		} 
			
		if(!empty($project['address'])) {
			if(empty($project['address']['postalCode']) /*&& $insert*/){
				if($warnings)
					$newProject["warnings"][] = "101" ;
				else
					throw new CTKException(Yii::t("import","101", null, Yii::app()->controller->module->id));
			}
			if(empty($project['address']['codeInsee'])/*&& $insert*/){
				if($warnings)
					$newProject["warnings"][] = "102" ;
				else
					throw new CTKException(Yii::t("import","102", null, Yii::app()->controller->module->id));
			}
			if(empty($project['address']['addressCountry']) /*&& $insert*/){
				if($warnings)
					$newProject["warnings"][] = "104" ;
				else
					throw new CTKException(Yii::t("import","104", null, Yii::app()->controller->module->id));
			}
			if(empty($project['address']['addressLocality']) /*&& $insert*/){
				if($warnings)
					$newProject["warnings"][] = "105" ;
				else
					throw new CTKException(Yii::t("import","105", null, Yii::app()->controller->module->id));
			}
			$newProject['address'] = $project['address'] ;

		}else {
			if($warnings)
				$newProject["warnings"][] = "100" ;
			else
				throw new CTKException(Yii::t("import","100", null, Yii::app()->controller->module->id));
		}


		if(!empty($project['geo']) && !empty($project["geoPosition"])){
			$newProject["geo"] = $project['geo'];
			$newProject["geoPosition"] = $project['geoPosition'];

		}else if(!empty($project["geo"]['latitude']) && !empty($project["geo"]["longitude"])){
			$newProject["geo"] = 	array(	"@type"=>"GeoCoordinates",
						"latitude" => $project["geo"]['latitude'],
						"longitude" => $project["geo"]["longitude"]);

			$newProject["geoPosition"] = array("type"=>"Point",
													"coordinates" =>
														array(
															floatval($project["geo"]['latitude']),
															floatval($project["geo"]['longitude']))
												 	  	);
		}
		else if($insert){
			if($warnings)
				$newProject["warnings"][] = "150" ;
			else
				throw new CTKException(Yii::t("import","150", null, Yii::app()->controller->module->id));
		}else if($warnings)
			$newProject["warnings"][] = "150" ;
		
		if (!empty($project['description']))
			$newProject["description"] = $project['description'];
		
		if (!empty($project['url']))
			$newProject["url"] = $project['url'];

		if (!empty($project['licence']))
			$newProject["licence"] = $project['licence'];

		if (!empty($project['properties']))
			$newProject["properties"] = $project['properties'];

		if (!empty($project['source']))
			$newProject["source"] = $project['source'];


		if (isset($project['tags']) ) {
			if ( is_array( $project['tags'] ) ) {
				$tags = $project['tags'];
			} else if ( is_string($project['tags']) ) {
				$tags = explode(",", $project['tags']);
			}
			$newProject["tags"] = $tags;
		}

		if (!empty($project['warnings'])){
			$newProject["warnings"] = $project['warnings'];
			$newProject["state"] = "uncomplete";
		}

		return $newProject;
	}



	/**
	 * Insert a new project, checking if the project is well formated
	 * @param array $params Array with all fields for a project
	 * @param string $userId UserId doing the insertion
	 * @return array as result type
	 */
	public static function insertProjetFromImportData($params, $parentId,$parentType, $warnings){
	    $newProject = self::getAndCheckProjectFromImportData($params, $parentId, true, null, $warnings);

	    if(!empty($newProject["warnings"]) && $warnings == true)
	    	$newProject["warnings"] = Import::getAndCheckWarnings($newProject["warnings"]);
	    
	    if(isset($newProject["tags"]))
			$newProject["tags"] = Tags::filterAndSaveNewTags($newProject["tags"]);

		if(empty($newProject["preferences"])){
			$newProject["preferences"] = $newProject["preferences"] = array("publicFields" => array(), "privateFields" => array(), "isOpenData" => true);
		}
		
	    PHDB::insert(self::COLLECTION,$newProject);
	    
	    return array("result"=>true, "msg"=>"Votre projet est communecté.", "id" => $newProject["_id"]);	
	}



	public static function getQuestionAnwser($project){
		if(!empty($project["tags"])){
			if(in_array("commun", $project['tags']) || in_array("fabmob", $project['tags'])){
				$url = "http://data.patapouf.org".$project["source"]["url"];
				
				$res = Import::getDataByUrl($url);

				$json = json_decode($res, true);

				if(!empty($json["question_answers"])){
					foreach ($json["question_answers"] as $key => $value) {
						$qt["key"] = $value["question"]["slug"] ;
						$qt["description"] = $value["answer"] ;
						$qt["value"] = -1 ;
						$project["properties"]["socialCode"][] = $qt;

					}

				}
			}
		}


		return $project ;
	}

	public static function getDataBinding() {
	  	return self::$dataBinding;
	}

	


}
?>