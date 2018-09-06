<?php 
class Element {

	const NB_DAY_BEFORE_DELETE = 5;
	const STATUS_DELETE_PEDING = "deletePending";
	const ERROR_DELETING = "errorTryingToDelete";

	public static $urlTypes = array (
        "chat" => "Chat",
        "decisionroom" => "Salle de decision",
        "website" => "Site web",
        "partner" => "Partenaire",
        "documentation" => "Documentation",
        "wiki" => "Wiki",
        "management" => "Gestion",
	    "funding" => "Financement",
	    "other" => "Autre"
	);

	public static $connectTypes = array(
		Organization::COLLECTION => "members",
		Project::COLLECTION => "contributors",
		Event::COLLECTION => "attendees",
		Person::COLLECTION => "followers"
	);

	public static $connectAs = array(
		Organization::COLLECTION => "member",
		Project::COLLECTION => "contributor",
		Event::COLLECTION => "attendee",
		Person::COLLECTION => "follows"
	);

	public static function getControlerByCollection ($type) { 

		$ctrls = array(
	    	Organization::COLLECTION => Organization::CONTROLLER,
	    	Person::COLLECTION => Person::CONTROLLER,
	    	Event::COLLECTION => Event::CONTROLLER,
	    	Project::COLLECTION => Project::CONTROLLER,
			News::COLLECTION => News::COLLECTION,
	    	Need::COLLECTION => Need::CONTROLLER,
	    	City::COLLECTION => City::CONTROLLER,
	    	Survey::COLLECTION => Survey::CONTROLLER,
	    	Poi::COLLECTION => Poi::CONTROLLER,
	    	Proposal::COLLECTION => Proposal::CONTROLLER,
	    	Action::COLLECTION => Action::CONTROLLER,
	    	//ActionRoom::COLLECTION => ActionRoom::CONTROLLER,
	    	//ActionRoom::COLLECTION_ACTIONS => ActionRoom::CONTROLLER,
	    	Place::COLLECTION => Place::CONTROLLER,
	    	Classified::COLLECTION => Classified::CONTROLLER,
	    	Classified::TYPE_RESSOURCES => Classified::TYPE_RESSOURCES_CONTROLLER,
	    	Classified::TYPE_JOBS => Classified::TYPE_JOBS_CONTROLLER,
	    	//Ressource::COLLECTION => Ressource::COLLECTION,
	    	//Ressource::CONTROLLER => Ressource::CONTROLLER
	    );	    
    	return @$ctrls[$type];
    }


    public static function getCollectionByControler ($type) { 

		$ctrls = array(
	    	Organization::CONTROLLER => Organization::COLLECTION,
	    	Person::CONTROLLER => Person::COLLECTION,
	    	Event::CONTROLLER => Event::COLLECTION,
	    	Project::CONTROLLER => Project::COLLECTION,
			News::COLLECTION => News::COLLECTION,
	    	Need::CONTROLLER => Need::COLLECTION,
	    	City::CONTROLLER => City::COLLECTION,
	    	Survey::CONTROLLER => Survey::COLLECTION,
	    	Poi::CONTROLLER => Poi::COLLECTION,
	    	Proposal::CONTROLLER => Proposal::COLLECTION,
	    	Action::CONTROLLER => Action::COLLECTION,
	    	//ActionRoom::CONTROLLER => ActionRoom::COLLECTION,
	    	//ActionRoom::CONTROLLER_ACTIONS => ActionRoom::COLLECTION,
	    	Place::CONTROLLER => Place::COLLECTION,
	    	Ressource::COLLECTION => Ressource::COLLECTION,
	    	Ressource::CONTROLLER => Ressource::CONTROLLER
	    );	    
    	return @$ctrls[$type];
    }

    public static function getModelByType($type) {
    	$models = array(
	    	Organization::COLLECTION => "Organization",
	    	Person::COLLECTION 		 => "Person",
	    	Event::COLLECTION 		 => "Event",
	    	Project::COLLECTION 	 => "Project",
			News::COLLECTION 		 => "News",
	    	Need::COLLECTION 		 => "Need",
	    	City::COLLECTION 		 => "City",
	    	Thing::COLLECTION 		 => "Thing",
	    	Poi::COLLECTION 		 => "Poi",
	    	Classified::COLLECTION   => "Classified",
	    	Product::COLLECTION 	 => "Product",
	    	Service::COLLECTION   	 => "Service",
	    	Survey::COLLECTION   	 => "Survey",
	    	Bookmark::COLLECTION   	 => "Bookmark",
	    	Proposal::COLLECTION   	 => "Proposal",
	    	Room::COLLECTION   	 	 => "Room",
	    	Action::COLLECTION   	 => "Action",
	    	Network::COLLECTION   	 => "Network",
	    	Url::COLLECTION   	 	 => "Url",
	    	Place::COLLECTION   => "Place",
	    	Ressource::COLLECTION   => "Ressource",
	    	Circuit::COLLECTION   	 => "Circuit",
	    	Risk::COLLECTION   => "Risk",
	    );	
	 	return @$models[$type];     
    }
    
    public static function getCommonByCollection ($type) { 

		$commons = array(
	    	Organization::COLLECTION => "organisation",
	    	Event::COLLECTION => "event",
	    	/*Person::COLLECTION => Person::CONTROLLER,
	    	Project::COLLECTION => Project::CONTROLLER,
			News::COLLECTION => News::COLLECTION,
	    	Need::COLLECTION => Need::CONTROLLER,
	    	City::COLLECTION => City::CONTROLLER,
	    	Survey::COLLECTION => Survey::CONTROLLER,
	    	ActionRoom::COLLECTION => ActionRoom::CONTROLLER,
	    	ActionRoom::COLLECTION_ACTIONS => ActionRoom::CONTROLLER,*/
	    );	    
    	return @$commons[$type];
    }

    public static function getFaIcon ($type) { 

		$fas = array(
	    	Organization::COLLECTION 	=> "group",
	    	Person::COLLECTION 			=> "user",
	    	Event::COLLECTION 			=> "calendar",
	    	Project::COLLECTION 		=> "lightbulb-o",
			News::COLLECTION 			=> "rss",
	    	Need::COLLECTION 			=> "cubes",
	    	City::COLLECTION 			=> "university",
	    	ActionRoom::TYPE_ACTION		=> "cog",
	    	ActionRoom::TYPE_ENTRY		=> "archive",
	    	ActionRoom::TYPE_DISCUSS	=> "comment",
	    	ActionRoom::TYPE_VOTE		=> "archive",
	    	ActionRoom::TYPE_ACTIONS	=> "cogs",

	    	Organization::TYPE_NGO 		=> "group",
	    	Organization::TYPE_BUSINESS => "industry",
	    	Organization::TYPE_GROUP 	=> "circle-o",
	    	Organization::TYPE_GOV 		=> "university",
	    	Classified::COLLECTION 		=> "bullhorn",
	    	Classified::TYPE_RESSOURCES	=>"cubes",
	    	Classified::TYPE_JOBS=>"briefcase",
	    );	
	    
	    if(isset($fas[$type])) return $fas[$type];
	    else return false;
    }
    public static function getColorIcon ($type) { 
    	$colors = array(
	    	Organization::COLLECTION 	=> "green",
	    	Person::COLLECTION 			=> "yellow",
	    	Event::COLLECTION 			=> "orange",
	    	Project::COLLECTION 		=> "purple",

	    	Organization::TYPE_NGO 		=> "green",
	    	Organization::TYPE_BUSINESS => "azure",
	    	Organization::TYPE_GROUP 	=> "turq",
	    	Organization::TYPE_GOV 		=> "red",
	    	Classified::COLLECTION 		=> "azure",
	    	Classified::TYPE_RESSOURCES	=>"vine",
	    	Classified::TYPE_JOBS=>"yellow-k",
	    );	
	    if(isset($colors[$type])) return $colors[$type];
	    else return false;
     }
    
    public static function getElementSpecsByType ($type) { 
    	$ctrler = self::getControlerByCollection ($type);
    	$prefix = "#".$ctrler;
		$fas = array(

	    	Person::COLLECTION 			=> array("icon"=>"user","color"=>"#FFC600","text-color"=>"yellow",
	    										 "hash"=> Person::CONTROLLER.".detail.id.",
	    										 "collection"=>Person::COLLECTION),
	    	Person::CONTROLLER 			=> array("icon"=>"user","color"=>"#FFC600","text-color"=>"yellow",
	    										 "hash"=> Person::CONTROLLER.".detail.id.",
	    										 "collection"=>Person::COLLECTION),

	    	Organization::COLLECTION 	=> array("icon"=>"group", "color"=>"#93C020","text-color"=>"green",
	    										 "hash"=> Organization::CONTROLLER.".detail.id."),
	    	Organization::CONTROLLER 	=> array("icon"=>"group", "color"=>"#93C020","text-color"=>"green",
	    										 "hash"=> Organization::CONTROLLER.".detail.id.",
	    										 "collection"=>Organization::COLLECTION),

	    	
	    	Event::COLLECTION 			=> array("icon"=>"calendar","color"=>"#FFA200","text-color"=>"orange",
	    										 "hash"=> Event::CONTROLLER.".detail.id."),
	    	Event::CONTROLLER 			=> array("icon"=>"calendar","color"=>"#FFA200","text-color"=>"orange",
	    										 "hash"=> Event::CONTROLLER.".detail.id.",
	    										 "collection"=>Event::COLLECTION),

	    	Project::COLLECTION 		=> array("icon"=>"lightbulb-o","color"=>"#8C5AA1","text-color"=>"purple",
	    										 "hash"=> Project::CONTROLLER.".detail.id."),
	    	Project::CONTROLLER 		=> array("icon"=>"lightbulb-o","color"=>"#8C5AA1","text-color"=>"purple",
	    										 "hash"=> Project::CONTROLLER.".detail.id.",
	    										 "collection"=>Project::COLLECTION),

			Classified::COLLECTION 		=> array("icon"=>"bullhorn","color"=>"#2BB0C6","text-color"=>"azure",
	    										 "hash"=> Classified::CONTROLLER.".detail.id."),
	    	Classified::CONTROLLER 		=> array("icon"=>"bullhorn","color"=>"#2BB0C6","text-color"=>"azure",
	    										 "hash"=> Classified::CONTROLLER.".detail.id.",
	    										 "collection"=>Classified::COLLECTION),
			Ressource::COLLECTION 		=> array("icon"=>"cube","color"=>"#2BB0C6","text-color"=>"vine",
	    										 "hash"=> Ressource::CONTROLLER.".detail.id."),
	    	Ressource::CONTROLLER 		=> array("icon"=>"cube","color"=>"#2BB0C6","text-color"=>"vine",
	    										 "hash"=> Ressource::CONTROLLER.".detail.id.",
	    										 "collection"=>Ressource::COLLECTION),
			
			Product::COLLECTION 		=> array("icon"=>"gift","color"=>"#2BB0C6","text-color"=>"azure",
	    										 "hash"=> Product::CONTROLLER.".detail.id."),
	    	Product::CONTROLLER 		=> array("icon"=>"gift","color"=>"#2BB0C6","text-color"=>"azure",
	    										 "hash"=> Product::CONTROLLER.".detail.id.",
	    										 "collection"=>Product::COLLECTION),
	    	Service::COLLECTION 		=> array("icon"=>"gift","color"=>"#2BB0C6","text-color"=>"azure",
	    										 "hash"=> Service::CONTROLLER.".detail.id."),
	    	Service::CONTROLLER 		=> array("icon"=>"gift","color"=>"#2BB0C6","text-color"=>"azure",
	    										 "hash"=> Service::CONTROLLER.".detail.id.",
	    										 "collection"=>Service::COLLECTION),

			Poi::COLLECTION 			=> array("icon"=>"map-marker","color"=>"#2BB0C6","text-color"=>"green-poi",
	    										 "hash"=> Poi::CONTROLLER.".detail.id."),
	    	Poi::CONTROLLER 			=> array("icon"=>"map-marker","color"=>"#2BB0C6","text-color"=>"green-poi",
	    										 "hash"=> Poi::CONTROLLER.".detail.id.",
	    										 "collection"=>Poi::COLLECTION),

			News::COLLECTION 			=> array("icon"=>"rss","hash"=> $prefix.""),

	    	City::COLLECTION 			=> array("icon"=>"university","color"=>"#E33551","text-color"=>"red",
	    										 "hash"=> $prefix.".detail.insee."),
	    	City::CONTROLLER 			=> array("icon"=>"university","color"=>"#E33551","text-color"=>"red",
	    										 "hash"=> $prefix.".detail.insee."),
	    	
	    	ActionRoom::TYPE_VOTE		=> array("icon"=>"archive","color"=>"#3C5665", "text-color"=>"azure",
	    		 								 "hash"=> "survey.entries.id.",
	    		 								 "collection"=>ActionRoom::COLLECTION),
	    	ActionRoom::TYPE_VOTE."s"	=> array("icon"=>"archive","color"=>"#3C5665", "text-color"=>"azure",
	    		 								 "hash"=> "survey.entries.id.",
	    		 								 "collection"=>ActionRoom::COLLECTION),
	    	ActionRoom::TYPE_ACTIONS	=> array("icon"=>"cogs","color"=>"#3C5665", "text-color"=>"lightblue2",
	    		 								 "hash"=> "rooms.actions.id.",
	    		 								 "collection"=>ActionRoom::COLLECTION),
	    	ActionRoom::TYPE_ACTIONS."s"=> array("icon"=>"cogs","color"=>"#3C5665", "text-color"=>"lightblue2",
	    		 								 "hash"=> "rooms.actions.id.",
	    		 								 "collection"=>ActionRoom::COLLECTION),
	    	ActionRoom::TYPE_ACTION		=> array("icon"=>"cog","color"=>"#3C5665", "text-color"=>"lightblue2",
	    		 								 "hash"=> "rooms.action.id.",
	    		 								 "collection"=>ActionRoom::COLLECTION_ACTIONS),
	    	ActionRoom::TYPE_ACTION."s"	=> array("icon"=>"cog","color"=>"#3C5665", "text-color"=>"lightblue2",
	    		 								 "hash"=> "rooms.action.id.",
	    		 								 "collection"=>ActionRoom::COLLECTION_ACTIONS),
	    	ActionRoom::TYPE_ENTRY		=> array("icon"=>"archive","color"=>"#3C5665", "text-color"=>"dark",
	    										 "hash"=> "survey.entry.id.",
	    										 "collection"=>Survey::COLLECTION ),
	    	ActionRoom::TYPE_ENTRY."s"	=> array("icon"=>"archive","color"=>"#3C5665", "text-color"=>"dark",
	    										 "hash"=> "survey.entry.id.",
	    										 "collection"=>Survey::COLLECTION ),
	    	Survey::COLLECTION	=> array("icon"=>"archive","color"=>"#3C5665", "text-color"=>"dark",
	    										 "hash"=> "survey.entry.id.",
	    										 "collection"=>Survey::COLLECTION ),
	    	Survey::CONTROLLER	=> array("icon"=>"archive","color"=>"#3C5665", "text-color"=>"dark",
	    										 "hash"=> "survey.entry.id.",
	    										 "collection"=>Survey::COLLECTION ),
	    	ActionRoom::TYPE_DISCUSS	=> array("icon"=>"comment","color"=>"#3C5665", "text-color"=>"dark",
	    										 "hash"=> "comment.index.type.actionRooms.id.",
	    										 "collection"=>ActionRoom::COLLECTION),
	    	ActionRoom::TYPE_DISCUSS."s"=> array("icon"=>"comment","color"=>"#3C5665", "text-color"=>"dark",
	    										 "hash"=> "comment.index.type.actionRooms.id.",
	    										 "collection"=>ActionRoom::COLLECTION)
	    );	
	    //echo $type.Project::COLLECTION;
	    if( isset($fas[$type]) ) 
	    	return $fas[$type];
	    else 
	    	return false;
    }



    public static function checkIdAndType($id, $type, $actionType=null) {
		if ($type == Organization::COLLECTION) {
        	$res = Organization::getById($id); 
            if (@$res["disabled"] && $actionType != "disconnect") {
                throw new CTKException("Impossible to link something on a disabled organization");    
            }
        } else 
        if ( in_array($type, array( Person::COLLECTION, Project::COLLECTION,Event::COLLECTION, Classified::COLLECTION, Need::COLLECTION,Poi::COLLECTION,Network::COLLECTION) ) ){
            $res = self::getByTypeAndId($type, $id);       
        } else if ($type== ActionRoom::COLLECTION_ACTIONS){
            $res = ActionRoom:: getActionById($id);
        } else if ( $type == Survey::COLLECTION) {
            $res = Survey::getById($id);
        } else if ( $type == Form::COLLECTION) {
            $res = Form::getByIdMongo($id);
        }  else {
        	throw new CTKException("Can not manage this type : ".$type);
        }
        if (empty($res)) throw new CTKException("The actor (".$id." / ".$type.") is unknown");

        return $res;
    }
    

    /**
     * Return a link depending on the type and the id of the element.
     * The HTML link could be kind of : <a href="" onclick="urlCtrl.loadByHash(...)">name</a>
     * If urlCtrl.loadByHashOnly is set : only the urlCtrl.loadByHash will be returned
     * @param String $type The type of the entity
     * @param String $id The id of the entity
     * @param type|null $loadByHashOnly if true, will return only the urlCtrl.loadByHash not surounded by the html link
     * @return String the link on the loaByHash to display the detail of the element
     */
    public static function getLink( $type, $id, $hashOnly=null ) {	    
    	$link = ""; 
    	$specs = self::getElementSpecsByType ($type);
    	if( @$specs["collection"] )
    		$type = $specs["collection"];

    	if(@$type && @$id && $type != City::COLLECTION){
    		if (!$hashOnly)
    			$el = PHDB::findOne ( $type , array( "_id" => new MongoId($id) ),array("name") );
	    	
	    	$link = $specs["hash"].$id;
	    }
	    else if($type == City::COLLECTION){
	    	$el = City::getByUnikey($id);
	    	$link = $specs["hash"].$el['insee'].".postalCode.".$el['cp'];
	    }
	    
	    //if ( !$hashOnly && @$el ) 
	    $link = '<a href="#'.$link.'" class="lbh add2fav">'.htmlspecialchars(@$el['name']).'</a>';
	    
    	return $link;
    }

	public static function getByTypeAndId($type, $id,$what=null){
		if( @$what ) 
			$element = PHDB::findOneById($type, $id, $what);
		else if($type == Person::COLLECTION)
			$element = Person::getById($id);
		else if($type == Organization::COLLECTION || $type == Organization::CONTROLLER  )
			$element = Organization::getById($id);		
		else if($type == Project::COLLECTION || $type == Project::CONTROLLER)
			$element = Project::getById($id);	
		else if($type == Event::COLLECTION || $type == Event::CONTROLLER )
			$element = Event::getById($id);	
		else if($type == City::COLLECTION)
			$element = City::getIdByInsee($id);
		else if($type == Poi::COLLECTION)
			$element = Poi::getById($id);
		else if($type == Place::COLLECTION)
			$element = Place::getById($id);
		else if($type == Classified::COLLECTION || $type == Classified::MODULE)
			$element = Classified::getById($id);
		else if($type == Ressource::COLLECTION || $type == Ressource::MODULE)
			$element = Ressource::getById($id);
		else if($type == ActionRoom::COLLECTION_ACTIONS)
			$element = PHDB::findOne( ActionRoom::COLLECTION_ACTIONS ,array("_id"=>new MongoId($id)));
		else if($type == Survey::CONTROLLER )
			$element = PHDB::findOne( Survey::COLLECTION ,array("_id"=>new MongoId($id)));
		else if($type == Proposal::COLLECTION )
			$element = PHDB::findOne( Proposal::COLLECTION ,array("_id"=>new MongoId($id)));
		else if($type == Action::COLLECTION )
			$element = PHDB::findOne( Action::COLLECTION ,array("_id"=>new MongoId($id)));
		else if($type == Room::COLLECTION )
			$element = PHDB::findOne( Room::COLLECTION ,array("_id"=>new MongoId($id)));
		else if($type == Network::COLLECTION )
			$element = Network::getById($id);
		else if($type == Service::COLLECTION)
			$element = Service::getById($id);
		else
			$element = PHDB::findOne($type,array("_id"=>new MongoId($id)));
	  	
	  	if ($element == null) 
	  		$element = Element::getGhost($type);
	  		//throw new CTKException("The element you are looking for has been moved or deleted");

	  	$el = $element;
		if(@$el["links"]) foreach(array("followers", "follows", "members", "contributors") as $key)
			if(@$el["links"][$key])
			$element["counts"][$key] = count($el["links"][$key]);

	  	return $element;
	}
	public static function getSimpleByTypeAndId($type, $id,$what=null){

		if( @$what ) 
			$element = PHDB::findOneById($type, $id, $what);
		else if($type == Person::COLLECTION)
			$element = Person::getSimpleUserById($id);
		else if($type == Organization::COLLECTION)
			$element = Organization::getSimpleOrganizationById($id);		
		else if($type == Project::COLLECTION)
			$element = Project::getSimpleProjectById($id);	
		else if($type == Event::COLLECTION)
			$element = Event::getSimpleEventById($id);	
		else if($type == City::COLLECTION)
			$element = City::getIdByInsee($id);
		else if($type == Poi::COLLECTION)
			$element = Poi::getById($id);
		else if($type == "action")
			$element = PHDB::findOne("actions",array("_id"=>new MongoId($id)));
		else
			$element = PHDB::findOne($type,array("_id"=>new MongoId($id)));
	  	
	  	if ($element == null) 
	  		$element = Element::getGhost($type);
	  		//throw new CTKException("The element you are looking for has been moved or deleted");
	  	return $element;
	}

	public static function getGhost($type){
		return array("name"=>"Unknown (deleted)", 
					 "slug"=>"unknown",
					 "type"=>$type,
					 "typeSig"=>$type);
	}

	/**
	 * get all poi details of an element
	 * @param type $id : is the mongoId (String) of the parent
	 * @param type $type : is the type of the parent
	 * @return list of pois
	 */
	public static function getByIdAndTypeOfParent($collection, $id, $type, $orderBy,$where=null){
		$condition=array("parentId"=>$id,"parentType"=>$type);
		if(@$where && !empty($where))
			$condition=array_merge($condition, $where);
		$list = PHDB::findAndSort($collection,$condition, $orderBy);
	   	return $list;
	}
	/**
	 * get poi with limit $limMin and $limMax
	 * @return list of pois
	 */
	public static function getByTagsAndLimit($collection, $limitMin=0, $indexStep=15, $searchByTags=""){
		$where = array("name"=>array('$exists'=>1));
		if(@$searchByTags && !empty($searchByTags)){
			$queryTag = array();
			foreach ($searchByTags as $key => $tag) {
				if($tag != "")
					$queryTag[] = new MongoRegex("/".$tag."/i");
			}
			if(!empty($queryTag))
				$where["tags"] = array('$in' => $queryTag); 			
		}
		
		$list = PHDB::findAndSort( $collection, $where, array("updated" => -1));
	   	return $list;
	}

    public static function getInfos( $type, $id, $loadByHashOnly=null ) {	    
    	$link = ""; 
    	$name = ""; 
    	if(@$type && @$id && $type != City::COLLECTION){
    		$el = PHDB::findOne ( $type , array( "_id" => new MongoId($id) ) );
	    	$ctrl = self::getControlerByCollection($type);
	    	if( @$el && @$ctrl )
	    		$link = "urlCtrl.loadByHash('#".$ctrl.".detail.id.".$id."')";
	    }
	    else if($type == City::COLLECTION){
	    	$el = City::getByUnikey($id);
	    	$ctrl = self::getControlerByCollection($type);
	    	if( @$el && @$ctrl )
	    		$link = "urlCtrl.loadByHash('#".$ctrl.".detail.insee.".$el['insee'].".cp.".$el['cp']."')";
	    }
	    
	    if (! $loadByHashOnly) {
	    	$link = "<a href='javascript:;' onclick=\"".$link."\">".$el['name']."</a>";
	    }
	    
    	return array( "link" => $link , 
    					"name" => $el['name'], 
    					"profilThumbImageUrl" => @$el['profilThumbImageUrl'], 
    					"type"=>$type,
    					"id"=> $id);
    }



    private static function getDataBinding($collection) {
		if($collection == Person::COLLECTION)
			return Person::getDataBinding();
		else if($collection == Organization::COLLECTION)
			return Organization::getDataBinding();
		else if($collection == Event::COLLECTION)
			return Event::getDataBinding();
		else if($collection == Project::COLLECTION)
			return Project::getDataBinding();
		else if($collection == Survey::COLLECTION)
			return Survey::getDataBinding();
		else if($collection == Poi::COLLECTION)
			return Poi::getDataBinding();
		else if($collection == Proposal::COLLECTION)
			return Proposal::getDataBinding();
		else if($collection == Action::COLLECTION)
			return Action::getDataBinding();
		else if($collection == Place::COLLECTION)
			return Place::getDataBinding();
		else if($collection == Ressource::COLLECTION)
			return Ressource::getDataBinding();
		else if($collection == Service::COLLECTION)
			return Service::getDataBinding();
		else
			return array();
	}

    private static function getCollectionFieldNameAndValidate($collection, $elementFieldName, $elementFieldValue, $elementId) {
		return DataValidator::getCollectionFieldNameAndValidate(self::getDataBinding($collection), $elementFieldName, $elementFieldValue, $elementId);
	}



    public static function updateField($collection, $id, $fieldName, $fieldValue, $allDay=null) {
    	
    	//error_log("updateField : ".$fieldName." with value :".$fieldValue);
    	if (!Authorisation::canEditItemOrOpenEdition($id, $collection, Yii::app()->session['userId'])) {
			throw new CTKException(Yii::t("common","Can not update the element : you are not authorized to update that element !"));
		}

		if(is_string($fieldValue))
			$fieldValue = trim($fieldValue);

		//Manage boolean allDay. TODO SBAR - Trouver un autre moyen que de le mettre ici
		if ($fieldName == "allDay") {
			if ($fieldValue == "true") 
				$fieldValue = true;
			else
				$fieldValue = false;
		}
		
		$dataFieldName = self::getCollectionFieldNameAndValidate($collection, $fieldName, $fieldValue, $id);
		
		$verb = (empty($fieldValue) ? '$unset' : '$set');
		
		if ($dataFieldName == "name") 
			$fieldValue = $fieldValue;
		if ($dataFieldName == "tags") {
			$fieldValue = Tags::filterAndSaveNewTags($fieldValue);
			$set = array($dataFieldName => $fieldValue);
		}
		else if ( ($dataFieldName == "telephone.mobile"|| $dataFieldName == "telephone.fixe" || $dataFieldName == "telephone.fax")){
			if($fieldValue ==null)
				$fieldValue = array();
			else {
				$split = explode(",", $fieldValue);
				$fieldValue = array();
				foreach ($split as $key => $value) {
					$fieldValue[] = trim($value);
				}
			}
			$set = array($dataFieldName => $fieldValue);
		}
		else if ($fieldName == "locality") {
			//address
			try{
				if(!empty($fieldValue)){
					$verb = '$set';
					$address = array(
						"@type" => "PostalAddress",
						"codeInsee" => $fieldValue["address"]["codeInsee"],
						"addressCountry" => $fieldValue["address"]["addressCountry"],
						"postalCode" => $fieldValue["address"]["postalCode"],
						"addressLocality" => $fieldValue["address"]["addressLocality"],
						"streetAddress" => ((@$fieldValue["address"]["streetAddress"])?trim(@$fieldValue["address"]["streetAddress"]):""),
						"localityId" => $fieldValue["address"]["localityId"],
						"level1" => $fieldValue["address"]["level1"],
						"level1Name" => $fieldValue["address"]["level1Name"],
					);
					
					if(!empty($fieldValue["address"]["level2"])){
						$address["level2"] = $fieldValue["address"]["level2"];
						$address["level2Name"] =((@$fieldValue["address"]["level2Name"])?trim(@$fieldValue["address"]["level2Name"]):"");
					}

					if(!empty($fieldValue["address"]["level3"])){
						$address["level3"] = $fieldValue["address"]["level3"];
						$address["level3Name"] =((@$fieldValue["address"]["level3Name"])?trim(@$fieldValue["address"]["level3Name"]):"");
					}

					if(!empty($fieldValue["address"]["level4"])){
						$address["level4"] = $fieldValue["address"]["level4"];
						$address["level4Name"] =((@$fieldValue["address"]["level4Name"])?trim(@$fieldValue["address"]["level4Name"]):"");
					}

					//Check address is well formated
					$valid = DataValidator::addressValid($address);
					if ( $valid != "") throw new CTKException($valid);

					SIG::updateEntityGeoposition($collection, $id, $fieldValue["geo"]["latitude"], $fieldValue["geo"]["longitude"]);
					
					if($collection == Person::COLLECTION && Yii::app()->session['userId'] == $id){
						$user = Yii::app()->session["user"];
						$user["codeInsee"] = $address["codeInsee"];
						$user["postalCode"] = $address["postalCode"];
						$user["addressCountry"] = $address["addressCountry"];
						//$user["address"] = $address;
						Yii::app()->session["user"] = $user;
						Person::updateCookieCommunexion($id, $address);
					}
					$firstCitizen = Person::isFirstCitizen($fieldValue["address"]["codeInsee"]) ;

					if(!empty($fieldValue["address"]["postalCode"]))
						City::checkAndAddPostalCode ($fieldValue["address"]["localityId"], $fieldValue["address"]["postalCode"]);

				}else{
					$verb = '$unset' ;
					SIG::updateEntityGeoposition($collection, $id, null, null);
					if($collection == Person::COLLECTION && Yii::app()->session['userId'] == $id){
						$user = Yii::app()->session["user"];
						unset($user["codeInsee"]);
						unset($user["postalCode"]);
						unset($user["addressCountry"]);
						//unset($user["address"]);
						Yii::app()->session["user"] = $user;
						//Person::updateCookieCommunexion($id, null);
					}
					$address = null ;
				}
				$set = array("address" => $address);
				
			}catch (Exception $e) {  
				throw new CTKException("Error updating  : ".$e->getMessage());		
			}
		}else if ($fieldName == "addresses") {
			//address
			try{
				if(isset($fieldValue["addressesIndex"])){
					$elt = self::getElementById($id, $collection, null, array("addresses"));
					if(!empty($fieldValue["address"])){
						$verb = '$set';
						$address = array(
					        "@type" => "PostalAddress",
					        "codeInsee" => $fieldValue["address"]["codeInsee"],
					        "addressCountry" => $fieldValue["address"]["addressCountry"],
					        "postalCode" => $fieldValue["address"]["postalCode"],
					        "addressLocality" => $fieldValue["address"]["addressLocality"],
					        "streetAddress" => ((@$fieldValue["address"]["streetAddress"])?trim(@$fieldValue["address"]["streetAddress"]):""),
					        "localityId" => $fieldValue["address"]["localityId"],
							"level1" => $fieldValue["address"]["level1"],
							"level1Name" => $fieldValue["address"]["level1Name"],
						);

						if(!empty($fieldValue["address"]["level2"])){
							$address["level2"] = $fieldValue["address"]["level2"];
							$address["level2Name"] =((@$fieldValue["address"]["level2Name"])?trim(@$fieldValue["address"]["level2Name"]):"");
						}

						if(!empty($fieldValue["address"]["level3"])){
							$address["level3"] = $fieldValue["address"]["level3"];
							$address["level3Name"] =((@$fieldValue["address"]["level3Name"])?trim(@$fieldValue["address"]["level3Name"]):"");
						}

						if(!empty($fieldValue["address"]["level4"])){
							$address["level4"] = $fieldValue["address"]["level4"];
							$address["level4Name"] =((@$fieldValue["address"]["level4Name"])?trim(@$fieldValue["address"]["level4Name"]):"");
						}
						//Check address is well formated

						$valid = DataValidator::addressValid($address);
						if ( $valid != "") throw new CTKException($valid);

						
						if(empty($elt["addresses"]) || $fieldValue["addressesIndex"] >= count($elt["addresses"]) ){
							$geo = array("@type"=>"GeoCoordinates", "latitude" => $fieldValue["geo"]["latitude"], "longitude" => $fieldValue["geo"]["longitude"]);
							$geoPosition = array("type"=>"Point", "coordinates" => array(floatval($fieldValue["geo"]["longitude"]), floatval($fieldValue["geo"]["latitude"])));
							$locality = array(	"address" => $address,
												"geo" => $geo,
												"geoPosition" => $geoPosition);
							$addToSet = array("addresses" => $locality);
							$verbActivity = ActStr::VERB_ADD ;
						}
						else{
							SIG::updateEntityGeoposition($collection, $id, $fieldValue["geo"]["latitude"], $fieldValue["geo"]["longitude"], $fieldValue["addressesIndex"]);
							$headSet = "addresses.".$fieldValue["addressesIndex"].".address" ;
						}

					}else{
						$verb = '$unset' ;
						$verbActivity = ActStr::VERB_DELETE ;
						//SIG::updateEntityGeoposition($collection, $id, null, null, $fieldValue["addressesIndex"]);
						$address = null ;
						if(count($elt["addresses"]) == 1){
							$headSet = "addresses";
						}else{
							$headSet = "addresses.".$fieldValue["addressesIndex"] ;
							$updatePull = true ;
							$pull="addresses";
						}
					}

					if(!empty($headSet))
						$set = array($headSet => $address);

				}else{
					throw new CTKException("Error updating  : addressesIndex ");	
				}
			}catch (Exception $e) {  
				throw new CTKException("Error updating  : ".$e->getMessage());		
			}
		}else if ($fieldName == "geo" || $fieldName == "geoPosition") {
			try{
				if(!empty($fieldValue["addressesIndex"])){
					$headSet = "addresses.".$fieldValue["addressesIndex"].".".$fieldName ;
					unset($fieldValue["addressesIndex"]);
				}
				else
					$headSet = $fieldName ;

				$verb = (!empty($fieldValue)?'$set':'$unset');
				$geo = (!empty($fieldValue)?$fieldValue:null);

				$valid = (($fieldName == "geo")?DataValidator::geoValid($geo):DataValidator::geoPositionValid($geo));
				if ( $valid != "") throw new CTKException($valid);

				
				$set = array($headSet => $geo);

			}catch (Exception $e) {  
				throw new CTKException("Error updating  : ".$e->getMessage());		
			}
		}
		
		/*else if ($dataFieldName == "birthDate") {
			date_default_timezone_set('UTC');
			$dt = DateTime::createFromFormat('Y-m-d H:i', $fieldValue);
			if (empty($dt)) {
				$dt = DateTime::createFromFormat('Y-m-d', $fieldValue);
			}
			$newMongoDate = new MongoDate($dt->getTimestamp());
			$set = array($dataFieldName => $newMongoDate);

		//Date format
		}*/ else if ($dataFieldName == "startDate" || $dataFieldName == "endDate" || $dataFieldName == "birthDate") {
			date_default_timezone_set('UTC');
			$dt = DataValidator::getDateTimeFromString($fieldValue, $dataFieldName);

			if ($dataFieldName == "startDate" && @$allDay && $allDay==true)
				$dt=date_time_set($dt, 00, 00);
			if ($dataFieldName == "endDate" && @$allDay && $allDay==true) 
				$dt=date_time_set($dt, 23, 59);
			$newMongoDate = new MongoDate($dt->getTimestamp());
			$set = array($dataFieldName => $newMongoDate);
		} else if ($dataFieldName == "organizer") {
			$set = array("organizerId" => $fieldValue["organizerId"], 
							 "organizerType" => $fieldValue["organizerType"]);
			//get element and remove current organizer
			//var_dump($fieldValue);
			//exit;
			$element = self::getElementById($id, $collection);
			
			if( !empty($element["organizerId"]) || !empty($element["links"]["organizer"]) ){
				$oldOrganizerId = @$element["organizerId"] ? $element["organizerId"] : $element["links"]["organizer"];
				$oldOrganizerType = @$element["organizerType"] ? $element["organizerType"] : $element["links"]["organizer"][$oldOrganizerId]["type"];
				//remove the old organizer
				$res = Link::removeOrganizer($oldOrganizerId, $oldOrganizerType, $id, Yii::app()->session["userId"]);
				if (! @$res["result"]) throw new CTKException(@$res["msg"]);
			}
			//add new organizer
			if($fieldValue["organizerId"] != 'dontKnow' && $fieldValue["organizerType"] != 'dontKnow')
				$res = Link::addOrganizer($fieldValue["organizerId"], $fieldValue["organizerType"], $id, Yii::app()->session["userId"]);
			if (!empty($res) && ! @$res["result"]) throw new CTKException(@$res["msg"]);

		}else if ($dataFieldName == "parent") {
			
			//get element and remove current parent
			$element = Element::getElementById($id, $collection);

			if( !empty($element["parentId"]) || !empty($element["links"]["parent"]) ){

				$oldParentId = @$element["parentId"] ? $element["parentId"] : $element["links"]["parent"];
				$oldParentType = @$element["parentType"] ? $element["parentType"] : $element["links"]["parent"][$oldParentId]["type"];
				//remove the old parent
				$res = Link::removeParent($oldParentId, $oldParentType, $id, $collection, Yii::app()->session["userId"]);
				if (! @$res["result"]) throw new CTKException(@$res["msg"]);
			}
			//add new parent
			if($fieldValue["parentId"] != 'dontKnow' && $fieldValue["parentType"] != 'dontKnow'){
				$set = array("parentId" => $fieldValue["parentId"],
							 "parentType" => $fieldValue["parentType"]);
				$res = Link::addParent($fieldValue["parentId"], $fieldValue["parentType"], $id, $collection, Yii::app()->session["userId"]);
			}else{
				$verb == '$unset';
				$set = array("parentId" => "",
							 "parentType" => "");
			}

			if (!empty($res) && ! @$res["result"]) throw new CTKException(@$res["msg"]);

		} else if ($dataFieldName == "seePreferences") {
			//var_dump($fieldValue);
			if($fieldValue == "false"){
				$verb = '$unset' ;
				$set = array($dataFieldName => "");
			}else{
				$set = array($dataFieldName => $fieldValue);
			}
		} else if ($dataFieldName == "contacts") {
			if(!isset($fieldValue["index"]))
				$addToSet = array("contacts" => $fieldValue);
			else{
				$headSet = "contacts.".$fieldValue["index"] ;
				unset($fieldValue["index"]);
				if(count($fieldValue) == 0){
					$verb = '$unset' ;
					$verbActivity = ActStr::VERB_DELETE ;
					$fieldValue = null ;
					$updatePull = true ;
					$pull="contacts";
				}
				$set = array($headSet => $fieldValue);
			}
		} else if ($dataFieldName == "urls") {
			if(!isset($fieldValue["index"]))
				$addToSet = array("urls" => $fieldValue);
			else{
				$headSet = "urls.".$fieldValue["index"] ;
				unset($fieldValue["index"]);
				if(count($fieldValue) == 0){
					$verb = '$unset' ;
					$verbActivity = ActStr::VERB_DELETE ;
					$fieldValue = null ;
					$updatePull = true ;
					$pull="urls";
				}
				$set = array($headSet => $fieldValue);

			}
		} else
			$set = array($dataFieldName => $fieldValue);

		if ($verb == '$set') {
			$set["modified"] = new MongoDate(time());
			$set["updated"] = time();
		} else {
			$setModified = array();
			$setModified["modified"] = new MongoDate(time());
			$setModified["updated"] = time();
		}


		
		//Manage dateEnd field for survey
		if ($collection == Survey::COLLECTION) {
			$canUpdate = Survey::canUpdateSurvey($id, $dataFieldName, $fieldValue);
			if ($canUpdate["result"]) {
				if ($dataFieldName == "dateEnd") {
					$set = array($dataFieldName => strtotime($fieldValue));
				}
			} else {
				throw new CTKException($canUpdate["msg"]);
			}
		}

		//update 
		if(!empty($addToSet)){
			$resAddToSet = PHDB::update( $collection, array("_id" => new MongoId($id)), 
	                          array('$addToSet' => $addToSet));
		}

		if ($verb == '$set') {
			$resUpdate = PHDB::update( $collection, array("_id" => new MongoId($id)), 
			                          array($verb => $set));
		} else {
			$resUpdate = PHDB::update( $collection, array("_id" => new MongoId($id)), 
			                          array($verb => $set, '$set' => $setModified));
		}
		$res = array("result"=>false,"msg"=>"");
		if($resUpdate["ok"]==1){

			if(!empty($updatePull) && $updatePull == true){

				$resPull = PHDB::update( $collection, array("_id" => new MongoId($id)), 
		                          array('$pull' => array($pull => null)));
			}

			$fieldNames = array("badges", "geo", "geoPosition");
			if( $collection != Person::COLLECTION && !in_array($dataFieldName, $fieldNames)){
				// Add in activity to show each modification added to this entity
				if(empty($verbActivity))
					$verbActivity = ActStr::VERB_UPDATE ;
				ActivityStream::saveActivityHistory($verbActivity, $id, $collection, $dataFieldName, $fieldValue);
			}
			$msg=Yii::t(self::getControlerByCollection($collection),"The ".self::getControlerByCollection($collection)." has been updated");
			if ($fieldName == "language"){
				Yii::app()->language=$fieldValue;
				$msg=Yii::t("common","Changing language processing"); 
			}
			$res = array("result"=>true,"msg"=>$msg, "fieldName" => $fieldName, "value" => $fieldValue);

			if(isset($firstCitizen))
				$res["firstCitizen"] = $firstCitizen ;
		}else{
			throw new CTKException("Can not update the element!");
		}
		return $res;
	}

	public static function getImgProfil($person, $imgName, $assetUrl){
    	$url = "";
    	$testUrl = "";
    	if (isset($person) && !empty($person)) {
	        if(!empty($person[$imgName])){	
	          $url = Yii::app()->getRequest()->getBaseUrl(true).$person[$imgName];
	          $end = strpos($person[$imgName], "?");
	          if($end<0) $end = strlen($person[$imgName]);
	          $testUrl = substr($person[$imgName], 1, $end-1);
	        }
	        else{
	          $url = $assetUrl.'/images/thumbnail-default.jpg';
	          $testUrl = substr($url, 1);
	        }
	    }
	    return $url;
	    //echo $testUrl;
	    //error_log($testUrl);
	    //if(file_exists($testUrl)) return $url;
	    //else return $assetUrl.'/images/thumbnail-default.jpg';
    }

    public static function getAllLinksOld($links,$type, $id){
	    $contextMap = array();
		/*$contextMap["people"] = array();
		$contextMap["guests"] = array();
		$contextMap["attendees"] = array();
		$contextMap["organizations"] = array();
		$contextMap["projects"] = array();
		$contextMap["events"] = array();
		$contextMap["followers"] = array();*/


	    if($type == Organization::COLLECTION){
	    	$connectAs="members";
	    	$elt = Organization::getSimpleOrganizationById($id);
			$newOrga["type"]=Organization::COLLECTION;
			$contextMap[$elt["id"]] = $elt;
	    }
	    else if($type == Project::COLLECTION){
	    	$connectAs="contributors";
	    	$elt = Project::getSimpleProjectById($id);
	    	$contextMap[$elt["id"]] = $elt;
	    }
		else if ($type == Event::COLLECTION){
			$connectAs="attendees";
			$elt = Event::getSimpleEventById($id);
			$contextMap[$elt["id"]] = $elt;
		}
		else if ($type == Person::COLLECTION){
			$connectAs="follows";
			$elt = Person::getSimpleUserById($id);
			$contextMap[$elt["id"]] = $elt;
		}

		
	    
		if(!empty($links) && 
			( (Preference::showPreference($elt, $type, "directory", Yii::app()->session["userId"]) && 
			  $type == Person::COLLECTION ) || 
			  $type != Person::COLLECTION) 
		  ) {
			if(isset($links[$connectAs])){
				foreach ($links[$connectAs] as $key => $aMember) {
					//if($type==Event::COLLECTION){
						$citoyen = Person::getSimpleUserById($key);
						if(!empty($citoyen)){
							if(@$aMember["invitorId"])  {
								$contextMap[$citoyen["id"]] = $citoyen;
							}
							else{
								if(@$e["isAdmin"]){
									if(@$e["isAdminPending"])
										$citoyen["isAdminPending"]=true;
									$citoyen["isAdmin"]=true;         
								}
								$contextMap[$citoyen["id"]] = $citoyen;
							}
						}else{
							if($aMember["type"]==Organization::COLLECTION){
								$newOrga = Organization::getSimpleOrganizationById($key);
								if(!empty($newOrga)){
									if ($aMember["type"] == Organization::COLLECTION && @$aMember["isAdmin"]){
										$newOrga["isAdmin"]=true;  				
									}
									$newOrga["type"]=Organization::COLLECTION;
									if (!@$newOrga["disabled"]) {
										$contextMap[$newOrga["id"]] = $newOrga;
									}
								}
							} 
							else if($aMember["type"]==Person::COLLECTION){
								$newCitoyen = Person::getSimpleUserById($key);
								if (!empty($newCitoyen)) {
									if (@$aMember["type"] == Person::COLLECTION) {
										if(@$aMember["isAdmin"]){
											if(@$aMember["isAdminPending"])
												$newCitoyen["isAdminPending"]=true;  
												$newCitoyen["isAdmin"]=true;  	
										}			
										if(@$aMember["toBeValidated"]){
											$newCitoyen["toBeValidated"]=true;  
										}
									}
									$newCitoyen["type"]=Person::COLLECTION;
									$contextMap[$newCitoyen["id"]] = $newCitoyen;
								}
							}
						}
					//}
				}
			}
			// Link with events
			if(isset($links["events"])){
				foreach ($links["events"] as $keyEv => $valueEv) {
					$event = Event::getSimpleEventById($keyEv);
					//if(!empty($event))
					if(!empty($event) && !empty($event["endDate"]) && strtotime($event["endDate"]) > strtotime("now") )
						$contextMap[$event["id"]] = $event;
				}
			}
	
			// Link with projects
			if(isset($links["projects"])){
				foreach ($links["projects"] as $keyProj => $valueProj) {
					$project = Project::getSimpleProjectById($keyProj);
					if(!empty($project))
						$contextMap[$project["id"]] = $project;
				}
			}

			if(isset($links["followers"])){
				foreach ($links["followers"] as $key => $value) {
					$newCitoyen = Person::getSimpleUserById($key);
					if(!empty($newCitoyen))
						$contextMap[$newCitoyen["id"]] = $newCitoyen;
				}
			}

			if(isset($links["memberOf"])){
				foreach ($links["memberOf"] as $key => $value) {
					$newOrga = Organization::getSimpleOrganizationById($key);
					if(!empty($newOrga))
						$contextMap[$newOrga["id"]] = $newOrga;
				}
			}

			if(isset($links["subEvents"])){
				foreach ($links["subEvents"] as $keyEv => $valueEv) {
					$event = Event::getSimpleEventById($keyEv);
					if(!empty($event) && !empty($event["endDate"]) && strtotime($event["endDate"]) > strtotime("now") )
						$contextMap[$event["id"]] = $event;
				}
			}

			/*$follows = array("citoyens"=>array(),
  					"projects"=>array(),
  					"organizations"=>array(),
  					"count" => 0
  			);*/
  			if ($type == Person::COLLECTION){
			    if (@$links["follows"]) {
			        foreach ( @$links["follows"] as $key => $member ) {
			          	if( $member['type'] == Person::COLLECTION ) {
				            $citoyen = Person::getSimpleUserById( $key );
				  	        if(!empty($citoyen)) {
				  	        	$contextMap[$citoyen["id"]] = $citoyen;
				            }
			        	}

						if( $member['type'] == Organization::COLLECTION ) {
							$organization = Organization::getSimpleOrganizationById($key);
							if(!empty($organization)) {
								$contextMap[$organization["id"]] = $organization;
							}
						}

						if( $member['type'] == Project::COLLECTION ) {
						    $project = Project::getSimpleProjectById($key);
						    if(!empty($project)) {
								$contextMap[$project["id"]] = $project;
							}
						}
		        	}
				}
			}
		}
		//error_log("get POI for id : ".$id." - type : ".$type);
		/*if(isset($id)){
			$pois = PHDB::find(Poi::COLLECTION,array("parentId"=>$id,"parentType"=>$type));
			if(!empty($pois)) {
				$allPois = array();
				if(!is_array($pois)) $pois = array($pois);
				foreach ($pois as $key => $value) {
					if(@$value["type"])
						$value["typeSig"] = Poi::COLLECTION.".".$value["type"];
					else
						$value["typeSig"] = Poi::COLLECTION;
					$contextMap[(String) $value["_id"]] = $value;
				}
				
			}
		}*/
		return $contextMap;	
    }

    // public static function getLinksById($id,$type){
    // 	var_dump($type);
    // 	var_dump($id);
    // 	$links = PHDB::findOneById( $type, $id, array("links"));
    // 	var_dump($links);exit;
    // 	return $links;
    // }

    public static function getAllLinks($links,$type, $id){
	    $contextMap = array();
		/*$contextMap["people"] = array();
		$contextMap["guests"] = array();
		$contextMap["attendees"] = array();
		$contextMap["organizations"] = array();
		$contextMap["projects"] = array();
		$contextMap["events"] = array();
		$contextMap["followers"] = array();*/


	    if($type == Organization::COLLECTION){
	    	$connectAs="members";
	    	$elt = Organization::getSimpleOrganizationById($id);
			$newOrga["type"]=Organization::COLLECTION;
			$contextMap[$elt["id"]] = $elt;
	    }
	    else if($type == Project::COLLECTION){
	    	$connectAs="contributors";
	    	$elt = Project::getSimpleProjectById($id);
	    	$contextMap[$elt["id"]] = $elt;
	    }
		else if ($type == Event::COLLECTION){
			$connectAs="attendees";
			$elt = Event::getSimpleEventById($id);
			$contextMap[$elt["id"]] = $elt;
		}
		else if ($type == Person::COLLECTION){
			$connectAs="follows";
			$elt = Person::getSimpleUserById($id);
			$contextMap[$elt["id"]] = $elt;
		}		
	    
		if(!empty($links) && 
			( (Preference::showPreference($elt, $type, "directory", Yii::app()->session["userId"]) && 
			  $type == Person::COLLECTION ) || 
			  $type != Person::COLLECTION) 
		  ) {
			if(isset($links[$connectAs])){
				foreach ($links[$connectAs] as $key => $aMember) {
					$citoyen = Person::getSimpleUserById($key);
					if(!empty($citoyen)){
						if(@$aMember["invitorId"])  {
							$contextMap[$citoyen["id"]] = $citoyen;
						}
						else{
							if(@$e["isAdmin"]){
								if(@$e["isAdminPending"])
									$citoyen["isAdminPending"]=true;
								$citoyen["isAdmin"]=true;         
							}
							$contextMap[$citoyen["id"]] = $citoyen;
						}
					}else{
						if($aMember["type"]==Organization::COLLECTION){
							$valLink[Organization::COLLECTION][] = new MongoId($key) ;
						} 
						else if($aMember["type"]==Person::COLLECTION){
							$valLink[Person::COLLECTION][] = new MongoId($key) ;
						}
					}
				}
			}

			$valLink = array();
			// Link with events
			if(isset($links["events"])){
				foreach ($links["events"] as $keyEv => $valueEv) {
					$valLink[Event::COLLECTION][] = new MongoId($keyEv) ;
				}
			}

			if(isset($links["subEvents"])){
				foreach ($links["subEvents"] as $keyEv => $valueEv) {
					$valLink[Event::COLLECTION][] = new MongoId($keyEv) ;
				}
			}

			// Link with projects
			if(isset($links["projects"])){
				foreach ($links["projects"] as $keyProj => $valueProj) {
					$valLink[Project::COLLECTION][] = new MongoId($keyProj) ;
				}
			}
	
			if(isset($links["followers"])){
				foreach ($links["followers"] as $key => $value) {
					$valLink[Person::COLLECTION][] = new MongoId($key) ;
				}
			}

			if(isset($links["memberOf"])){
				foreach ($links["memberOf"] as $key => $value) {
					$valLink[Organization::COLLECTION][] = new MongoId($key) ;
				}
			}

  			if ($type == Person::COLLECTION){
			    if (@$links["follows"]) {
			        foreach ( @$links["follows"] as $key => $member ) {
			          	if( $member['type'] == Person::COLLECTION )
				            $valLink[Person::COLLECTION][] = new MongoId($key) ;

						if( $member['type'] == Organization::COLLECTION )
							$valLink[Organization::COLLECTION][] = new MongoId($key) ;

						if( $member['type'] == Project::COLLECTION )
						    $valLink[Project::COLLECTION][] = new MongoId($key) ;
		        	}
				}
			}
			
			$fieldsPer =array("id", "name", "username", "email", "roles", "tags", "profilImageUrl", "profilThumbImageUrl", "profilMarkerImageUrl");

			$fieldsOrg = array("id" , "name" , "type" , "email" , "url" , "shortDescription" , "description" , "address" , "pending" , "tags" , "geo" , "updated" , "profilImageUrl" , "profilThumbImageUrl" , "profilMarkerImageUrl" ,"profilMediumImageUrl" , "addresses", "telephone", "slug");

			$fieldsPro = array("id", "name", "shortDescription", "description", "address", "geo", "tags", "profilImageUrl", "profilThumbImageUrl", "profilMarkerImageUrl", "profilMediumImageUrl", "addresses");

			$fieldEve = array("id", "name", "type",  "shortDescription", "description", "address", "geo", "tags", "profilImageUrl", "profilThumbImageUrl", "profilMarkerImageUrl", "profilMediumImageUrl", "startDate", "endDate", "addresses", "allDay");

			if( !empty($valLink) ) {
				foreach ($valLink as $type => $valLink) {
					$contactsComplet = null;
					if($type == Person::COLLECTION)
						$contactsComplet = Person::getByArrayId($valLink, $fieldsPer, true, true); 
					if($type == Organization::COLLECTION)
						$contactsComplet = Organization::getByArrayId($valLink, $fieldsOrg, true);
					if($type == Project::COLLECTION)
						$contactsComplet = Project::getByArrayId($valLink, $fieldsPro, true);
					if($type == Event::COLLECTION)
						$contactsComplet = Event::getByArrayId($valLink, $fieldEve, true);

					if(!empty($contactsComplet))
						$contextMap = array_merge($contextMap, $contactsComplet);					
				}
			}

			if(isset($links[$connectAs])){
				foreach ($links[$connectAs] as $key => $aMember) {
					if(!empty($contextMap[$key])){
						if($aMember["type"] == Organization::COLLECTION && @$aMember["isAdmin"])
							$contextMap[$key]["isAdmin"]=true;

						else if (@$aMember["type"] == Person::COLLECTION) {
							if(@$aMember["isAdmin"]){
								if(@$aMember["isAdminPending"])
									$contextMap[$key]["isAdminPending"]=true;  
								$contextMap[$key]["isAdmin"]=true;  	
							}			
							if(@$aMember["toBeValidated"]){
								$contextMap[$key]["toBeValidated"]=true;  
							}
						}
					}
				}
			}

		}
		//
		
		//error_log("get POI for id : ".$id." - type : ".$type);
		/*if(isset($id)){
			$pois = PHDB::find(Poi::COLLECTION,array("parentId"=>$id,"parentType"=>$type));
			if(!empty($pois)) {
				$allPois = array();
				if(!is_array($pois)) $pois = array($pois);
				foreach ($pois as $key => $value) {
					if(@$value["type"])
						$value["typeSig"] = Poi::COLLECTION.".".$value["type"];
					else
						$value["typeSig"] = Poi::COLLECTION;
					$contextMap[(String) $value["_id"]] = $value;
				}
				
			}
		}*/
		return $contextMap;	
    }

    public static function getActive($type){
        $list = PHDB::findAndSort( $type ,array("updated"=>array('$exists'=>1)),array("updated"=>1), 4);
        return $list;
     }


    /**
	 * answers to show or not to show a field by it's name
	 * @param String $id : is the mongoId of the action room
	 * @param String $person : is the mongoId of the action room
	 * @return "" or the value to be shown
	 */
	public static function showField($fieldName,$element, $isLinked) {
	  	
	  	$res = null;

	  	$attConfName = $fieldName;
	  	if($fieldName == "address.streetAddress") 	$attConfName = "locality";
	  	if($fieldName == "telephone") 				$attConfName =  "phone";

	  	if( Yii::app()->session['userId'] == (string)$element["_id"]
	  		||  ( isset($element["preferences"]) && isset($element["preferences"]["publicFields"]) && in_array( $attConfName, $element["preferences"]["publicFields"]) )  
	  		|| ( $isLinked && isset($element["preferences"]) && isset($element["preferences"]["privateFields"]) && in_array( $attConfName, $element["preferences"]["privateFields"]))  )
	  	{
	  		$res = ArrayHelper::getValueByDotPath($element,$fieldName);
	  	
	  	}
	  	
	  	return $res;
     
	}
	/**
		* Put last timestamp on element label 
		* label ex: update, lastInvitation
	**/
	public static function updateTimeElementByLabel($elementType,$elementId, $label) {
		PHDB::update($elementType, 
			array("_id" => $elementId) , 
            array('$set' => array( $label =>time()))
		);
		return true;
	}

	/**
		Get communtiy of an element, complete or linked to specific search
		* @param String $typeCommunity : get specific type of element in a community
		* @param String $attribute defined the kind of community : members / admin / pending
		* @param String $role : get sepecific member with role,
		* @param String $settings : in order to get specific community towards a notifications and emails settings
	**/
	public static function getCommunityByTypeAndId($type, $id, $typeCommunity="all", $attribute=null, $role=null, $settings=null) {
		$res = array();
	  	$element = self::getElementSimpleById($id, $type, null, array("links"));
	  	if (empty($element)) {
            throw new CTKException(Yii::t("common", "The id of {what} is unkown : please contact us to fix this problem", array("{what}"=>Yii::t("common","this ".self::getControlerByCollection($type)))));
        }
	  	if ( @$element && @$element["links"] && @Link::$linksTypes[$type] && @$element["links"][Link::$linksTypes[$type][Person::COLLECTION]] ) 
	  	{
	  		$community = array();
	  		foreach ($element["links"][Link::$linksTypes[$type][Person::COLLECTION]] as $key => $value) {
	  			$add=false;
	  			if(!@$value["toBeValidated"] && !@$value["isInviting"]){
		        	
			        $add = ($typeCommunity=="all" || $value['type'] == $typeCommunity) ? true : false;
			        if($add && $attribute !== null){  
			        	if($attribute=="isAdmin" && @$value["isAdmin"] && !@$value["isAdminPending"])
			        		$add = (empty($role) || (!empty($role) && @$value["roles"] && in_array($role, $value["roles"]))) ? true : false;
			        	else if($attribute=="onlyMembers" && !@$value["isAdmin"])
			        		$add = (empty($role) || (!empty($role) && @$value["roles"] && in_array($role, $value["roles"]))) ? true : false;
			        	else
			        		$add=false;
			        	//$searchInAttribute=false;
			        }
			        if($add && $role !== null){
			        	if(@$value["roles"] && in_array($role, $value["roles"]))
			        		$add=true;
			        	else
			        		$add=false;
			        }
			        if($add && $settings !== null){
			        	if(@$value[$settings["type"]]){
			        		if($settings["value"]=="high" && $value[$settings["type"]]=="high")
			        			$add=true;
			        		else if($settings["value"]=="default" && in_array($value[$settings["type"]],["default","high"]))
			        			$add=true;
			        		else if($settings["value"]=="low" && $value[$settings["type"]] != "desactivated")
			        			$add=true;
			        		else
			        			$add=false;

			        	}
			        	else if(in_array($settings["value"], ["low", "default"]))
			        		$add=true;
			        	else
			        		$add=false;
			        }
			    }
			  	if($add){
		    		if(@$settings && $settings["type"]=="mails")
	        			$res[$key]= Element::getElementSimpleById($key, Person::COLLECTION, null, array("email", "username", "language")); 
	        		else
	        			$res[$key] = $value;
	        	}
	  		}
	  	}
	  	return $res;
	}

	/**
	 * Demande la suppression d'un élément
	 * - Si creator demande la suppression et organisation vide (pas de links, pas de members) => suppression de l’orga
	 * - Si superadmin => suppression direct
	 * - Si edition libre sans admin 
	 * 		- Mail + notification envoyée aux super admins + creator
	 * - Si admins > 0 pour l’orga :
	 * 		- envoi d’un mail + notification aux admins
	 * 		- L’orga est en attente de validation de suppression pendant X jours. Un des admins peut venir et bloquer la suppression pendant ce laps de temps. 
	 * 		- Après X jours, un batch passe et supprime l’organisation
	 * 		- Notifications des admins après suppression
	 * @param String $elementType : element type
	 * @param String $elementId : element Id
	 * @param String $reason : reason why the element can be deleted
	 * @param String $userId : the userId asking to delete the element
	 * @return array : result : boolean, msg : String
	 */
	public static function askToDelete($elementType, $elementId, $reason, $userId,$elemTypes) {
		if (! Authorisation::canDeleteElement($elementId, $elementType, $userId)) {
			return array("result" => false, "msg" => "The user cannot delete this element !");
		}

		$res = array("result" => false, "msg" => "Something bad happend : impossible to delete this element");

		//What type of element i can delete
		$managedTypes = array(Organization::COLLECTION, 
							Project::COLLECTION, 
							Event::COLLECTION, 
							Classified::COLLECTION,
							Proposal::COLLECTION, 
							Action::COLLECTION, 
							Room::COLLECTION);
		
		if (!in_array($elementType, $managedTypes)) 
			return array( "result" => false, "msg" => "Impossible to delete this type of element" );

		$modelElement = self::getModelByType($elementType);

		$canBeDeleted = false;
		$element = self::getByTypeAndId($elementType, $elementId);
		
		if (@$element["status"]	== self::STATUS_DELETE_PEDING) 
			return array("result" => false, "msg" => "The element is already in delete pending status !");

		//retrieve admins of the element
		$admins = array();

		if (isset($element["links"])) {
			foreach (@$element["links"] as $type => $links) {
				if (is_array($links)) {
					foreach ($links as $id => $aLink) {
						if (@$aLink["type"] == Person::COLLECTION && @$aLink["isAdmin"] == true) {
							array_push($admins, $id);
						}
					}
				}
			}
		}
		
		$creator = empty($element["creator"]) ? "" : $element["creator"];

		//Check if the creator is the user asking to delete the element
		if ($creator == $userId) {
			// If almost empty element (no links expect creator as member) => delete the element
			if (count(@$element["links"]) == 0) {
				$canBeDeleted = true;
			} else if (count(@$element["links"]["members"]) == 1) {
				$canBeDeleted = isset($element["links"]["members"][$creator]);
			} else if(count($admins) == 1) {
				$canBeDeleted = in_array($creator, $admins);
			}
		//If open data without admin => the super admin will statut
		} else if ((@$element["preferences"]["isOpenData"] == true || 
					@$element["preferences"]["isOpenData"] == 'true' ) && 
					count($admins) == 0) {
			$canBeDeleted = false;
		}

		$DDATypes = array(	Proposal::COLLECTION, 
							Action::COLLECTION, 
							Resolution::COLLECTION,
							Room::COLLECTION);

		if (in_array($elementType, $DDATypes)) 
			$canBeDeleted = true;

		// If the userId is superAdmin : element can be deleted as well
		if (Authorisation::isUserSuperAdmin($userId)) {
			$canBeDeleted = true;
		}

		//var_dump($element["links"]); exit;

		//Try to delete the element
		if ($canBeDeleted) {
			$res = self::deleteElement($elementType, $elementId, $reason, $userId);
		} else {
			//If open data without admin
			if ((@$element["preferences"]["isOpenData"] == true || @$element["preferences"]["isOpenData"] == 'true' ) && count($admins) == 0)  {
				//Ask the super admins to act for the deletion of the element
				$adminsId = array();
				$superAdmins = Person::getCurrentSuperAdmins();
				foreach ($superAdmins as $id => $aPerson) {
					array_push($adminsId, $id);
				}
				error_log("Pour la suppression de l'élément ".$elementType."/".$elementId." : on demande aux super admins");
				$res = self::goElementDeletePending($elementType, $elementId, $reason, $adminsId, $userId,true);
			}

			//If at least one admin => ask if one of the admins want to stop the deletion. The element is mark as pending deletion. After X days, if no one block the deletion => the element if deleted
			if (count($admins) > 0) {
				error_log("Pour la suppression de l'élément ".$elementType."/".$elementId." : on demande aux admins de l'élément");
				$res = self::goElementDeletePending($elementType, $elementId, $reason, $admins, $userId, false);
			}
		}

		return $res;
	}

	/**
	 * Suppression de l'élément et de ses liens. 
	 * - Suppression des liens 
	 * 		- Persons : followers / member / memberOf
	 * 		- Projects :  links.contributor. Vider le parentId+parentType
	 *		- Event : links.events, vider le organizerId et organizerType
	 * 		- Organization : member / memberOf
	 * - Suppresion des Documents 
	 * 		- Supprimer les images de profil 
	 * - Vider le activityStream de type history
	 * - Suppression des News, Actions, Surveys, ActionRooms, Comments
	 * @param type $elementType : type d'élément
	 * @param type $elementId : id of the element
	 * @param type $reason : reason of the deletion
	 * @param type $userId : userId making the deletion
	 * @return array result : bool, msg : message
	 */
	public static function deleteElement($elementType, $elementId, $reason, $userId) {
		
		if (! Authorisation::canDeleteElement($elementId, $elementType, $userId)) {
			return array("result" => false, "msg" => Yii::t('common', "You are not allowed to delete this element !"));
		}

		//array to know likeTypes to their backwards link. Ex : a person "members" type link got a memberOf link in his collection
		$linksTypes = array(
			Person::COLLECTION => 
				array(	"followers" => "follow", 
						"members" => "memberOf",
						"follow" => "followers",
						"attendees" => "events",
						"helpers" => "needs",
						"contributors" => "projects"),
			Organization::COLLECTION => 
				array(	"memberOf" => "member",
						"members" =>"memberOf",
						"follow" => "followers",
						"contributors" => "projects"),
			Event::COLLECTION => 
				array("events" => "organizer"),
			Project::COLLECTION =>
				array("projects" => "contributors"),
			//TODO : pb with links in needs collection. the parentType is used on the linkType. Better use "needer" or parent.
			Need::COLLECTION => 
				array(	"needs" => "organizations",
						"needs" => "helpers"),
			);
		
		$elementToDelete = self::getByTypeAndId( $elementType, $elementId );

		$resError = array(
    		"result" => false, 
    		"action" => array( "start_delete" => array( 
    						"type"=>$elementType, 
    						"id"=>$elementId,
    						"name" => $elementToDelete["name"]
    					)), 
    		"msg" => Yii::t('common',"Error trying to delete this element : please contact your administrator."),
    	);

		//Remove Documents => Profil Images
		//TODO SBAR : Remove other images ?
    	$profilImages = Document::listMyDocumentByIdAndType($elementId, $elementType, Document::IMG_PROFIL, Document::DOC_TYPE_IMAGE, array( 'created' => -1 ));
    	//error_log("count docs ".count( $profilImages ) );
    	foreach ($profilImages as $docId => $document) {
    		Document::removeDocumentById($docId, $userId);
    		//error_log("delete document id ".$docId);
    	}
    	$resError["action"]["deleted_Documents"] = count($profilImages);
    	
    	//Remove Activity of the Element
    	$res = ActivityStream::removeElementActivityStream($elementId, $elementType);
    	$resError["action"]["removeElementActivityStream"] = $res;
    	if (!$res) return $resError;
    	//Delete News
    	$res = News::deleteNewsOfElement($elementId, $elementType, $userId, true);
    	$resError["action"]["deleteNewsOfElement"] = $res;
    	$resError["res"] = $res;
    	if (!$res["result"]) {
    		error_log("error deleting News ".@$res["id"]." : ".$res["msg"]); 
    		return $resError;
    	}
    	//Delete Action Rooms
    	$res = Room::deleteElementActionRooms($elementId, $elementType, $userId);
    	$resError["action"]["deleteElementActionRooms"] = $res; 
    	if (!$res["result"]) return $resError;


		$listEventsId = array();
		$listProjectId = array();
		//Remove backwards links
		$resError["action"]["RemoveBackwardsLinks"] = $res;
		if (isset($elementToDelete["links"])) {
			foreach ($elementToDelete["links"] as $linkType => $aLink) {
				foreach ($aLink as $linkElementId => $linkInfo) {
					$linkElementType = $linkInfo["type"];
					if (!isset($linksTypes[$linkElementType][$linkType])) {
						//error_log(print_r(@$linksTypes[$linkElementType]));
						error_log("Unknown backward links for a link in a ".$elementType." of type ".$linkType." to a ".$linkElementType);
						continue;
					}
					$linkToDelete = $linksTypes[$linkElementType][$linkType];
					
					$collection = $linkElementType;
					if ($collection == Event::COLLECTION) array_push($listEventsId, new MongoId($linkElementId));
					if ($collection == Project::COLLECTION) array_push($listProjectId, new MongoId($linkElementId));

					$where = array("_id" => new MongoId($linkElementId));
					$action = array('$unset' => array('links.'.$linkToDelete.'.'.$elementId => ""));
					PHDB::update($collection, $where, $action);
					error_log("Because of deletion of element :".$elementType."/".$elementId." : delete a backward link on a element ".$linkElementId." of type ".$collection." of type ".$linkToDelete);
				}
			}
		}

		if($elementType == Room::COLLECTION){
			$DDATypes = array(	Proposal::COLLECTION, 
								Resolution::COLLECTION, 
								Action::COLLECTION);

			foreach ($DDATypes as $k => $ddaType) {
				$where = array("idParentRoom" => $elementId);
    			$ddaOfThisRoom = PHDB::find($ddaType, $where);
    			foreach ($ddaOfThisRoom as $kk => $dda) {
    				$whereNews = array("object.type" => $ddaType, "object.id" => (string)$dda["_id"]);
    				//echo "FOUND : ".$ddaType."::COLLECTION > "; var_dump($dda);
    				//echo "REMOVE : News::COLLECTION > "; var_dump($whereNews);
    				PHDB::remove(News::COLLECTION, $whereNews);
    			}
    			//echo "REMOVE : ".$ddaType."::COLLECTION > "; var_dump($where);
    			PHDB::remove($ddaType, $where);
			}
			//exit;
			/*$where = array("idParentRoom" => $elementId);
    		PHDB::remove(Proposal::COLLECTION, $where);
    		PHDB::remove(Action::COLLECTION, $where);
    		PHDB::remove(Resolution::COLLECTION, $where);*/
		}
		
		//Unset the organizer for events organized by the element
		$resError["action"]["Unset_the_organizer_for_events_organized_by_the_element"] = $res;
		if (count($listEventsId) > 0) {
			$where = array('_id' => array('$in' => $listEventsId));
			$action = array('$set' => array("organizerId" => Event::NO_ORGANISER, "organizerType" => Event::NO_ORGANISER));
			PHDB::update(Event::COLLECTION, $where, $action);
		}

		//Unset the project with parent this element
		$resError["action"]["Unset_the_project_with_parent_this_element"] = $res;
		if (count($listProjectId) > 0) {
			$where = array('_id' => array('$in' => $listProjectId));
			$action = array('$unset' => array("parentId" => "", "parentType" => ""));
			PHDB::update(Project::COLLECTION, $where, $action);
		}
    	
		//Delete the element
		$where = array("_id" => new MongoId($elementId));
    	// NOTIFY COMMUNITY OF DELETED ELEMENT
    	Notification::constructNotification(ActStr::VERB_DELETE, array("id" => Yii::app()->session["userId"],"name"=> Yii::app()->session["user"]["name"]), array("type"=>$elementType,"id"=> $elementId), null, ActStr::VERB_DELETE);
		
		$resError["action"]["remove_element"] = $res;
    	PHDB::remove($elementType, $where);
    	
    	$res = array("result" => true, "status" => "deleted", "msg" => Yii::t('common',"The element {elementName} of type {elementType} has been deleted with success.", array("{elementName}" => @$elementToDelete["name"], "{elementType}" => @$elementType )));

		Log::save(array("userId" => $userId, "browser" => @$_SERVER["HTTP_USER_AGENT"], "ipAddress" => @$_SERVER["REMOTE_ADDR"], "created" => new MongoDate(time()), "action" => "deleteElement", "params" => array("id" => $elementId, "type" => $elementType)));
		$resError["action"]["final"] = $res;
		return $res;
	}

	//deletes elements with no strings attached
	//no particpants, no connected eleemnts ...etc
	//like POI, Ressources,Classifieds
	//deletes images by folders and 
	public static function deleteSimple($id,$type, $userId) {
		error_log("deleteSimple ".$id.",".$type);
		if ( !@$userId) 
            return array( "result" => false, "msg" => "You must be loggued to delete something" );
        
        
        $el = self::getByTypeAndId( $type, $id );
        if (! Authorisation::canDeleteElement($id, $type, $userId)) 
			return array("result" => false, "msg" => "The user cannot delete this element !");
		
		$res = array("result" => false, "msg" => "Something bad happend : impossible to delete this element");

        //Delete the comments
        $resComments = Comment::deleteAllContextComments($id,$type, $userId);
		if (@$resComments["result"]) {
			$resDocs = Document::removeDocumentByFolder($type."/".$id);
			PHDB::remove($type, array("_id"=>new MongoId($id)));
			$res = array("result" => true, 
						 "msg" => "The element has been deleted succesfully", 
						 "resDocs" => $resDocs);
		} else {
			return $resComments;
		}
		
		return $res;
	}

	/**
	 * The element is mark as pending deletion with a date.
	 * Send notification/mail to $admins (list of persons) to know if they accept the delete of the element
	 * After X days, if no one block the deletion => the element if deleted (this behavior is done with a batch)
	 * @param String $elementType : The element type
	 * @param String $elementId : the element Id
	 * @param String $reason : the reason why the element will be deleted
	 * @param array $admins : a list of person to sent notifications
	 * @param String $userId : the userId asking the deletion
	 * @return array result => bool, msg => String
	 */
	private static function goElementDeletePending($elementType, $elementId, $reason, $admins, $userId, $isSuperAdmin=false) {
		$res = array("result" => true, "status" => "deletePending", "msg" => Yii::t('common', "The element has been put in status 'delete pending', waiting the admin to confirm the delete."));
		
		//Mark the element as deletePending
		PHDB::update($elementType, 
					array("_id" => new MongoId($elementId)), array('$set' => array("status" => self::STATUS_DELETE_PEDING, "statusDate" => new MongoDate(), "reasonDelete" => $reason, "userAskingToDelete" => $userId)));
		
		//Send emails to admins
		Mail::confirmDeleteElement($elementType, $elementId, $reason, $admins, $userId);
		//TODO SBAR => @bouboule help wanted
		//Notification::actionOnPerson();
		if($isSuperAdmin){
			/*Notification::actionToAdmin(
	            ActStr::VERB_RETURN, 
	            array("type" => Cron::COLLECTION), 
	            array("id" => $this->id, "type"=>self::COLLECTION, "event" => $this->event),
	            array("id" => $this->personId, "type"=>Person::COLLECTION, "email"=>$this->recipient)
        	);*/
		}else
			Notification::constructNotification(ActStr::VERB_DELETE, array("id" => Yii::app()->session["userId"],"name"=> Yii::app()->session["user"]["name"]), array("type"=>$elementType,"id"=> $elementId), null, ActStr::VERB_ASK);
		
		return $res;
	}

	/**
	 * An admin of the element want to stop the process of delete of the element.
	 * Remove the pending status of the element
	 * @param String $elementType : The element type
	 * @param String $elementId : the element Id
	 * @param String $userId : the userId asking to stop
	 * @return array result => bool, msg => String
	 */
	public static function stopToDelete($elementType, $elementId, $userId) {
		$res = array("result" => true, "msg" => Yii::t('common',"The element is no more in 'delete pending' status"));
		//remove the status deletePending on the element
		PHDB::update($elementType, 
					array("_id" => new MongoId($elementId)), array('$unset' => array("status" => "", "statusDate" => "")));
		Notification::constructNotification(ActStr::VERB_DELETE, array("id" => Yii::app()->session["userId"],"name"=> Yii::app()->session["user"]["name"]), array("type"=>$elementType,"id"=> $elementId), null, ActStr::VERB_REFUSE);

		
		//TODO SBAR => 
		// - send email to notify the admin : the element has been stop by the user 
		// - add activity Stream
		// - Notification
		
		return $res;
	}
    
    public static function isElementStatusDeletePending($elementType, $elementId) {
        $element = Element::getElementById($elementId, $elementType);
        return @$element["status"] == Element::STATUS_DELETE_PEDING;
    }

	public static function save($params){
        $id = null;
        $data = null;

        if(empty($params["collection"]))
        	return array("result"=> false, "error"=>"400", "msg" => "Bad Request : Check you parameters");

        $collection = $params["collection"];
        
        if( !empty($params["id"]) ){
        	$id = $params["id"];
        }
        
        $key = self::getModelByType($collection);

		//$paramsImport = (empty($params["paramsImport"])?null:$params["paramsImport"]);
		$paramsLinkImport = ( empty($params["paramsImport"] ) ? null : $params["paramsImport"]);
		
		unset($params["paramsImport"]);
        unset($params['key']);
       
        $params = self::prepData( $params );
        unset($params['collection']);
        unset($params['id']);

        $postParams = array();
        if( !in_array( $collection, array("poi", "actions", "proposals", "resolutions", "classified", "ressources") ) && 
        	@$params["urls"] && @$params["medias"] ){
	        	$postParams["medias"] = $params["medias"];
	        	unset($params['medias']);
	        	$postParams["urls"] = $params["urls"];
	        	unset($params['urls']);
        }

        if($collection == Room::COLLECTION){
        	if(isset($params["roles"])){
        		$params["roles"] = explode(",", @$params["roles"]);
        	}
        }

        if($collection == City::COLLECTION)
        	$params = City::prepCity($params);
        
        if(isset($params["price"]))
        	$params["price"] = (int)$params["price"];

        
        /*$microformat = PHDB::findOne(PHType::TYPE_MICROFORMATS, array( "key"=> $key));
        $validate = ( !isset($microformat )  || !isset($microformat["jsonSchema"])) ? false : true;
        //validation process based on microformat defeinition of the form
        */
        //validation process based on databind on each Elemnt Mode
        $valid = array("result"=>true);
        if( $collection == Event::COLLECTION ){
            $valid = Event::validateFirst($params);
        } 
        //error_log("KEY : ". $key);

        if( $valid["result"] )
        	try {
        		//var_dump($key);exit;
        		$valid = DataValidator::validate( ucfirst($key), json_decode (json_encode ($params), true), ( empty($paramsLinkImport) ? null : true) );
        	} catch (CTKException $e) {
        		$valid = array("result"=>false, "msg" => $e->getMessage());
        	}
        
        if( $valid["result"]) {
			if( $collection == Event::COLLECTION ){
            	 $res = Event::formatBeforeSaving($params);
            	 if ($res["result"]) 
            	 	$params = $res["params"];
            	 else
            	 	throw new CTKException("Error processing before saving on event");
            }

            if($id){ //var_dump($params); exit;
            	$exists = PHDB::findOne($collection,array("_id"=>new MongoId($id)));
                if(!@$exists){
                	$params["creator"] = Yii::app()->session["userId"];
	        		$params["created"] = time();
	        		if(in_array($collection,[Organization::COLLECTION,Project::COLLECTION,Event::COLLECTION])){
	        			$slug=Slug::checkAndCreateSlug($params["name"],$collection,$id);
	        			Slug::save($collection,$id,$slug);
	        			$params["slug"]=$slug;
	        		}

                	PHDB::updateWithOptions($collection,array("_id"=>new MongoId($id)), array('$set' => $params ),array('upsert' => true ));
                	$params["_id"]=new MongoId($id);
                	if( $collection == Organization::COLLECTION )
                		$res["afterSave"] = Organization::afterSave($params, Yii::app()->session["userId"], $paramsLinkImport);
                	else if( $collection == Event::COLLECTION )
                		$res["afterSave"] = Event::afterSave($params, $paramsLinkImport);
                	else if( $collection == Project::COLLECTION )
                		$res["afterSave"] = Project::afterSave($params, $paramsLinkImport);
                	else if( $collection == Proposal::COLLECTION || $collection == Action::COLLECTION || $collection == Room::COLLECTION )
                		$res["afterSave"] = Cooperation::afterSave($params, $collection);
                	else if( $collection == Network::COLLECTION )
                		$res["afterSave"] = Network::afterSave($params, Yii::app()->session["userId"]);
                	
                	$res["afterSaveGbl"] = self::afterSave((string)$params["_id"],$collection,$params,$postParams);
                }
                else
                	PHDB::update($collection,array("_id"=>new MongoId($id)), array('$set' => $params ));
                
                $res = array("result"=>true,
                             "msg"=>Yii::t("common","Your data are well updated"),
                             "reload"=>true,
                             "map"=>$params,
                             "id"=>$id);
            } 
            else 
            { 
                $params["created"] = time();
                PHDB::insert($collection, $params );
                $res = array("result"=>true,
                             "msg"=>Yii::t("common","Your data are well registred"),
                             "reload"=>true,
                             "map"=>$params,
                             "id"=>(string)$params["_id"]);  
                //TODO
                //self::afterSave();
                
                // ***********************************
                //post process for specific actions
                // ***********************************
               // echo "ici";
                //echo $collection;

                if(in_array($collection,[Organization::COLLECTION,Project::COLLECTION,Event::COLLECTION])){
        			$slug=Slug::checkAndCreateSlug($params["name"],$collection, $res["id"]);
        			//var_dump($slug);
        			Slug::save($collection, $res["id"],$slug);
        			$params["slug"]=$slug;
        			self::updateField($collection, $res["id"], "slug", $slug);
        		}

                if( $collection == Organization::COLLECTION )
                	$res["afterSave"] = Organization::afterSave($params, Yii::app()->session["userId"], $paramsLinkImport);
                else if( $collection == Event::COLLECTION )
                	$res["afterSave"] = Event::afterSave($params, $paramsLinkImport);
                else if( $collection == Project::COLLECTION )
                	$res["afterSave"] = Project::afterSave($params, $paramsLinkImport );
                else if( $collection == Proposal::COLLECTION || $collection == Action::COLLECTION )
                	$res["afterSave"] = Cooperation::afterSave($params, $collection);
                else if( $collection == Network::COLLECTION )
                	$res["afterSave"] = Network::afterSave($params, Yii::app()->session["userId"]);

               // echo "pas d'id - "; var_dump($postParams); exit;
               $res["afterSaveGbl"] = self::afterSave((string)$params["_id"],$collection,$params,$postParams, $paramsLinkImport);
                //if( false && @$params["parentType"] && @$params["parentId"] )
                //{
                    //createdObjectAsParam($authorType, $authorId, $objectType, $objectId, $targetType, $targetId, $geo, $tags, $address, $verb="create")
                    //TODO
                    //Notification::createdObjectAsParam($authorType[Person::COLLECTION],$userId,$elementType, $elementType, $parentType[projet crée par une orga => orga est parent], $parentId, $params["geo"], (isset($params["tags"])) ? $params["tags"]:null ,$params["address"]);  
                //}
            }
          //  if(@$url = ( @$params["parentType"] && @$params["parentId"] && in_array($collection, array("poi") && Yii::app()->theme != "notragora")) ? "#".self::getControlerByCollection($params["parentType"]).".detail.id.".$params["parentId"] : null )
	        //    $res["url"] = $url;
	        if(in_array($collection, array("poi","classified"))){
		        if(Yii::app()->theme->name != "notragora" && @$params["parentType"] && @$params["parentId"])
		        	$url="#page.type.".$params["parentType"].".id.".$params["parentId"].".view.directory.dir.poi";
		        else
		        	$url="#poi.detail.id.".$res["id"];
	        } else{
		        $url=false;
	        }
             
			$res["url"]=$url;
        } else 
            $res = array( "result" => false, "error"=>"400",
                          "msg" => Yii::t("common","Something went really bad : ".$valid['msg']) );

        return $res;
    }

    public static function afterSave ($id, $collection, $params, $postParams, $paramsImport=null) {
    	$res = array();
    	
    	/*if( @$postParams["medias"] )
    	{
    		//create POI for medias connected to the parent
    		unset($params['_id']);
    		$poiParams["parentType"] = $collection;
    		$poiParams["parentId"] = $id;
    		$poiParams["name"] = "liens";
    		$poiParams["type"] = "link";
    		$poiParams["key"] = Poi::COLLECTION;
    		$poiParams["collection"] = Poi::COLLECTION;
    		$poiParams["medias"] = $postParams['medias'];
    		$poiParams["urls"] = $postParams['urls'];
    		//echo "afterSave - "; var_dump($poiParams); exit;
    		$res["medias"] = self::save($poiParams);
    	}*/
    	// Mail reference inivite on communecter
        if(in_array($collection,[Organization::COLLECTION,Project::COLLECTION,Event::COLLECTION])){
        	if(@$params["email"] && $params["email"]!=@Yii::app()->session["userEmail"]){
        		Mail::referenceEmailInElement($collection, $id, $params["email"]);
        	}
        }



        if (empty($paramsImport))
			Notification::createdObjectAsParam( Person::COLLECTION, 
												Yii::app()->session["userId"], 
												$collection, 
												$id, 
												Person::COLLECTION, 
												Yii::app()->session["userId"], 
												( !empty($params["geo"]) ? $params["geo"] : "" ) , 
												( !empty($params["tags"]) ? $params["tags"] : null ),
												( ( !empty($organization["address"]) && !empty($organization["address"]["codeInsee"]) ) ? $organization["address"]["codeInsee"] : "" ) ) ;
		ActivityStream::saveActivityHistory( ActStr::VERB_CREATE, $id, $collection, "organization", $params["name"] ) ;
                
    	return $res;
    }

    public static function prepData ($params) { 

    	if($params["collection"] == Network::COLLECTION){
    		$params = Network::prepData ($params); 
    	}else{
    		//empty fields aren't properly validated and must be removed
	        foreach ($params as $k => $v) {
	            if($v== "")
	                unset($params[$k]);
	        }
	        $coordinates = @$params["geoPosition"]["coordinates"] ;
	        if(@$coordinates && (is_string($coordinates[0]) || is_string($coordinates[1])))
				$params["geoPosition"]["coordinates"] = array(floatval($coordinates[0]), floatval($coordinates[1]));

			if (!empty($params["tags"]))
				$params["tags"] = Tags::filterAndSaveNewTags($params["tags"]);
	        
			$params["modified"] = new MongoDate(time());
			$params["updated"] = time();
			
			if( empty($params["id"]) ){
		        $params["creator"] = Yii::app()->session["userId"];
		        $params["created"] = time();
		    }
		    
		    if(@$params["public"] && in_array($params["collection"], [Event::COLLECTION, Project::COLLECTION])){
        		//$params["preferences"]["public"]=$params["public"];
        		if(!is_bool($params["public"]))
		    		$params["public"] = ($params["public"] == "true") ? true : false;
		    	if($params["public"]==false)
		    		$params["preferences"]["private"]=true;
        		if(@$params["preferences"]["private"]){
        			$params["preferences"]["isOpenData"]=false;
        			$params["preferences"]["isOpenEdition"]=false;
        		}
        		unset($params["public"]);
        	}

		    if (isset($params["allDay"])) {
		    	if ($params["allDay"] == "true") {
					$params["allDay"] = true;
				} else {
					$params["allDay"] = false;
				}
			}

			if (isset($params["public"]) && !is_bool($params["public"])) {
		    	if ($params["public"] == "true")
					$params["public"] = true;
				else 
					$params["public"] = false;
				
			}

			if(isset($params["name"]))
				$params["name"] = $params["name"];

			if(isset($params["slug"]) && !empty($params["slug"])){
				$params["slug"]=$params["slug"];
				if(!empty(Slug::getByTypeAndId($params["collection"],$params["id"])))
					Slug::update($params["collection"],$params["id"],$params["slug"]);
				else
					Slug::save($params["collection"],$params["id"],$params["slug"]);
			}
			if(isset($params["shortDescription"]))
				$params["shortDescription"] = strip_tags($params["shortDescription"]);


			if(!empty($params["parentType"])){
				$parentType = self::getCollectionByControler($params["parentType"]);
				if(!empty($parentType))
					$params["parentType"] = $parentType;
			}

			if(empty($params["idParentRoom"]) && 
				!empty($params["parentIdSurvey"]) && 
				$params["collection"] == Action::COLLECTION){
				$room = PHDB::findOne(ActionRoom::COLLECTION, array("parentIdSurvey" => $params["parentIdSurvey"]));

				if(empty($room)){

					$form = Form::getByIdMongo($params["parentIdSurvey"], array("title"));

					$paramsRoom = array(
						"parentId" => $params["parentId"],
						"parentType" => $params["parentType"],
						"parentIdSurvey" => $params["parentIdSurvey"],
						"status" => "open",
						"description" => "",
						"name" => $form["title"],
						"key" => ActionRoom::CONTROLLER,
						"collection" => ActionRoom::COLLECTION,
					);
					//
					//
					$room = self::save($paramsRoom);
					//Rest::json($room); exit ;
					$params["idParentRoom"] = $room["id"] ;
				}else{
					//var_dump($room); exit;
					$params["idParentRoom"] = (String) $room["_id"] ;
				}
				//Rest::json($params); exit ;			
			}


			if($params["collection"] == Action::COLLECTION && !empty($params["role"])){
				$params["role"] = array( InflectorHelper::slugify( $params["role"] ) => $params["role"] ) ;
			}
    	}

        return $params;
    }

	public static function alreadyExists ($params, $collection) {
		$result = array("result" => false);
		$where = array(	"name" => $params["name"],
						"address.localityId" => $params["address"]["localityId"]);
		$element = PHDB::findOne($collection, $where);
		if(!empty($element))
			$result = array("result" => true ,
							"element" => $element);
		return $result;
    }


    /**
	 * Retrieve a element by id from DB
	 * @param String $id of the event
	 * @return array with data id, name, type profilImageUrl
	 */
	public static function getElementById($id, $collection, $where=null, $fields=null){
		$where["_id"] = new MongoId($id) ;
		$element = PHDB::findOne($collection, $where ,$fields);
		return @$element;
	}


	public static function getElementSimpleById($id, $collection,$where=null, $fields=null){
		if(empty($fields))
			$fields = array("_id", "name");
		$element = self::getElementById($id, $collection, $where ,$fields) ;
		return @$element;
	}

	public static function followPerson($params, $gmail=null){
		
		$invitedUserId = "";

        if (empty(Yii::app()->session["userId"])) {
        	Rest::json(array("result" => false, "msg" => Yii::t("common","The current user is not valid : please login.")));
        	die();
        }
        
        //Case spécial : Vérifie si l'email existe et retourne l'id de l'utilisateur
        if (!empty($params["invitedUserEmail"]))
        	$invitedUserId = Person::getPersonIdByEmail($params["invitedUserEmail"]);

        if(Yii::app()->session["userId"]==$invitedUserId){
        	Rest::json(array("result" => false, "msg" => Yii::t("common","You try to invite yourself")));
        	die();
        }
        //Case 1 : the person invited exists in the db
        if (!empty($params["connectUserId"]) || !empty($invitedUserId)) {
        	if (!empty($params["connectUserId"]))
        		$invitedUserId = $params["connectUserId"];
        	if(Link::isLinked(Yii::app()->session["userId"], Person::COLLECTION, (string)$invitedUserId))
        		$res["msg"]=Yii::t("common","This user is already connected and you already follow him");
        	else{
	        	$child["childId"] = Yii::app()->session["userId"] ;
    	    	$child["childType"] = Person::COLLECTION;

	        	$res = Link::follow((string)$invitedUserId, Person::COLLECTION, $child);
	            $actionType = ActStr::VERB_FOLLOW;
	            $msg=Yii::t("common","This user is already connected, but now you follow him");
	        }
		//Case 2 : the person invited does not exist in the db
		} else if (empty($params["invitedUserId"])) {
			$newPerson = array("name" => $params["invitedUserName"], "email" => $params["invitedUserEmail"], "invitedBy" => Yii::app()->session["userId"]);
			
			//if(!empty($params["msgEmail"]))
			$res = Person::createAndInvite($newPerson, @$params["msgEmail"], $gmail);
			//else
				//$res = Person::createAndInvite($newPerson);

            $actionType = ActStr::VERB_INVITE;
            if ($res["result"]) {
            	$invitedUserId = $res["id"];
                $child["childId"] = Yii::app()->session["userId"];
    			$child["childType"] = Person::COLLECTION;
                $res = Link::follow($invitedUserId, Person::COLLECTION, $child);
            }
            $msg=Yii::t("common","The invitation is sent with success");
		}
		
        if (@$res["result"] == true) {
            $person = Person::getSimpleUserById($invitedUserId);
            $res = array("result" => true, "msg"=>$msg,"invitedUser" => $person);
        } else {
            $res = array("result" => false, "msg" => $res["msg"]);
        }

		return $res;
	}

	public static function followPersonByListMails($listMails, $msgEmail=null, $gmail=null){
		$result = array("result" => false);
		$params["msgEmail"] = (empty($msgEmail)?null:$msgEmail) ;
		foreach ($listMails as $key => $value) {
			$result["result"] = true ;
			//if(!empty($value["mail"])){
				$params["invitedUserEmail"] = $key ;

				if(empty($value)){
					$split = explode("@", $key);
					$params["invitedUserName"] = $split[0];
				}else
					$params["invitedUserName"] = $value ;
				$result["data"][] = self::followPerson($params, $gmail);
			//}
		}
		return $result;
	}
	
    public static function saveChart($type, $id, $properties, $label){
	    //TODO SABR - Check the properties before inserting
	    PHDB::update($type,
			array("_id" => new MongoId($id)),
            array('$set' => array("properties.chart.".$label=> $properties))
        );
        return true;
    }
    
	public static function removeChart($type, $id, $label){
		PHDB::update($type, 
            array("_id" => new MongoId($id)) , 
            array('$unset' => array("properties.chart.".$label => 1))
        );
        return true;	
	}

	public static function afterSaveImport($eltId, $eltType, $paramsImport){
		if (@$paramsImport) {
			if(!empty($paramsImport["link"])){
				$idLink = $paramsImport["link"]["idLink"];
				$typeLink = $paramsImport["link"]["typeLink"];
				if (@$paramsImport["link"]["role"] == "admin"){
					$isAdmin = true;
				}else{
					$isAdmin = false;
				}


				/*const person2person = "follows";
			    const person2organization = "memberOf";
			    const organization2person = "members";
			    const person2events = "events";
			    const person2projects = "projects";
			    const event2person = "attendees";
			    const project2person = "contributors";
			    const need2Item = "needs";*/
				if($eltType == Organization::COLLECTION){
					if($typeLink == Organization::COLLECTION){
						$connectType1 = "members";
						$connectType2 = "memberOf";
						//Link::connect($idLink, $typeLink, $eltId, $eltType, $creatorId,"members", false);
						//Link::connect($eltId, $eltType, $idLink, $typeLink, $creatorId,"memberOf",false);
					}
					else if($typeLink == Person::COLLECTION){
						$connectType1 = "members";
						$connectType2 = "memberOf";
					}
				}else if($eltType == Person::COLLECTION){
					if($typeLink == Organization::COLLECTION){
						$connectType1 = "memberOf";
						$connectType2 = "members";
					}else if($typeLink == Person::COLLECTION){
						$connectType1 = "followers";
						$connectType2 = "follows";
					}
				}else if($eltType == Project::COLLECTION){
					if($typeLink == Organization::COLLECTION){
						$connectType1 = "contributors";
						$connectType2 = "projects";
					}else if($typeLink == Person::COLLECTION){
						$connectType1 = "contributors";
						$connectType2 = "projects";
					}
				}else if($eltType == Event::COLLECTION){
					if($typeLink == Organization::COLLECTION){
						//$connectType1 = "memberOf";
						//$connectType2 = "members";
					}else if($typeLink == Person::COLLECTION){
						$connectType1 = "attendees";
						$connectType2 = "events";
					}else if($typeLink == Event::COLLECTION){
						$connectType1 = "attendees";
						$connectType2 = "events";
					}
				}

				if(!empty($connectType1) && !empty($connectType2)){
					Link::connect($eltId, $eltType, $idLink, $typeLink, $creatorId, $connectType1,$isAdmin);
					Link::connect($idLink, $typeLink, $eltId, $eltType, $creatorId, $connectType2,$isAdmin);
				}
				
				
			}

			if(!empty($paramsImport["img"])){
		    	try{
		    		$paramsImg = $paramsImport["img"] ;
					$resUpload = Document::uploadDocumentFromURL(	$paramsImg["module"], $eltType, 
																	$eltId, "avatar", false, 
																	$paramsImg["url"], $paramsImg["name"]);
					if(!empty($resUpload["result"]) && $resUpload["result"] == true){
						$params = array();
						$params['id'] = $eltId;
						$params['type'] = $eltType;
						$params['moduleId'] = $paramsImg["module"];
						$params['folder'] = $eltType."/".$eltId;
						$params['name'] = $resUpload['name'];
						$params['author'] = Yii::app()->session["userId"] ;
						$params['size'] = $resUpload["size"];
						$params["contentKey"] = "profil";
						$resImgSave = Document::save($params);
						if($resImgSave["result"] == false)
							throw new CTKException("Impossible de sauvegarder l'image.");
					}else{
						throw new CTKException("Impossible uploader l'image.");
					}
				}catch (CTKException $e){
					throw new CTKException($e);
				}	
			}
		}
	}


	public static function saveContact($params){
		$id = $params["parentId"];
		$collection = $params["parentType"];
		if(!empty($params["phone"]))
			$params["telephone"] = explode(",", $params["phone"]);
		//if(!empty($params["idContact"]))
		//	$params["id"] = $params["idContact"];
		unset($params["parentId"]);
		unset($params["parentType"]);
		unset($params["phone"]);
		//unset($params["idContact"]);

		if(empty($params["name"]) && empty($params["email"]) && empty($params["role"]) && empty($params["telephone"]))
			$res = array("result" => false, "msg" => "Vous devez avoir au moins une information sur le contact");
		else
			$res = self::updateField($collection, $id, "contacts", $params);

		if($res["result"])
			$res["msg"] = Yii::t("common","Contacts are well updated");
		return $res;
	}

	public static function saveUrl($params){
		$id = $params["parentId"];
		$collection = $params["parentType"];
		$params["url"]=self::getAndCheckUrl($params["url"]);

		unset($params["parentId"]);
		unset($params["parentType"]);
		unset($params["key"]);
		unset($params["collection"]);
		$res = self::updateField($collection, $id, "urls", $params);
		if($res["result"])
			$res["msg"] = Yii::t("common","URLs are well updated");
		return $res;
	}

	public static function getAndCheckUrl($url){
		$needles = array("http://", "https://");
		$find=false;
	    foreach($needles as $needle) {
	    	if(stripos($url, $needle) == 0)
	    		$find = true;
	    }
	    if(!$find)
	    	$url="http://".$url;
	    return $url ;
	}

	public static function getUrls($id, $type){
		$res = array();
		$listElt = array(Organization::COLLECTION, Person::COLLECTION, Project::COLLECTION, Event::COLLECTION);
		if(in_array($type, $listElt) ){
			$res = PHDB::findOne( $type , array( "_id" => new MongoId($id) ) ,array("urls") );
			$res = (!empty($res["urls"]) ? $res["urls"] : array() );
		}
		return $res;
	}

	public static function getCuriculum($id, $type){
		$res = array();
		$listElt = array(Organization::COLLECTION, Person::COLLECTION, Project::COLLECTION, Event::COLLECTION);
		if(in_array($type, $listElt) ){
			$res = PHDB::findOne( $type , array( "_id" => new MongoId($id) ) ,array("curiculum") );
			$res = (!empty($res["curiculum"]) ? $res["curiculum"] : array() );
		}
		return $res;
	}

	public static function getContacts($id, $type){
		$res = array();
		$listElt = array(Organization::COLLECTION, Person::COLLECTION, Project::COLLECTION, Event::COLLECTION);
		if(in_array($type, $listElt) ){
			$res = PHDB::findOne( $type , array( "_id" => new MongoId($id) ) ,array("contacts") );
			$res = (!empty($res["contacts"]) ? $res["contacts"] : array() );

			foreach ($res as $key => $value) {
				$res[$key]["typeSig"] = "person";
			}
		}
		return $res;
	}

	public static function getContactsByMails($listMails){
		$res = array();
		foreach ($listMails as $key => $mail){
			$valid = DataValidator::email($mail) ;
			if( $valid  == ""){
				$person = PHDB::findOne( Person::COLLECTION , array( "email" => $mail ), array("_id", "name", "profilThumbImageUrl") );
				if(!empty($person["_id"])){
					$person["id"] = (String) $person["_id"];
					$res[$mail] = $person;
				}else
					$res[$mail] = false ;
			}
			else
				$res[$mail] = false ;
		}
		return $res;
	}

	public static function updateBlock($params){
		$block = $params["block"];
		$collection = $params["typeElement"];
		$id = $params["id"];
		$res = array();
		try {

			if($block == "info"){
				if(isset($params["name"])){
					$res[] = self::updateField($collection, $id, "name", $params["name"]);
					/*PHDB::update( $collection,  array("_id" => new MongoId($id)), 
			 										array('$unset' => array("hasRC"=>"") ));*/
				}
				if(isset($params["username"]) && $collection == Person::COLLECTION)
					$res[] = self::updateField($collection, $id, "username", $params["username"]);
				if(isset($params["avancement"]) && $collection == Project::COLLECTION)
					$res[] = self::updateField($collection, $id, "avancement", $params["avancement"]);
				if(isset($params["tags"]))
					$res[] = self::updateField($collection, $id, "tags", $params["tags"]);
				if(isset($params["type"])  && ( $collection == Event::COLLECTION || $collection == Organization::COLLECTION) )
					$res[] = self::updateField($collection, $id, "type", $params["type"]);
				if(isset($params["email"])){
					$resEmail=self::updateField($collection, $id, "email", $params["email"]);
					$res[] = $resEmail;
					// Mail reference inivite on communecter
			        if($resEmail["result"] && in_array($collection,[Organization::COLLECTION,Project::COLLECTION,Event::COLLECTION])){
			        	if(@$params["email"] && $params["email"]!=@Yii::app()->session["userEmail"]){
			        		Mail::referenceEmailInElement($collection, $id, $params["email"]);
			        	}
			        }
				}

				if(isset($params["slug"])){
					$el = PHDB::findOne($collection,array("_id"=>new MongoId($id)));
					$oldslug = @$el["slug"];
					if(!empty(Slug::getByTypeAndId($collection,$id)))
						Slug::update($collection,$id,$params["slug"]);
					else
						Slug::save($collection,$id,$params["slug"]);
					$res[] = self::updateField($collection, $id, "slug", $params["slug"]);
				}
				//update RC channel name if exist
				if(@$el["hasRC"]){
					RocketChat::rename( $oldslug, $params["slug"], @$el["preferences"]["isOpenEdition"] );
				}
				if(isset($params["url"]))
					$res[] = self::updateField($collection, $id, "url", self::getAndCheckUrl($params["url"]));
				if(isset($params["birthDate"]) && $collection == Person::COLLECTION)
					$res[] = self::updateField($collection, $id, "birthDate", $params["birthDate"]);
				if(isset($params["fixe"]))
					$res[] = self::updateField($collection, $id, "fixe", $params["fixe"]);
				if(isset($params["fax"]))
					$res[] = self::updateField($collection, $id, "fax", $params["fax"]);
				if(isset($params["mobile"]))
					$res[] = self::updateField($collection, $id, "mobile", $params["mobile"]);
				
				if( !empty($params["parentId"]) ){
					$parent["parentId"] = $params["parentId"] ;
					$parent["parentType"] = ( !empty($params["parentType"]) ? $params["parentType"] : "dontKnow" ) ;
					$resParent = self::updateField($collection, $id, "parent", $parent);
					if($parent["parentType"] != "dontKnow" && $parent["parentId"] != "dontKnow")
						$resParent["value"]["parent"] = Element::getByTypeAndId( $params["parentType"], $params["parentId"]);
					$res[] = $resParent;
				}
				
				if(!empty($params["organizerId"]) ){
					$organizer["organizerId"] = $params["organizerId"] ;
					$organizer["organizerType"] = ( !empty($params["organizerType"]) ? $params["organizerType"] : "dontKnow" ) ;
					$resOrg = self::updateField($collection, $id, "organizer", $organizer);

					if($params["organizerType"]!="dontKnow" && $params["organizerId"] != "dontKnow"){
						$resOrg["value"]["organizer"] = Element::getByTypeAndId( $params["organizerType"], $params["organizerId"]);
					}
					$res[] = $resOrg;
				}

			}else if($block == "network"){
				if(isset($params["telegram"]) && $collection == Person::COLLECTION)
					$res[] = self::updateField($collection, $id, "telegram", $params["telegram"]);
				if(isset($params["facebook"]))
					$res[] = self::updateField($collection, $id, "facebook", self::getAndCheckUrl($params["facebook"]));
				if(isset($params["twitter"]))
					$res[] = self::updateField($collection, $id, "twitter", self::getAndCheckUrl($params["twitter"]));
				if(isset($params["github"]))
					$res[] = self::updateField($collection, $id, "github", self::getAndCheckUrl($params["github"]));
				if(isset($params["gpplus"]))
					$res[] = self::updateField($collection, $id, "gpplus", self::getAndCheckUrl($params["gpplus"]));
				if(isset($params["skype"]))
					$res[] = self::updateField($collection, $id, "skype", self::getAndCheckUrl($params["skype"]));
				if(isset($params["diaspora"]))
					$res[] = self::updateField($collection, $id, "diaspora", self::getAndCheckUrl($params["diaspora"]));
				if(isset($params["mastodon"]))
					$res[] = self::updateField($collection, $id, "mastodon", self::getAndCheckUrl($params["mastodon"]));
				if(isset($params["instagram"]))
					$res[] = self::updateField($collection, $id, "instagram", self::getAndCheckUrl($params["instagram"]));

			}else if( $block == "when" && ( $collection == Event::COLLECTION || $collection == Project::COLLECTION) ) {
				
				if(isset($params["allDayHidden"]) && $collection == Event::COLLECTION)
					$res[] = self::updateField($collection, $id, "allDay", (($params["allDayHidden"] == "true") ? true : false));
				if(isset($params["startDate"]))
					$res[] = self::updateField($collection, $id, "startDate", $params["startDate"],@$params["allDay"]);
				if(isset($params["endDate"]))
					$res[] = self::updateField($collection, $id, "endDate", $params["endDate"],@$params["allDay"]);
			
			}else if($block == "toMarkdown"){

				$res[] = self::updateField($collection, $id, "description", $params["value"]);
				$res[] = self::updateField($collection, $id, "descriptionHTML", null);

			}else if($block == "descriptions"){

				if(isset($params["tags"]))
					$res[] = self::updateField($collection, $id, "tags", $params["tags"]);

				if(isset($params["description"])){
					$res[] = self::updateField($collection, $id, "description", $params["description"]);
					self::updateField($collection, $id, "descriptionHTML", null);
				}
				
				if(isset($params["shortDescription"]))
					$res[] = self::updateField($collection, $id, "shortDescription", strip_tags($params["shortDescription"]));
			
			}else if($block == "activeCoop"){

				if(isset($params["status"]))
					$res[] = self::updateField($collection, $id, "status", $params["status"]);
				if(isset($params["voteActivated"]))
					$res[] = self::updateField($collection, $id, "voteActivated", $params["voteActivated"]);
				if(isset($params["amendementActivated"]))
					$res[] = self::updateField($collection, $id, "amendementActivated", $params["amendementActivated"]);
			
			}else if($block == "amendement"){

				if(isset($params["txtAmdt"]) && isset($params["typeAmdt"]) && isset($params["id"]) && @Yii::app()->session['userId']){
					$proposal = Proposal::getById($params["id"]);
					$amdtList = @$proposal["amendements"] ? $proposal["amendements"] : array();
					$rand = rand(1000, 100000);
					while(isset($amdtList[$rand])){ $rand = rand(1000, 100000); }

					$amdtList[$rand] = array(
										"idUserAuthor"=> Yii::app()->session['userId'],
										"typeAmdt" => $params["typeAmdt"],
										"textAdd"=> $params["txtAmdt"]);
					Notification::constructNotification ( ActStr::VERB_AMEND, array("id" => Yii::app()->session["userId"],"name"=> Yii::app()->session["user"]["name"]), array("type"=>$proposal["parentType"],"id"=>$proposal["parentId"]),array( "type"=>Proposal::COLLECTION,"id"=> $params["id"] ) );
					$res[] = self::updateField($collection, $id, "amendements", $amdtList);
				}
			
			}else if($block == "curiculum.skills"){
				$parent = Element::getByTypeAndId($params["typeElement"], $params["id"]);
				$cv = @$parent["curiculum"] ? $parent["curiculum"] : array();

				$CVAttrs = array("competences", "mainQualification", "hasVehicle", "languages",
								"motivation", "driverLicense", "url");
				foreach ($CVAttrs as $att) {
					if(@$params[$att]) 
					$cv["skills"][$att] = @$params[$att];
				}
				$res[] = self::updateField($collection, $id, "curiculum", $cv);
				//var_dump($params);
			}else if($block == "curiculum.lifepath"){
				$parent = Element::getByTypeAndId($params["typeElement"], $params["id"]);
				$cv = @$parent["curiculum"] ? $parent["curiculum"] : array();
				$indexLP = @$cv["lifepath"] ? sizeof($cv["lifepath"]) : 0;
				
				$CVAttrs = array("title", "description", "startDate", "endDate",
								"location");
				foreach ($CVAttrs as $att) {
					if(@$params[$att]) 
					$cv["lifepath"][$indexLP][$att] = @$params[$att];
				}
				$res[] = self::updateField($collection, $id, "curiculum", $cv);
				//var_dump($params);
			}

			if(Import::isUncomplete($id, $collection)){
				Import::checkWarning($id, $collection, Yii::app()->session['userId'] );
			}


			$result = array("result"=>true);
			$resultGoods = array();
			$resultErrors = array();
			$values = array();
			$msg = "";
			$msgError = "";
			foreach ($res as $key => $value) {
				if($value["result"] == true){
					if($msg != "")
						$msg .= ", ";
					$msg .= Yii::t("common",$value["fieldName"]);
					$values[$value["fieldName"]] = $value["value"];
				}else{
					if($msgError != "")
						$msgError .= ". ";
					$msgError .= $value["mgs"];
				}
			}

			if($msg != ""){
				$resultGoods["result"]=true;
				$resultGoods["msg"]= Yii::t("common", "The following attributs has been updated :")." ".Yii::t("common",$msg);
				$resultGoods["values"] = $values ;
				$result["resultGoods"] = $resultGoods ;
				$result["result"] = true ;
			}

			if($msgError != ""){
				$resultErrors["result"]=false;
				$resultErrors["msg"]=Yii::t("common", $msgError);
				$result["resultErrors"] = $resultErrors ;
			}
		} catch (CTKException $e) {
			$resultErrors["result"]=false;
			$resultErrors["msg"]=$e->getMessage();
			$result["resultErrors"] = $resultErrors ;
		}
		return $result;
	}



	public static function getInfoDetail($params, $element, $type, $id){
		$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
        $params["openEdition"] = Authorisation::isOpenEdition($id, $type, @$element["preferences"]);
        $params["controller"] = self::getControlerByCollection($type);
        if($type==Person::COLLECTION && !@$element["links"]){
        	$fields=array("links");
        	$links=Element::getElementSimpleById($id,$type,null,$fields);
        	$links=@$links["links"];
        }
        else
        	$links=@$element["links"];
        $connectType = @self::$connectTypes[$type];
        if($type==Person::COLLECTION)
        	$connectType="friends";
       	if(((!@$links[$connectType][Yii::app()->session["userId"]] && $type!=Event::COLLECTION) || (@$links[$connectType][Yii::app()->session["userId"]] && 
                @$links[$connectType][Yii::app()->session["userId"]][Link::TO_BE_VALIDATED])) && 
                @Yii::app()->session["userId"] && 
                ($type != Person::COLLECTION || 
                (string)$element["_id"] != Yii::app()->session["userId"])){
                    $params["linksBtn"]["followBtn"]=true;	
                    if (@$links["followers"][Yii::app()->session["userId"]])
                        $params["linksBtn"]["isFollowing"]=true;
                    else if(!@$links["followers"][Yii::app()->session["userId"]] && 
                            $type != Event::COLLECTION)   
                        $params["linksBtn"]["isFollowing"]=false;       
            }
            
            $connectAs = @self::$connectAs[$type];
            
            $params["linksBtn"]["connectAs"]=$connectAs;
            $params["linksBtn"]["connectType"]=$connectType;
            if( @Yii::app()->session["userId"] && $type!= Person::COLLECTION && !@$links[$connectType][Yii::app()->session["userId"]]){
                $params["linksBtn"]["communityBn"]=true;                    
                $params["linksBtn"]["isMember"]=false;
            }else if($type != Person::COLLECTION  && @Yii::app()->session["userId"]){
                //Ask Admin button
                $connectAs="admin";
                $params["linksBtn"]["communityBn"]=true;
                $params["linksBtn"]["isMember"]=true;
                if(@$links[$connectType][Yii::app()->session["userId"]][Link::TO_BE_VALIDATED])
                    $params["linksBtn"][Link::TO_BE_VALIDATED]=true;
                if(@$links[$connectType][Yii::app()->session["userId"]][Link::IS_INVITING]){
                    $params["linksBtn"][Link::IS_INVITING]=true;
                    $params["invitedMe"]=array(
                    	"invitorId"=>$links[$connectType][Yii::app()->session["userId"]]["invitorId"],
                    	"invitorName"=>$links[$connectType][Yii::app()->session["userId"]]["invitorName"]);
                }
				if(@$links[$connectType][Yii::app()->session["userId"]][Link::IS_ADMIN_INVITING]){
                    $params["linksBtn"][Link::IS_ADMIN_INVITING]=true;
                    $params["invitedMe"]=array(
                    	"invitorId"=>$links[$connectType][Yii::app()->session["userId"]]["invitorId"],
                    	"invitorName"=>$links[$connectType][Yii::app()->session["userId"]]["invitorName"],
                    	"isAdminInviting"=>true);
                }

                $params["linksBtn"]["isAdmin"]=true;
                if(@$links[$connectType][Yii::app()->session["userId"]][Link::IS_ADMIN_PENDING])
                    $params["linksBtn"][Link::IS_ADMIN_PENDING]=true;
                //Test if user has already asked to become an admin
                if(!in_array(Yii::app()->session["userId"], Authorisation::listAdmins($id, $type,true)))
                    $params["linksBtn"]["isAdmin"]=false;              
            }

            $params["isLinked"] = Link::isLinked($id,$type, 
                                    Yii::app()->session['userId'], 
                                    @$element["links"]);

            if($params["isLinked"]==true)
                $params["countNotifElement"]=ActivityStream::countUnseenNotifications(Yii::app()->session["userId"], $type, $id);
            if($type==Event::COLLECTION){
                $params["countStrongLinks"]= @$attendeeNumber;
                //$params["countLowLinks"] = @$invitedNumber;
            }
            else{
                $params["countStrongLinks"]= @$countStrongLinks;
                $params["countLowLinks"] = count(@$element["links"]["followers"]);
            }
            $params["countInvitations"]=@$invitedNumber;
            $params["countries"] = OpenData::getCountriesList();

            if(@$_POST["modeEdit"]){
                $params["modeEdit"]=$_POST["modeEdit"];
            }
            
            if(@$_GET["network"])
                $params["networkJson"]=Network::getNetworkJson($_GET["network"]);
        //}

        return $params;
	}


	public static function getElementForJS($element, $type = null) {
		$newElement = $element ;
		unset($newElement["badges"]);
		unset($newElement["modified"]);
		unset($newElement["modifiedByBatch"]);
		unset($newElement["preferences"]);
		unset($newElement["roles"]);
		unset($newElement["two_step_register"]);
		unset($newElement["lastLoginDate"]);

		if(in_array(@$element["type"],array_keys( Organization::$types) ) )
			$newElement["typeOrga"] = $element["type"] ;
		if(in_array(@$element["type"],array_keys( Event::$types) ) )
			$newElement["typeEvent"] = $element["type"] ;

	
		$newElement["fixe"] = (!empty($element["telephone"]["fixe"])) ? ArrayHelper::arrayToString($element["telephone"]["fixe"]) : "" ;
		$newElement["mobile"] = (!empty($element["telephone"]["mobile"])) ? ArrayHelper::arrayToString($element["telephone"]["mobile"]) : "" ;
		$newElement["fax"] = (!empty($element["telephone"]["fax"])) ? ArrayHelper::arrayToString($element["telephone"]["fax"]) : "" ;
		$newElement["id"] = @(String) $element["_id"] ;

		if(!empty($type))
			$newElement["type"] = $type;


		if(!empty($element["properties"]["avancement"]))
			$newElement["avancement"] = $element["properties"]["avancement"];


		if($type == Person::COLLECTION){
			if(empty($newElement["socialNetwork"]))
				$newElement["socialNetwork"] = array();

			$sNetwork = array("telegram", "github", "skype", "twitter", "facebook", "gpplus", "instagram", "diaspora", "mastodon");
			foreach ($sNetwork as $key => $value) {
				if(empty($newElement["socialNetwork"][$value]))
					$newElement["socialNetwork"][$value] = "";
			}
		}
		

		return $newElement;
	}

	public static function getParamsOnepage($type, $id){
		$members=array();
		//$list = Lists::get(array("eventTypes"));
		$events=array();
		$projects=array();
		$needs=array();
		$elementAuthorizationId=$id;
		$elementAuthorizationType=$type;
		if($type != Person::COLLECTION){
			$listsToRetrieveOrga = array("public", "typeIntervention", "organisationTypes", "NGOCategories", "localBusinessCategories");
			$listsOrga = Lists::get($listsToRetrieveOrga);

			$listsToRetrieveEvent = array("eventTypes");
			$listsEvent = Lists::get($listsToRetrieveEvent);
		}
		


		if($type == Organization::COLLECTION){
			$element = Organization::getById($id);
			if (empty($element)) throw new CHttpException(404,Yii::t("organization","The organization you are looking for has been moved or deleted !"));
			$params["listTypes"] = isset($listsOrga["organisationTypes"]) ? $listsOrga["organisationTypes"] : null;
			$params["public"] 			 = isset($listsOrga["public"]) 			  ? $listsOrga["public"] : null;
			$params["typeIntervention"]  = isset($listsOrga["typeIntervention"])  ? $listsOrga["typeIntervention"] : null;
			$params["NGOCategories"] 	 = isset($listsOrga["NGOCategories"]) 	  ? $listsOrga["NGOCategories"] : null;
			$params["localBusinessCategories"] = isset($listsOrga["localBusinessCategories"]) ? $listsOrga["localBusinessCategories"] : null;
			$connectType = "members";
			
			
		} else if ($type == Project::COLLECTION){
			$element = Project::getById($id);
			if (empty($element)) throw new CHttpException(404,Yii::t("projet","The project you are looking for has been moved or deleted !"));
			$params["eventTypes"] = $listsEvent["eventTypes"];
			$params["listTypes"] = @$listsEvent["eventTypes"];
			$connectType = "contributors";
			// Link with events
			
		} else if ($type == Event::COLLECTION){
			$element = Event::getById($id);
			if (empty($element)) throw new CHttpException(404,Yii::t("event","The event you are looking for has been moved or deleted !"));
			$params["listTypes"] = $listsEvent["eventTypes"];
			$connectType = "attendees";
			$invitedNumber=0;
			$attendeeNumber=0;
			if(@$element["links"][$connectType]){
				foreach ($element["links"][$connectType] as $uid => $e) {
					if(@$e["invitorId"]){
		  				if(@Yii::app()->session["userId"] && $uid==Yii::app()->session["userId"])
		  					$params["invitedMe"]=array("invitorId"=>$e["invitorId"],"invitorName"=>$e["invitorName"]);
		  				$invitedNumber++;
			  		} else
	  					$attendeeNumber++;

				}
			}
			//EventOrganizer
			if(@$element["links"]["organizer"]){
				foreach ($element["links"]["organizer"] as $uid => $e) {
            		$organizer["type"] = $e["type"];
            		if($organizer["type"] == Project::COLLECTION ){
                		$iconNav="fa-lightbulb-o";
                		$urlType="project";
                		$organizerInfo = Project::getSimpleProjectById($uid);
                		$organizer["type"]=$urlType;
            		}
            		else if($organizer["type"] == Organization::COLLECTION ){
		                $iconNav="fa-group";
		                $urlType="organization";	
		                $organizerInfo = Organization::getSimpleOrganizationById($uid);  
						$organizer["type"]=$urlType;
						$organizer["typeOrga"]=@$organizerInfo["type"];              
            		}
					else{
						$iconNav="fa-user";
		                $urlType="person";	
		                $organizerInfo = Person::getSimpleUserById($uid);  
						$organizer["type"]=$urlType;
					}
            		$organizer["id"] = $uid;
            		$organizer["name"] = @$organizerInfo["name"];
            		$organizer["profilImageUrl"] = @$organizerInfo["profilImageUrl"];
            		$organizer["profilThumbImageUrl"] = @$organizerInfo["profilThumbImageUrl"];
          		}
		  		$params["organizer"] = $organizer;
              		
            }
			//events can have sub events
	        $params["subEvents"] = PHDB::find(Event::COLLECTION,array("parentId"=>$id));
	        $params["subEventsOrganiser"] = array();
	        $hasSubEvents = false;
	        if(@$params["subEvents"]){
	        	$hasSubEvents = true;
	        	foreach ($params["subEvents"] as $key => $value) {
	        		if( @$value["links"]["organizer"] ){
		        		foreach ($value["links"]["organizer"] as $key => $value) {
		        			if( !@$params["subEventsOrganiser"][$key])
		        				$params["subEventsOrganiser"][$key] = Element::getInfos( $value["type"], $key);
		        		}
	        		}
	        	}
	        }

		} else if ($type == Person::COLLECTION){
			$element = Person::getById($id);
			if (empty($element)) throw new CHttpException(404,Yii::t("person","The person you are looking for has been moved or deleted !"));
	
			$connectType = "attendees";
	
		} else if ($type == Poi::COLLECTION){
			$element = Poi::getById($id);
			if (empty($element)) throw new CHttpException(404,Yii::t("poi","The poi you are looking for has been moved or deleted !"));
			$connectType = "attendees";
			$elementAuthorizationId=$element["parentId"];
			$elementAuthorizationType=$element["parentType"];
			if($element["parentType"]==Organization::COLLECTION){
				$params["parent"] = Organization::getSimpleOrganizationById($element["parentId"]);
			}else{
				$params["parent"] = Project::getSimpleProjectById($element["parentId"]); 
			}
		
		}
		$params["controller"] = Element::getControlerByCollection($type);
		if(	@$element["links"] ) {
			if(isset($element["links"][$connectType])){
				$countStrongLinks=0;//count($element["links"][$connectType]);
				$nbMembers=0;
				$invitedNumber=0;
				foreach ($element["links"][$connectType] as $key => $aMember) {
					if($nbMembers < 11){
						if($aMember["type"]==Organization::COLLECTION){
							$newOrga = Organization::getSimpleOrganizationById($key);
							if(!empty($newOrga)){
								if ($aMember["type"] == Organization::COLLECTION && @$aMember["isAdmin"]){
									$newOrga["isAdmin"]=true;  				
								}
								$newOrga["type"]=Organization::COLLECTION;
								//array_push($contextMap["organizations"], $newOrga);
								//array_push($members, $newOrga);
								$members[$key] = $newOrga ;
							}
						} else if($aMember["type"]==Person::COLLECTION){
							//if(!@$aMember["isInviting"]){
								$newCitoyen = Person::getSimpleUserById($key);
								if (!empty($newCitoyen)) {
									if (@$aMember["type"] == Person::COLLECTION) {
										if(@$aMember["isAdmin"]){
											if(@$aMember["isAdminPending"])
												$newCitoyen["isAdminPending"]=true;  
												$newCitoyen["isAdmin"]=true;  	
										}			
										if(@$aMember["toBeValidated"]){
											$newCitoyen["toBeValidated"]=true;  
										}
										if(@$aMember["isInviting"]){
											$newCitoyen["isInviting"]=true;
										}
										if(@$aMember["isAdminInviting"]){
											$newCitoyen["isAdminInviting"]=true;
										}		
					  				
									}
									$newCitoyen["type"]=Person::COLLECTION;
									//array_push($contextMap["people"], $newCitoyen);
									//array_push($members, $newCitoyen);
									$members[$key] = $newCitoyen ;
									$nbMembers++;
								}
							//}
						}
					} 
					if(!@$aMember["isInviting"]){
						$countStrongLinks++;
						if(@$aMember["isAdminInviting"] && @Yii::app()->session["userId"] && $key==Yii::app()->session["userId"])
		  					$params["invitedMe"]=array("invitorId"=>$aMember["invitorId"],"invitorName"=>$aMember["invitorName"],"admin"=>true);
					}
					else{
		  				if(@Yii::app()->session["userId"] && $key==Yii::app()->session["userId"])
		  					$params["invitedMe"]=array("invitorId"=>$aMember["invitorId"],"invitorName"=>$aMember["invitorName"]);
						$invitedNumber++;
					}
					//else {
						//break;
					//}
				}
			}
		}
		if(!@$element["disabled"]){
	        //if((@$config["connectLink"] && $config["connectLink"]) || empty($config)){ TODO CONFIG MUTUALIZE WITH NETWORK AND OTHER PLATFORM
        	if((!@$element["links"][$connectType][Yii::app()->session["userId"]] || (@$element["links"][$connectType][Yii::app()->session["userId"]] && @$element["links"][$connectType][Yii::app()->session["userId"]][Link::TO_BE_VALIDATED])) && @Yii ::app()->session["userId"] && ($type != Person::COLLECTION || $element["_id"] != Yii::app()->session["userId"])){
        		$params["linksBtn"]["followBtn"]=true;
                if (@$element["links"]["followers"][Yii::app()->session["userId"]])
                    	$params["linksBtn"]["isFollowing"]=true;
                 else if(!@$element["links"]["followers"][Yii::app()->session["userId"]]     
                    && $type != Event::COLLECTION)   
                    	$params["linksBtn"]["isFollowing"]=false; 	               
            }
            // Add member , contributor, attendee
            if($type == Organization::COLLECTION)
               $connectAs="member";
            else if($type == Project::COLLECTION)
                $connectAs="contributor";
            else if($type == Event::COLLECTION)
                $connectAs="attendee";
            else if($type==Person::COLLECTION)
            	$connectAs="friend";
           $params["linksBtn"]["connectAs"]=$connectAs;
           $params["linksBtn"]["connectType"]=$connectType;
            if( @Yii::app()->session["userId"] && $type!= Person::COLLECTION && !@$element["links"][$connectType][Yii::app()->session["userId"]]){
            	$params["linksBtn"]["communityBn"]=true;	            	
            	$params["linksBtn"]["isMember"]=false;
            }else if($type != Person::COLLECTION  && @Yii::app()->session["userId"]){
                //Ask Admin button
                $connectAs="admin";
                $params["linksBtn"]["communityBn"]=true;
               	$params["linksBtn"]["isMember"]=true;
               	if(@$element["links"][$connectType][Yii::app()->session["userId"]][Link::TO_BE_VALIDATED])
               		$params["linksBtn"][Link::TO_BE_VALIDATED]=true;
               	$params["linksBtn"]["isAdmin"]=true;
               	if(@$element["links"][$connectType][Yii::app()->session["userId"]][Link::IS_ADMIN_PENDING])
               		$params["linksBtn"][Link::IS_ADMIN_PENDING]=true;
                //Test if user has already asked to become an admin
                if(!in_array(Yii::app()->session["userId"], Authorisation::listAdmins($id, $type,true)))
                	$params["linksBtn"]["isAdmin"]=false;              
            }
        }

        // Link with projects
		if(isset($element["links"]["projects"])){
			foreach ($element["links"]["projects"] as $keyProj => $valueProj) {
				 $project = Project::getPublicData($keyProj);
	       		 $projects[$keyProj] = $project;
			}
		}
		
		// Link with needs
		if(isset($element["links"]["needs"])){
			foreach ($element["links"]["needs"] as $keyNeed => $value){
				$need = Need::getSimpleNeedById($keyNeed);
	       		$needs[$keyNeed] = $need;
			}
		}

		// Link with events
		if(isset($element["links"]["events"])){
			foreach ($element["links"]["events"] as $keyEv => $valueEv) {
				 $event = Event::getSimpleEventById($keyEv);
				 if(!empty($event) && (@$event["endDateSec"] > time() || @$event["startDateSec"] > time()))
           		 	$events[$keyEv] = $event;
			}
		}
		//$lists = Lists::get($listsToRetrieve);
		//$params["eventTypes"] = $list["eventTypes"];
		//$params["subview"]=$view;
		$params["tags"] = array("TODO : écrire la liste de suggestion de tags"); Tags::getActiveTags();
		$params["element"] = $element;
		$params["members"] = $members;
		$params["type"] = $type;
		$params["events"]=$events;
		$params["projects"]=$projects;
		$params["needs"]=$needs;
		$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $elementAuthorizationType, $elementAuthorizationId);
		$params["openEdition"] = Authorisation::isOpenEdition($elementAuthorizationId, $elementAuthorizationType, @$element["preferences"]);
		if(@Yii::app()->session["network"]){
			$params["openEdition"] = false;
			$params["edit"] = false;
		}

		$params["isLinked"] = Link::isLinked($elementAuthorizationId,$elementAuthorizationType, 
									Yii::app()->session['userId'], 
									@$element["links"]);

		if($params["isLinked"]==true)
			$params["countNotifElement"]=ActivityStream::countUnseenNotifications(Yii::app()->session["userId"], $elementAuthorizationType, $elementAuthorizationId);
		if($type==Event::COLLECTION){
			$params["countStrongLinks"]= @$attendeeNumber;
			//$params["countLowLinks"] = @$invitedNumber;
		}
		else{
			$params["countStrongLinks"]= @$countStrongLinks;
			$params["countLowLinks"] = count(@$element["links"]["followers"]);
		}
		$params["countInvitations"]=@$invitedNumber;
		$params["countries"] = OpenData::getCountriesList();

		if(@$_POST["modeEdit"]){
			$params["modeEdit"]=$_POST["modeEdit"];
		}

		//manage delete in progress status
		$params["deletePending"] = Notification::isElementStatusDeletePending($type, $id);
		
		
		return $params;
	}

	public static function getUrlMyNetwork($id, $type) {
		$server = ((isset($_SERVER['HTTPS']) AND (!empty($_SERVER['HTTPS'])) AND strtolower($_SERVER['HTTPS'])!='off') ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];
		$jsonNetwork = $server.Yii::app()->createUrl("network/default/index/id/".$id."/type/".$type);
		$urlNetwork = $server.Yii::app()->createUrl("/?network=".$jsonNetwork);

		return $urlNetwork;
    }



public static function myNetwork($id, $type){
        $myN = json_decode(file_get_contents("../../modules/co2/data/myNetwork.json", FILE_USE_INCLUDE_PATH), true);
        if($type == Person::COLLECTION || $type == Organization::COLLECTION || $type == Event::COLLECTION || $type == Project::COLLECTION){
            $myN["request"]["sourceKey"][0] = $id."@".$type ;

        }

        return $myN;
    }


 	public static function getHash($element){
 		$id = @$element["id"] ? @$element["id"] : @$element["_id"];
 		return  (@$element["slug"]) ? 
				"#".$element["slug"] : 
				"#page.type.".@$element["type"].".id.".$id;
 	}

}