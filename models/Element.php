<?php 
class Element {



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
	    );	
	    
	    if(isset($fas[$type])) return $fas[$type];
	    else return false;
    }
    
    public static function getElementSpecsByType ($type) { 
    	$ctrler = self::getControlerByCollection ($type);
    	$prefix = "#".$ctrler;
		$fas = array(

	    	Person::COLLECTION 			=> array("icon"=>"user","color"=>"#FFC600","text-color"=>"yellow",
	    										 "hash"=> $prefix.".detail.id."),

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

	    	City::CONTROLLER 			=> array("icon"=>"university","color"=>"#E33551","text-color"=>"red",
	    										 "hash"=> $prefix.".detail.insee."),
	    	ActionRoom::TYPE_VOTE		=> array("icon"=>"archive","color"=>"#3C5665", "text-color"=>"dark",
	    		 								 "hash"=> "survey.entries.id.",
	    		 								 "collection"=>ActionRoom::COLLECTION),
	    	ActionRoom::TYPE_VOTE."s"	=> array("icon"=>"archive","color"=>"#3C5665", "text-color"=>"dark",
	    		 								 "hash"=> "survey.entries.id.",
	    		 								 "collection"=>ActionRoom::COLLECTION),
	    	ActionRoom::TYPE_ACTIONS	=> array("icon"=>"cogs","color"=>"#3C5665", "text-color"=>"dark",
	    		 								 "hash"=> "rooms.actions.id.",
	    		 								 "collection"=>ActionRoom::COLLECTION),
	    	ActionRoom::TYPE_ACTIONS."s"=> array("icon"=>"cogs","color"=>"#3C5665", "text-color"=>"dark",
	    		 								 "hash"=> "rooms.actions.id.",
	    		 								 "collection"=>ActionRoom::COLLECTION),
	    	ActionRoom::TYPE_ACTION		=> array("icon"=>"cog","color"=>"#3C5665", "text-color"=>"dark",
	    		 								 "hash"=> "rooms.action.id.",
	    		 								 "collection"=>ActionRoom::COLLECTION_ACTIONS),
	    	ActionRoom::TYPE_ACTION."s"	=> array("icon"=>"cog","color"=>"#3C5665", "text-color"=>"dark",
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

    /**
     * Return a link depending on the type and the id of the element.
     * The HTML link could be kind of : <a href="" onclick="loadByHash(...)">name</a>
     * If loadByHashOnly is set : only the loadByHash will be returned
     * @param String $type The type of the entity
     * @param String $id The id of the entity
     * @param type|null $loadByHashOnly if true, will return only the loadbyhash not surounded by the html link
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
	    $link = '<a href="#'.$link.'" class="lbh">'.htmlspecialchars(@$el['name']).'</a>';
	    
    	return $link;
    }

	public static function getByTypeAndId($type, $id){
		if($type == Person::COLLECTION)
			$element = Person::getById($id);
		else if($type == Organization::COLLECTION)
			$element = Organization::getById($id);		
		else if($type == Project::COLLECTION)
			$element = Project::getById($id);	
		else if($type == Event::COLLECTION)
			$element = Event::getById($id);	
		else if($type == City::COLLECTION)
			$element = City::getIdByInsee($id);
		return $element;
	}

    public static function getInfos( $type, $id, $loadByHashOnly=null ) {	    
    	$link = ""; 
    	$name = ""; 
    	if(@$type && @$id && $type != City::COLLECTION){
    		$el = PHDB::findOne ( $type , array( "_id" => new MongoId($id) ) );
	    	$ctrl = self::getControlerByCollection($type);
	    	if( @$el && @$ctrl )
	    		$link = "loadByHash('#".$ctrl.".detail.id.".$id."')";
	    }
	    else if($type == City::COLLECTION){
	    	$el = City::getByUnikey($id);
	    	$ctrl = self::getControlerByCollection($type);
	    	if( @$el && @$ctrl )
	    		$link = "loadByHash('#".$ctrl.".detail.insee.".$el['insee'].".cp.".$el['cp']."')";
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
		else
			return array();
	}

    private static function getCollectionFieldNameAndValidate($collection, $elementFieldName, $elementFieldValue, $elementId) {
		return DataValidator::getCollectionFieldNameAndValidate(self::getDataBinding($collection), $elementFieldName, $elementFieldValue, $elementId);
	}



    public static function updateField($collection, $id, $fieldName, $fieldValue) {

    	if (!Authorisation::canEditItemOrOpenEdition($id, $collection, Yii::app()->session['userId'])) {
			throw new CTKException("Can not update the element : you are not authorized to update that element !");
		}
		if(is_string($fieldValue))
			$fieldValue = trim($fieldValue);

		$dataFieldName = self::getCollectionFieldNameAndValidate($collection, $fieldName, $fieldValue, $id);
		
		//$verb = ($fieldValue == "" || $fieldValue == null ) ? '$unset' : '$set';
		$verb = '$set' ;
		//$set = array($fieldName => $fieldValue);

		//Specific case : 
		//Tags
		//var_dump($dataFieldName);
		if ($dataFieldName == "tags") {
			$fieldValue = Tags::filterAndSaveNewTags($fieldValue);
			$set = array($dataFieldName => $fieldValue);
		}
		else if ( ($dataFieldName == "telephone.mobile"|| $dataFieldName == "telephone.fixe" || $dataFieldName == "telephone.fax")){
			if($fieldValue ==null)
				$fieldValue = array();
			else
				$fieldValue = explode(",", $fieldValue);
			$set = array($dataFieldName => $fieldValue);
		}
		else if ($fieldName == "address") {
			//address
			if(!empty($fieldValue["postalCode"]) && !empty($fieldValue["codeInsee"])) {
				$insee = $fieldValue["codeInsee"];
				$postalCode = $fieldValue["postalCode"];
				$cityName = $fieldValue["addressLocality"];
				$address = SIG::getAdressSchemaLikeByCodeInsee($insee, $postalCode,$cityName);
				$set = array("address" => $address);
				if (!empty($fieldValue["streetAddress"]))
					$set["address"]["streetAddress"] = $fieldValue["streetAddress"];
				if(empty($fieldValue["geo"])){
					$set["geo"] = SIG::getGeoPositionByInseeCode($insee, $postalCode,$cityName);
					//SIG::updateEntityGeoposition($collection,$id,$geo["latitude"],$geo["longitude"]);
					SIG::updateEntityGeoposition($collection,$id,$set["geo"]["latitude"],$set["geo"]["longitude"]);
				}
				if($collection == Person::COLLECTION){
					$user = Yii::app()->session["user"];
					$user["codeInsee"] = $insee;
					$user["postalCode"] = $postalCode;
					$user["address"] = $address;
					Yii::app()->session["user"] = $user;
				}
			} else 
				throw new CTKException("Error updating  : address is not well formated !");			
		}
		else if ($dataFieldName == "birthDate") 
		{
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
			if( !is_string( $fieldValue ) && get_class( $fieldValue ) == "MongoDate"){
				$newMongoDate = $fieldValue;
			}else{
				$dt = DateTime::createFromFormat('Y-m-d H:i', $fieldValue);
				if (empty($dt)) {
					$dt = DateTime::createFromFormat('Y-m-d', $fieldValue);
				}
				$newMongoDate = new MongoDate($dt->getTimestamp());
			}
			$set = array($dataFieldName => $newMongoDate);	
		}
		else if ($dataFieldName == "seePreferences") {
			//var_dump($fieldValue);
			if($fieldValue == "false"){
				//$verb = "$unset";
				$verb = '$unset' ;
				$set = array($dataFieldName => "");
			}else{
				$set = array($dataFieldName => $fieldValue);
			}
		}
		else
			$set = array($dataFieldName => $fieldValue);

		if(Person::COLLECTION == $collection){
			if ( $fieldValue == "bgClass") {
				//save to session for all page reuse
				$user = Yii::app()->session["user"];
				$user["bg"] = $fieldValue;
				Yii::app()->session["user"] = $user;
			} else if ( $fieldName == "bgUrl") {
				//save to session for all page reuse
				$user = Yii::app()->session["user"];
				$user["bgUrl"] = $fieldValue;
				Yii::app()->session["user"] = $user;
			} 
		}else{
			$set["modified"] = new MongoDate(time());
			$set["updated"] = time();
		}
		
		//update 
		$resUpdate = PHDB::update( $collection, array("_id" => new MongoId($id)), 
		                          array($verb => $set));
		$res = array("result"=>false,"msg"=>"");

		if($resUpdate["ok"]==1){
			if( $collection != Person::COLLECTION && Authorisation::isOpenEdition($id, $collection) && $dataFieldName != "badges"){
				// Add in activity to show each modification added to this entity
						//echo $dataFieldName;
				ActivityStream::saveActivityHistory(ActStr::VERB_UPDATE, $id, $collection, $dataFieldName, $fieldValue);
			}
			$res = array("result"=>true,"msg"=>Yii::t(Element::getControlerByCollection($collection),"The ".Element::getControlerByCollection($collection)." has been updated"));
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
     
    public static function getAllLinks($links,$type){
	    if($type == Organization::COLLECTION)
		    $connectAs="members";
	    else if($type == Project::COLLECTION)
		    $connectAs="contributors";
		else if ($type == Event::COLLECTION)
			$connectAs="attendees";
		else if ($type == Person::COLLECTION)
			$connectAs="knows";

	    $contextMap = array();
		$contextMap["people"] = array();
		$contextMap["guests"] = array();
		$contextMap["attendees"] = array();
		$contextMap["organizations"] = array();
		$contextMap["projects"] = array();
		$contextMap["events"] = array();
		$contextMap["followers"] = array();
		if(!empty($links)){
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
				            $citoyen = Person::getPublicData( $key );
				  	        if(!empty($citoyen)) {
				              array_push( $follows[Person::COLLECTION], $citoyen );
				            }
			        	}
						if( $member['type'] == Organization::COLLECTION ) {
							$organization = Organization::getPublicData($key);
							if(!empty($organization)) {
								array_push($follows[Organization::COLLECTION], $organization );
							}
						}
						if( $member['type'] == Project::COLLECTION ) {
						    $project = Project::getPublicData($key);
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


	public static function save($params){

        //var_dump($params);
            $id = null;
            //var_dump($params);
            $data = null;
            $collection = $params["collection"];
            if( !empty($params["id"]) ){
                $id = $params["id"];
            }
            $key = $params["key"];

            $url = ( @$params["parentType"] && @$params["parentId"] && in_array($collection, array("poi"))) ? "#".$params["parentType"].".detail.id.".$params["parentId"] : null; 

            unset($params['collection']);
            unset($params['key']);


            //empty fields aren't properly validated and must be removed
            foreach ($params as $k => $v) {
                if($v== "")
                    unset($params[$k]);
            }
            $params["creator"] = Yii::app()->session["userId"];
            $params["created"] = time();
            /*$microformat = PHDB::findOne(PHType::TYPE_MICROFORMATS, array( "key"=> $key));
            $validate = ( !isset($microformat )  || !isset($microformat["jsonSchema"])) ? false : true;
            //validation process based on microformat defeinition of the form
            */
            //validation process based on databind on each Elemnt Model
            
            $valid = DataValidator::validate( ucfirst($key), json_decode (json_encode ($params)) );
            
            if( $valid["result"] )
            {
                if($id)
                {
                    //update a single field
                    //else update whole map
                    $changeMap = ( !$microformat && isset( $key )) ? array('$set' => array( $key => $params[ $key ] ) ) : array('$set' => $params );
                    PHDB::update($collection,array("_id"=>new MongoId($id)), $changeMap);
                    $res = array("result"=>true,
                                 "msg"=>"Vos données ont été mise à jour.",
                                 "reload"=>true,
                                 "map"=>$params,
                                 "id"=>(string)$params["_id"]);
                } 
                else 
                {
                    $params["created"] = time();
                    PHDB::insert($collection, $params );
                    $res = array("result"=>true,
                                 "msg"=>"Vos données ont bien été enregistré.",
                                 "reload"=>true,
                                 "map"=>$params,
                                 "id"=>(string)$params["_id"]);  

                    if( @$params["parentType"] && @$params["parentId"] ){
                        //createdObjectAsParam($authorType, $authorId, $objectType, $objectId, $targetType, $targetId, $geo, $tags, $address, $verb="create")
                        Notification::createdObjectAsParam(Person::COLLECTION,Yii::app()->session["userId"],$class::COLLECTION, (String)$params["parentId"], $params["parentType"], $params["parentId"], $newProject["geo"], (isset($newProject["tags"])) ? $newProject["tags"]:null ,$newProject["address"]);  
                    }
                }
                if(@$url)
                    $res["url"] = $url;

            } else 
                $res = array( "result" => false, 
                              "msg" => Yii::t("common","Something went really bad : Invalid Content") );

        return $res;
     }
}