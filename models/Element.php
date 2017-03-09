<?php 
class Element {

	public static $urlTypes = array(
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
	    	ActionRoom::COLLECTION => ActionRoom::CONTROLLER,
	    	ActionRoom::COLLECTION_ACTIONS => ActionRoom::CONTROLLER,
	    );	    
    	return @$ctrls[$type];
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
	    	Organization::TYPE_GROUP 	=> "black",
	    	Organization::TYPE_GOV 		=> "red",
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
        } else if ($type == Person::COLLECTION) {
        	$res = Person::getById($id);
        } else if ($type== Event::COLLECTION){
        	$res = Event:: getById($id);
        } else if ($type== Project::COLLECTION){
        	$res = Project:: getById($id);
        } else if ($type== Need::COLLECTION){
            $res = Need:: getById($id);
        }else if ($type == Poi::COLLECTION){
            $res = Poi:: getById($id);
        } else if ($type== ActionRoom::COLLECTION_ACTIONS){
            $res = ActionRoom:: getActionById($id);
        } else if ( $type == Survey::COLLECTION) {
            $res = Survey::getById($id);
        } else {

        	throw new CTKException("Can not manage this type : ".$type);
        }
        if (empty($res)) throw new CTKException("The actor (".$id." / ".$type.") is unknown");

        return $res;
    }
    

    /**
     * Return a link depending on the type and the id of the element.
     * The HTML link could be kind of : <a href="" onclick="url.loadByHash(...)">name</a>
     * If url.loadByHashOnly is set : only the url.loadByHash will be returned
     * @param String $type The type of the entity
     * @param String $id The id of the entity
     * @param type|null $loadByHashOnly if true, will return only the url.loadByHash not surounded by the html link
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
		else if($type == Organization::COLLECTION)
			$element = Organization::getById($id);		
		else if($type == Project::COLLECTION)
			$element = Project::getById($id);	
		else if($type == Event::COLLECTION)
			$element = Event::getById($id);	
		else if($type == City::COLLECTION)
			$element = City::getIdByInsee($id);
		else if($type == Poi::COLLECTION)
			$element = Poi::getById($id);
		else
			$element = PHDB::findOne($type,array("_id"=>new MongoId($id)));
	  	
	  	return $element;
	}

	/**
	 * get all poi details of an element
	 * @param type $id : is the mongoId (String) of the parent
	 * @param type $type : is the type of the parent
	 * @return list of pois
	 */
	public static function getByIdAndTypeOfParent($collection, $id, $type){
		$list = PHDB::find($collection,array("parentId"=>$id,"parentType"=>$type));
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
	    		$link = "url.loadByHash('#".$ctrl.".detail.id.".$id."')";
	    }
	    else if($type == City::COLLECTION){
	    	$el = City::getByUnikey($id);
	    	$ctrl = self::getControlerByCollection($type);
	    	if( @$el && @$ctrl )
	    		$link = "url.loadByHash('#".$ctrl.".detail.insee.".$el['insee'].".cp.".$el['cp']."')";
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
		else
			return array();
	}

    private static function getCollectionFieldNameAndValidate($collection, $elementFieldName, $elementFieldValue, $elementId) {
		return DataValidator::getCollectionFieldNameAndValidate(self::getDataBinding($collection), $elementFieldName, $elementFieldValue, $elementId);
	}



    public static function updateField($collection, $id, $fieldName, $fieldValue) {
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
				         "id" => "468768",
				        "name" => "mairie",
				        "codeInsee" => $fieldValue["address"]["codeInsee"],
				        "addressCountry" => $fieldValue["address"]["addressCountry"],
				        "postalCode" => $fieldValue["address"]["postalCode"],
				        "addressLocality" => $fieldValue["address"]["addressLocality"],
				        "streetAddress" => ((@$fieldValue["address"]["streetAddress"])?trim(@$fieldValue["address"]["streetAddress"]):""),
				        "depName" => ((@$fieldValue["address"]["depName"])?trim(@$fieldValue["address"]["depName"]):""),
				        "regionName" => ((@$fieldValue["address"]["regionName"])?trim(@$fieldValue["address"]["regionName"]):""),
				    	);
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
						Person::updateCookieCommunexion($id, null);
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
					        "depName" => ((@$fieldValue["address"]["depName"])?trim(@$fieldValue["address"]["depName"]):""),
				        	"regionName" => ((@$fieldValue["address"]["regionName"])?trim(@$fieldValue["address"]["regionName"]):""),
					    	);
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
							$pull="contacts";
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
		
		else if ($dataFieldName == "birthDate") {
			date_default_timezone_set('UTC');
			$dt = DateTime::createFromFormat('Y-m-d H:i', $fieldValue);
			if (empty($dt)) {
				$dt = DateTime::createFromFormat('Y-m-d', $fieldValue);
			}
			$newMongoDate = new MongoDate($dt->getTimestamp());
			$set = array($dataFieldName => $newMongoDate);
		//Date format
		} else if ($dataFieldName == "startDate" || $dataFieldName == "endDate") {
			date_default_timezone_set('UTC');
			$dt = DataValidator::getDateTimeFromString($fieldValue, $dataFieldName);
			$newMongoDate = new MongoDate($dt->getTimestamp());
			$set = array($dataFieldName => $newMongoDate);
		} else if ($dataFieldName == "organizer") {
			$set = array("organizerId" => $fieldValue["organizerId"], 
							 "organizerType" => $fieldValue["organizerType"]);
			//get element and remove current organizer
			$element = Element::getElementById($id, $collection);
			$oldOrganizerId = @$element["organizerId"] ? $element["organizerId"] : key($element["links"]["organizer"]);
			$oldOrganizerType = @$element["organizerType"] ? $element["organizerType"] : $element["links"]["organizer"][$oldOrganizerId]["type"];
			//remove the old organizer
			$res = Link::removeOrganizer($oldOrganizerId, $oldOrganizerType, $id, Yii::app()->session["userId"]);
			if (! @$res["result"]) throw new CTKException(@$res["msg"]);
			//add new organizer
			$res = Link::addOrganizer($fieldValue["organizerId"], $fieldValue["organizerType"], $id, Yii::app()->session["userId"]);
			if (! @$res["result"]) throw new CTKException(@$res["msg"]);

		} else if ($dataFieldName == "seePreferences") {
			//var_dump($fieldValue);
			if($fieldValue == "false"){
				$verb = '$unset' ;
				$set = array($dataFieldName => "");
			}else{
				$set = array($dataFieldName => $fieldValue);
			}
		} else if ($dataFieldName == "contacts") {
			if(empty($fieldValue["index"]))
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
			if(empty($fieldValue["index"]))
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
			$res = array("result"=>true,"msg"=>Yii::t(Element::getControlerByCollection($collection),"The ".Element::getControlerByCollection($collection)." has been updated"), "fieldName" => $fieldName, "value" => $fieldValue);

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
     
    public static function getAllLinks($links,$type, $id){
	    $contextMap = array();
		$contextMap["people"] = array();
		$contextMap["guests"] = array();
		$contextMap["attendees"] = array();
		$contextMap["organizations"] = array();
		$contextMap["projects"] = array();
		$contextMap["events"] = array();
		$contextMap["followers"] = array();


	    if($type == Organization::COLLECTION){
	    	$connectAs="members";
	    	$elt = Organization::getSimpleOrganizationById($id);
			$newOrga["type"]=Organization::COLLECTION;
			array_push($contextMap["organizations"], $elt);
	    }
	    else if($type == Project::COLLECTION){
	    	$connectAs="contributors";
	    	$elt = Project::getSimpleProjectById($id);
			array_push($contextMap["projects"], $elt);
	    }
		else if ($type == Event::COLLECTION){
			$connectAs="attendees";
			$elt = Event::getSimpleEventById($id);
			array_push($contextMap["events"], $elt);
		}
		else if ($type == Person::COLLECTION){
			$connectAs="follows";
			$elt = Person::getSimpleUserById($id);
			array_push($contextMap["people"], $elt);
		}
	    
		if(!empty($links) && 
			( (Preference::showPreference($elt, $type, "directory", Yii::app()->session["userId"]) && $type == Person::COLLECTION) || 
			$type != Person::COLLECTION) ) {
			if(isset($links[$connectAs])){
				foreach ($links[$connectAs] as $key => $aMember) {
					if($type==Event::COLLECTION){
						$citoyen = Person::getSimpleUserById($key);
						if(!empty($citoyen)){
							if(@$aMember["invitorId"])  {
								array_push($contextMap["guests"], $citoyen);
							}
							else{
				                  if(@$e["isAdmin"]){
				                    if(@$e["isAdminPending"])
				                      $citoyen["isAdminPending"]=true;
				                    $citoyen["isAdmin"]=true;         
				                  }
								  array_push($contextMap["attendees"], $citoyen);
							}
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
									array_push($contextMap["organizations"], $newOrga);
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
								array_push($contextMap["people"], $newCitoyen);
							}
						}
					}
				}
			}
			// Link with events
			if(isset($links["events"])){
				foreach ($links["events"] as $keyEv => $valueEv) {
					 $event = Event::getSimpleEventById($keyEv);
					 if(!empty($event))
					 	array_push($contextMap["events"], $event);
				}
			}
	
			// Link with projects
			if(isset($links["projects"])){
				foreach ($links["projects"] as $keyProj => $valueProj) {
					 $project = Project::getSimpleProjectById($keyProj);
					 if (!empty($project))
	           		 array_push($contextMap["projects"], $project);
				}
			}

			if(isset($links["followers"])){
				foreach ($links["followers"] as $key => $value) {
					$newCitoyen = Person::getSimpleUserById($key);
					if (!empty($newCitoyen))
					array_push($contextMap["followers"], $newCitoyen);
				}
			}
			if(isset($links["memberOf"])){
				foreach ($links["memberOf"] as $key => $value) {
					$newOrga = Organization::getSimpleOrganizationById($key);
					if (!empty($newOrga))
					array_push($contextMap["organizations"], $newOrga);
				}
			}

			if(isset($links["subEvents"])){
				foreach ($links["subEvents"] as $keyEv => $valueEv) {
					 $event = Event::getSimpleEventById($keyEv);
					 if(!empty($event))
					 	array_push($contextMap["events"], $event);
				}
			}

			$follows = array("citoyens"=>array(),
  					"projects"=>array(),
  					"organizations"=>array(),
  					"count" => 0
  			);
  			if ($type == Person::COLLECTION){
	  			$contextMap["follows"] = array();
				$countFollows=0;
			    if (@$links["follows"]) {
			        foreach ( @$links["follows"] as $key => $member ) {
			          	if( $member['type'] == Person::COLLECTION ) {
				            $citoyen = Person::getSimpleUserById( $key );
				  	        if(!empty($citoyen)) {
				              array_push( $follows[Person::COLLECTION], $citoyen );
				            }
			        	}
						if( $member['type'] == Organization::COLLECTION ) {
							$organization = Organization::getSimpleOrganizationById($key);
							if(!empty($organization)) {
								array_push($follows[Organization::COLLECTION], $organization );
							}
						}
						if( $member['type'] == Project::COLLECTION ) {
						    $project = Project::getSimpleProjectById($key);
						    if(!empty($project)) {
								array_push( $follows[Project::COLLECTION], $project );
							}
						}
						$countFollows++;
		        	}
				}	
				$follows["count"]= $countFollows;
				$contextMap["follows"] = $follows;
			}
			
		}
		//error_log("get POI for id : ".$id." - type : ".$type);
		if(isset($id)){
			$pois = PHDB::find(Poi::COLLECTION,array("parentId"=>$id,"parentType"=>$type));
			if(!empty($pois)) {
				$allPois = array();
				if(!is_array($pois)) $pois = array($pois);
				foreach ($pois as $key => $value) {
					if(@$value["type"])
						$value["typeSig"] = Poi::COLLECTION.".".$value["type"];
					else
						$value["typeSig"] = Poi::COLLECTION;
					$allPois[] = $value;
				}
				$contextMap["pois"] = $allPois;
			}else{
				$contextMap["pois"] = array();
			}
		}
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

	public static function delete($elementType,$elementId,$userId) {
		
		if ($elementType != Poi::COLLECTION && $elementType != Poi::CONTROLLER) {
            return array( "result" => false, "msg" => "For now you can only delete Points of interest" );   
        }
		if ( !@$userId) {
            return array( "result" => false, "msg" => "You must be loggued to delete something" );
        }
        
        $el = self::getByTypeAndId($elementType, $elementId);
        //TODO : we could also allow admins
        if ( $userId != $el['creator']) {
            return array( "result" => false, "msg" => "You must be owner to delete something" );    
        }
        
		PHDB::remove($elementType, array("_id"=>new MongoId($elementId)));
		//since userId is creator 
		//todo for more complexe elements 
		$resDocs = Document::removeDocumentByFolder($elementType."/".$elementId);
		
		return array("result" => true, "msg" => "The element has been deleted succesfully", "resDocs" => $resDocs);
	}

	public static function save($params){
        $id = null;
        $data = null;
        if(!@$params["collection"] && !@$params["key"])
        	return array("result"=> false, "error"=>"400", "msg" => "Bad Request");

        $collection = $params["collection"];
        
        if( !empty($params["id"]) ){
        	$id = $params["id"];
        }
        $key = $params["key"];

		//$paramsImport = (empty($params["paramsImport"])?null:$params["paramsImport"]);
		$paramsLinkImport = ( empty($params["paramsImport"] ) ? null : $params["paramsImport"]);

		unset($params["paramsImport"]);
        unset($params['key']);
       
        $params = self::prepData( $params );
        unset($params['collection']);
        unset($params['id']);

        $postParams = array();
        if( !in_array($collection, array("poi")) && @$params["urls"] && @$params["medias"] ){
        	$postParams["medias"] = $params["medias"];
        	unset($params['medias']);
        	$postParams["urls"] = $params["urls"];
        	unset($params['urls']);
        }

        if($collection == City::COLLECTION)
        	$params = City::prepCity($params);
        
        /*$microformat = PHDB::findOne(PHType::TYPE_MICROFORMATS, array( "key"=> $key));
        $validate = ( !isset($microformat )  || !isset($microformat["jsonSchema"])) ? false : true;
        //validation process based on microformat defeinition of the form
        */
        //validation process based on databind on each Elemnt Mode
        $valid = array("result"=>true);
        if( $collection == Event::COLLECTION ){
            $valid = Event::validateFirst($params);
        }
        if( $valid["result"] )
        	try {
        		$valid = DataValidator::validate( ucfirst($key), json_decode (json_encode ($params), true) );
        	} catch (CTKException $e) {
        		$valid = array("result"=>false, "msg" => $e->getMessage());
        	}
        
        if( $valid["result"]) 
        {
			if( $collection == Event::COLLECTION )
			{
            	 $res = Event::formatBeforeSaving($params);
            	 if ($res["result"]) 
            	 	$params = $res["params"];
            	 else
            	 	throw new CTKException("Error processing before saving on event");
            }

            if($id) 
            {
            	//var_dump($params);
                //update a single field
                //else update whole map
                //$changeMap = ( !$microformat && isset( $key )) ? array('$set' => array( $key => $params[ $key ] ) ) : array('$set' => $params );
                $exists = PHDB::findOne($collection,array("_id"=>new MongoId($id)));
                if(!@$exists){
                	$params["creator"] = Yii::app()->session["userId"];
	        		$params["created"] = time();
                	PHDB::updateWithOptions($collection,array("_id"=>new MongoId($id)), array('$set' => $params ),array('upsert' => true ));
                }
                else
                	PHDB::update($collection,array("_id"=>new MongoId($id)), array('$set' => $params ));
                $res = array("result"=>true,
                             "msg"=>"Vos données ont été mises à jour.",
                             "reload"=>true,
                             "map"=>$params,
                             "id"=>$id);
            } 
            else 
            {
                $params["created"] = time();
                PHDB::insert($collection, $params );
                $res = array("result"=>true,
                             "msg"=>"Vos données ont bien été enregistrées.",
                             "reload"=>true,
                             "map"=>$params,
                             "id"=>(string)$params["_id"]);  
                //TODO
                //self::afterSave();
                
                // ***********************************
                //post process for specific actions
                // ***********************************
                if( $collection == Organization::COLLECTION )
                	$res["afterSave"] = Organization::afterSave($params, Yii::app()->session["userId"], $paramsLinkImport);
                else if( $collection == Event::COLLECTION )
                	$res["afterSave"] = Event::afterSave($params);
                else if( $collection == Project::COLLECTION )
                	$res["afterSave"] = Project::afterSave($params, @$params["parentId"] , @$params["parentType"] );
                $res["afterSaveGbl"] = self::afterSave((string)$params["_id"],$collection,$params,$postParams);
                //if( false && @$params["parentType"] && @$params["parentId"] )
                //{
                    //createdObjectAsParam($authorType, $authorId, $objectType, $objectId, $targetType, $targetId, $geo, $tags, $address, $verb="create")
                    //TODO
                    //Notification::createdObjectAsParam($authorType[Person::COLLECTION],$userId,$elementType, $elementType, $parentType[projet crée par une orga => orga est parent], $parentId, $params["geo"], (isset($params["tags"])) ? $params["tags"]:null ,$params["address"]);  
                //}
            }
          //  if(@$url = ( @$params["parentType"] && @$params["parentId"] && in_array($collection, array("poi") && Yii::app()->theme != "notragora")) ? "#".self::getControlerByCollection($params["parentType"]).".detail.id.".$params["parentId"] : null )
	        //    $res["url"] = $url;
	        if(@$params["parentType"] && @$params["parentId"] && in_array($collection, array("poi","classified"))){
		        if(Yii::app()->theme->name != "notragora")
		        	$url="#".self::getControlerByCollection($params["parentType"]).".detail.id.".$params["parentId"];
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

    public static function afterSave ($id, $collection, $params,$postParams) {
    	$res = array();
    	if( @$postParams["medias"] )
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
    		$res["medias"] = self::save($poiParams);
    	}
    	return $res;
    }

    public static function prepData ($params) { 

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

	    if (isset($params["allDay"])) {
	    	if ($params["allDay"] == "true") {
				$params["allDay"] = true;
			} else {
				$params["allDay"] = false;
			}
		}

		if(isset($params["name"])) 
	    	$params["name"] = $params["name"];
	
		//TODO SBAR - Manage elsewhere (maybe in the view)
		//Manage the event startDate and endDate format : 
		//it comes with the format DD/MM/YYYY HH:ii or DD/MM/YYYY 
		//and must be transform in YYYY-MM-DD HH:ii
		/*if (@$params["startDate"]) {
			$startDate = DateTime::createFromFormat('d/m/Y', $params["startDate"]);
			if (empty($startDate)) {
				$startDate = DateTime::createFromFormat('d/m/Y H:i', $params["startDate"]);
				if (! empty($startDate)) 
					$params["startDate"] = $startDate->format('Y-m-d H:i');
				else 
					throw new CTKException("Start Date is not well formated !");
			} else 
				$params["startDate"] = $startDate->format('Y-m-d');
		}
		if (@$params["endDate"]) {
			$endDate = DateTime::createFromFormat('d/m/Y', $params["endDate"]);
			if (empty($endDate)) {
				$endDate = DateTime::createFromFormat('d/m/Y H:i', $params["endDate"]);
				if (! empty($endDate)) 
					$params["endDate"] = $endDate->format('Y-m-d H:i');
				else 
					throw new CTKException("End Date is not well formated !");
			} else 
				$params["endDate"] = $endDate->format('Y-m-d');
		}*/

        return $params;
     }

	public static function alreadyExists ($params, $collection) {
		$result = array("result" => false);
		$where = array(	"name" => $params["name"],
						"address.codeInsee" => $params["address"]["codeInsee"]);
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
		$fields = array("_id", "name");
		$element = self::getElementById($id, $collection, $where ,$fields) ;
		return @$element;
	}

	public static function followPerson($params, $gmail=null){
		
		$invitedUserId = "";

        if (empty(Yii::app()->session["userId"])) {
        	Rest::json(array("result" => false, "msg" => "The current user is not valid : please login."));
        	die();
        }
        
        //Case spécial : Vérifie si l'email existe et retourne l'id de l'utilisateur
        if (!empty($params["invitedUserEmail"]))
        	$invitedUserId = Person::getPersonIdByEmail($params["invitedUserEmail"]) ;

        //Case 1 : the person invited exists in the db
        if (!empty($params["connectUserId"]) || !empty($invitedUserId)) {
        	if (!empty($params["connectUserId"]))
        		$invitedUserId = $params["connectUserId"];

        	$child["childId"] = Yii::app()->session["userId"] ;
        	$child["childType"] = Person::COLLECTION;

        	$res = Link::follow($invitedUserId, Person::COLLECTION, $child);
            $actionType = ActStr::VERB_FOLLOW;
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
		}
		
        if (@$res["result"] == true) {
            $person = Person::getSimpleUserById($invitedUserId);
            $res = array("result" => true, "invitedUser" => $person);
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
			if(!empty($value["mail"])){
				$params["invitedUserEmail"] = $value["mail"] ;

				if(empty($value["name"])){
					$split = explode("@", $value["mail"]);
					$params["invitedUserName"] = $split[0];
				}else
					$params["invitedUserName"] = $value["name"] ;
				$result["data"][] = Element::followPerson($params, $gmail);
			}
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
		if(!empty($params["idContact"]))
			$params["id"] = $params["idContact"];
		unset($params["parentId"]);
		unset($params["parentType"]);
		unset($params["phone"]);
		unset($params["idContact"]);


		if(empty($params["name"]) && empty($params["email"]) && empty($params["role"]) && empty($params["telephone"]))
			$res = array("result" => false, "msg" => "Vous devez avoir au moins une information sur le contact");
		else
			$res = self::updateField($collection, $id, "contacts", $params);

		if($res["result"])
			$res["msg"] = "Les contacts ont été mis à jours";
		return $res;
	}

	public static function saveUrl($params){
		$id = $params["parentId"];
		$collection = $params["parentType"];
		$params["url"]=self::getAndCheckUrl($params["url"]);

		unset($params["parentId"]);
		unset($params["parentType"]);
		$res = self::updateField($collection, $id, "urls", $params);
		if($res["result"])
			$res["msg"] = "Les urls ont été mis à jours";
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


	public static function updateBlock($params){
		$block = $params["block"];
		$collection = $params["typeElement"];
		$id = $params["id"];

		$res = array();
		if($block == "contact"){
			if(isset($params["email"]))
				$res[] = self::updateField($collection, $id, "email", $params["email"]);
			if(isset($params["url"]))
				$res[] = self::updateField($collection, $id, "url", self::getAndCheckUrl($params["url"]));
			if(isset($params["birthDate"]))
				$res[] = self::updateField($collection, $id, "birthDate", $params["birthDate"]);
			if(isset($params["fixe"]))
				$res[] = self::updateField($collection, $id, "fixe", $params["fixe"]);
			if(isset($params["fax"]))
				$res[] = self::updateField($collection, $id, "fax", $params["fax"]);
			if(isset($params["mobile"]))
				$res[] = self::updateField($collection, $id, "mobile", $params["mobile"]);

		}else if($block == "info"){
			if(isset($params["name"]))
				$res[] = self::updateField($collection, $id, "name", $params["name"]);
			if(isset($params["username"]))
				$res[] = self::updateField($collection, $id, "username", $params["username"]);
			if(isset($params["shortDescription"]))
				$res[] = self::updateField($collection, $id, "shortDescription", $params["shortDescription"]);
			if(isset($params["avancement"]))
				$res[] = self::updateField($collection, $id, "avancement", $params["avancement"]);
			if(isset($params["tags"]))
				$res[] = self::updateField($collection, $id, "tags", $params["tags"]);
			if(isset($params["type"]))
				$res[] = self::updateField($collection, $id, "type", $params["type"]);
			if(isset($params["telegramAccount"]))
				$res[] = self::updateField($collection, $id, "telegramAccount", $params["telegramAccount"]);
			if(isset($params["facebookAccount"]))
				$res[] = self::updateField($collection, $id, "facebookAccount", self::getAndCheckUrl($params["facebookAccount"]));
			if(isset($params["twitterAccount"]))
				$res[] = self::updateField($collection, $id, "twitterAccount", self::getAndCheckUrl($params["twitterAccount"]));
			if(isset($params["gitHubAccount"]))
				$res[] = self::updateField($collection, $id, "gitHubAccount", self::getAndCheckUrl($params["gitHubAccount"]));
			if(isset($params["gpplusAccount"]))
				$res[] = self::updateField($collection, $id, "url", self::getAndCheckUrl($params["gpplusAccount"]));
			if(isset($params["skypeAccount"]))
				$res[] = self::updateField($collection, $id, "url", self::getAndCheckUrl($params["skypeAccount"]));
		}else if($block == "when"){
			if(isset($params["allDay"]))
				$res[] = self::updateField($collection, $id, "allDay", (($params["allDay"] == "true") ? true : false));
			if(isset($params["startDate"]))
				$res[] = self::updateField($collection, $id, "startDate", $params["startDate"]);
			if(isset($params["endDate"]))
				$res[] = self::updateField($collection, $id, "endDate", $params["endDate"]);
		}else if($block == "toMarkdown"){
			$res[] = self::updateField($collection, $id, "description", $params["value"]);
			$res[] = self::updateField($collection, $id, "descriptionHTML", null);
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
				$msg .= $value["fieldName"];
				$values[$value["fieldName"]] = $value["value"];
			}else{
				if($msgError != "")
					$msgError .= ". ";
				$msgError .= $value["mgs"];
			}
		}

		if($msg != ""){
			$resultGoods["result"]=true;
			$resultGoods["msg"]=Yii::t("common", "The next attributs has been updated : ".$msg);
			$resultGoods["values"] = $values ;
			$result["resultGoods"] = $resultGoods ;
			$result["result"] = true ;
		}

		if($msgError != ""){
			$resultErrors["result"]=false;
			$resultErrors["msg"]=Yii::t("common", $msgError);
			$result["resultErrors"] = $resultErrors ;
		}

		return $result;
	}

}