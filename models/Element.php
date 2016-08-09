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

    public static function getFaIcon ($type) { 

		$fas = array(
	    	Organization::COLLECTION 	=> "group",
	    	Person::COLLECTION 			=> "user",
	    	Event::COLLECTION 			=> "calendar",
	    	Project::COLLECTION 		=> "lightbulb-o",
			News::COLLECTION 			=> "rss",
	    	Need::COLLECTION 			=> "cubes",
	    	City::COLLECTION 			=> "university"
	    );	
	    
	    if(isset($fas[$type])) return $fas[$type];
	    else return false;
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
    public static function getLink( $type, $id, $loadByHashOnly=null ) {	    
    	$link = ""; 
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


    public static function updateField($collection, $id, $fieldName, $fieldValue) {
		//$fieldName = Organization::getCollectionFieldNameAndValidate($fieldName, $fieldValue, $id);
		$verb = ($fieldValue == "" || $fieldValue == null ) ? '$unset' : '$set';
		$set = array($fieldName => $fieldValue);

		//Specific case : 
		//Tags
		if ($fieldName == "tags") {
			$fieldValue = Tags::filterAndSaveNewTags($fieldValue);
			$set = array($fieldName => $fieldValue);
		} 
		else if ($fieldName == "telephone") {
			//Telephone
			$tel = array();
			$fixe = array();
			$mobile = array();
			
			if(!empty($fieldValue))
			{
				foreach ($fieldValue as $key => $value) {
					if(substr($value, 0, 2) == "02")
						$fixe[] = $value ;
					else
						$mobile[] = $value ;

					if(!empty($fixe))
						$tel["fixe"] = $fixe;
					if(!empty($mobile))
						$tel["mobile"] = $mobile;
				}
			}
			$set = array($fieldName => $tel);
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
				if(empty($fieldValue["geo"]))
					$set["geo"] = SIG::getGeoPositionByInseeCode($insee, $postalCode,$cityName);
			} else 
				throw new CTKException("Error updating  : address is not well formated !");			
		}

		//update 
		PHDB::update( $collection, array("_id" => new MongoId($id)), 
		                          array($verb => $set));
		return true;
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
	    
	    //echo $testUrl;
	    if(file_exists($testUrl)) return $url;
	    else return $assetUrl.'/images/thumbnail-default.jpg';
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
		$contextMap["organization"] = array();
		$contextMap["people"] = array();
		$contextMap["organizations"] = array();
		$contextMap["projects"] = array();
		$contextMap["events"] = array();
		$contextMap["followers"] = array();
		if(!empty($links)){
			if(isset($links[$connectAs])){
				foreach ($links[$connectAs] as $key => $aMember) {
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
			if(isset($links["membersOf"])){
				foreach ($links["membersOf"] as $key => $value) {
					$newOrga = Organization::getSimpleOrganizationById($key);
					if (!empty($newOrga))
					array_push($contextMap["membersOf"], $newOrga);
				}
			}


			// Link with needs
			/*if(isset($organization["links"]["needs"])){
				foreach ($organization["links"]["needs"] as $key => $value){
					$need = Need::getSimpleNeedById($key);
					//array_push($contextMap["projects"], $project);
				}
			}*/
		}
		return $contextMap;	
    }
}