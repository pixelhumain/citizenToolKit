<?php 
class Person {
	public $jsonLD= array();
	const COLLECTION = "citoyens";
	const CONTROLLER = "person";
	const ICON = "fa-user";
	const COLOR = "#F5E740";

	const REGISTER_MODE_MINIMAL	 	= "minimal";
	const REGISTER_MODE_NORMAL 		= "normal";
	const REGISTER_MODE_TWO_STEPS 	= "two_steps_register";


	//From Post/Form name to database field name with rules
	public static $dataBinding = array(
	    "name" => array("name" => "name", "rules" => array("required")),
	    "slug" => array("name" => "slug", "rules" => array("checkSlug")),
	    "username" => array("name" => "username", "rules" => array("required", "checkUsername")),
	    "birthDate" => array("name" => "birthDate", "rules" => array("required")),
	    "email" => array("name" => "email", "rules" => array("email")),
	    "pwd" => array("name" => "pwd"),
	    "address" => array("name" => "address", "rules" => array("addressValid")),
	    "streetAddress" => array("name" => "address.streetAddress"),
	    "postalCode" => array("name" => "address.postalCode"),
	    "codeInsee" => array("name" => "address.codeInsee"),
	    "addressLocality" => array("name" => "address.addressLocality"), 
	    "addressCountry" => array("name" => "address.addressCountry"),
	    "geo" => array("name" => "geo", "rules" => array("geoValid")),
	    "geoPosition" => array("name" => "geoPosition", "rules" => array("geoPositionValid")),
	    "telephone" => array("name" => "telephone"),
	    "mobile" => array("name" => "telephone.mobile"),
	    "fixe" => array("name" => "telephone.fixe"),
	    "fax" => array("name" => "telephone.fax"),
	    "tags" => array("name" => "tags"),
	    "shortDescription" => array("name" => "shortDescription"),
	    "description" => array("name" => "description"),
	    "facebook" => array("name" => "socialNetwork.facebook"),
	    "twitter" => array("name" => "socialNetwork.twitter"),
	    "gpplus" => array("name" => "socialNetwork.googleplus"),
	    "github" => array("name" => "socialNetwork.github"),
	    "skype" => array("name" => "socialNetwork.skype"),
	    "telegram" => array("name" => "socialNetwork.telegram"),
	    "instagram" => array("name" => "socialNetwork.instagram"),
	    "diaspora" => array("name" => "socialNetwork.diaspora"),
	    "mastodon" => array("name" => "socialNetwork.mastodon"),
	    "bgClass" => array("name" => "preferences.bgClass"),
	    "bgUrl" => array("name" => "preferences.bgUrl"),
	    "roles" => array("name" => "roles"),
	    "two_steps_register" => array("name" => "two_steps_register"),
	    "source" => array("name" => "source"),
	    "warnings" => array("name" => "warnings"),
	    "isOpenData" => array("name" => "isOpenData"),
	    "modules" => array("name" => "modules"),
	    "badges" => array("name" => "badges"),
	    "multitags" => array("name" => "multitags"),
	    "multiscopes" => array("name" => "multiscopes"),
	    "url" => array("name" => "url"),
	    "urls" => array("name" => "urls"),
	    "lastLoginDate" => array("name" => "lastLoginDate"),
	    "seePreferences" => array("name" => "seePreferences"),
	    "locality" => array("name" => "address"),
	    "language" => array("name" => "language"),
	    "curiculum" => array("name" => "curiculum"),
	    "modified" => array("name" => "modified"),
	    "updated" => array("name" => "updated"),
	    "creator" => array("name" => "creator"),
	    "created" => array("name" => "created"),
	    "descriptionHTML" => array("name" => "descriptionHTML"),
	    "onepageEdition" => array("name" => "onepageEdition"),
	);

	public static function logguedAndValid() {
    	if (isset(Yii::app()->session["userId"])) {
	    	$user = PHDB::findOneById( self::COLLECTION ,Yii::app()->session["userId"]);
	    	
	    	$valid = Role::canUserLogin($user, Yii::app()->session["isRegisterProcess"]);
	    	$isLogguedAndValid = (isset( Yii::app()->session["userId"]) && $valid["result"]);
    	} else {
    		$isLogguedAndValid = false;
    	}
    	return $isLogguedAndValid;
    }
	/**
	 * used to save any user session data 
	 * good practise shouldn't be to heavy
	 * user = array("name"=>$username)
	 * $isRegisterProcess => save in the session if it's the first time a he is connected
	 */
	public static function saveUserSessionData($account, $isRegisterProcess = false, $pwd=null) {
	  	Yii::app()->session["userId"] = (string)$account["_id"];
	  	Yii::app()->session["userEmail"] = $account["email"];
	  	Yii::app()->session["pwd"] = $pwd;

	  	$name = (isset($account["name"])) ? $account["name"] : "Anonymous" ;
	    $user = array("name"=>$name);
		
		if(	@$account["username"] ) 
	      	$user ["username"] = $account["username"];
	    if(@$account["slug"] ) 
	      	$user ["slug"] = $account["slug"];
	    if( @$account["cp"] ) 
	      	$user ["postalCode"] = $account["cp"];
	    if( @$account["address"]) {
	    	if ( @$account["address"]["postalCode"] ) $user ["postalCode"] = $account["address"]["postalCode"];
	    	if ( @$account["address"]["codeInsee"] ) $user ["codeInsee"] = $account["address"]["codeInsee"];
	    	if ( @$account["address"]["addressCountry"] ) $user ["addressCountry"] = $account["address"]["addressCountry"];
	    }
		if( @$account["roles"])
	     	$user ["roles"] = $account["roles"];
	    if( @$account["preferences"])
	     	$user ["preferences"] = $account["preferences"];
	    
	    //Last login date
	    $user ["lastLoginDate"] = @$account["lastLoginDate"] ? $account["lastLoginDate"] : time();
	    
		//Image profil
	    $simpleUser = self::getById((string)$account["_id"]);
	    $user ["profilImageUrl"] = $simpleUser["profilImageUrl"];
	    $user ["profilThumbImageUrl"] = $simpleUser["profilThumbImageUrl"];
	    $user ["profilMarkerImageUrl"] = $simpleUser["profilMarkerImageUrl"];
	    Yii::app()->session["user"] = $user;
	    Yii::app()->session["isRegisterProcess"] = $isRegisterProcess;

        Yii::app()->session["userIsAdmin"] = Role::isUserSuperAdmin(@$account["roles"]);
        Yii::app()->session["userIsAdminPublic"] = Role::isSourceAdmin(@$account["roles"]);  

	    Yii::app()->session['logguedIntoApp'] = (isset(Yii::app()->controller->module->id)) ? Yii::app()->controller->module->id : "communecter";
    }

    /**
	 * used to clear all user's data from session
	 */
    public static function clearUserSessionData()
    {
      Yii::app()->session["userId"] = null;
      Yii::app()->session["userEmail"] = null; 
      Yii::app()->session["user"] = null; 
      Yii::app()->session['logguedIntoApp'] = null;
      Yii::app()->session['requestedUrl'] = null;
      Yii::app()->session["loginToken"] = null;
      Yii::app()->session["rocketUserId"] = null;
      CookieHelper::setCookie("communexionActivated", false);
    }

	/**
	 * get a Person By Id
	 * @param String $id : is the mongoId of the person
	 * @param boolean $clearAttribute : by default true. Will clear the confidential attributes
	 * @return type
	 */
	public static function getById($id, $clearAttribute = true) {
	  	$person = PHDB::findOneById( self::COLLECTION, $id );
	  	
	  	if (empty($person)) {
	  		$person = Element::getGhost(self::COLLECTION);
			//throw new CTKException("The element you are looking for has been moved or deleted");
	  		//TODO Sylvain - Find a way to manage inconsistente data
            //throw new CTKException("The person id ".$id." is unkown : contact your admin");
        } else {
			if (!empty($person["birthDate"])) {
				date_default_timezone_set('UTC');
				$person["birthDate"] = date('Y-m-d H:i:s', $person["birthDate"]->sec);
			}
			$person = array_merge($person, Document::retrieveAllImagesUrl($id, self::COLLECTION, null, $person));
			$person["typeSig"] = "people";
			$person["type"] = self::COLLECTION;
			if(!isset($person["address"])) 
				$person["address"] = array( "codeInsee" => "", 
											"postalCode" => "", 
											"addressLocality" => "",
											"streetAddress" => "",
											"addressCountry" => "");
        }
        
        if($clearAttribute) {
        	$person = self::clearAttributesByConfidentiality($person);
        }

        if(@$person["links"])
				$person["linkSize"] = count($person["links"], COUNT_RECURSIVE);

	  	return $person;
	}

	public static function getByArrayId($arrayId, $fields = array(), $clearAttribute = true, $simpleUser = false) { 
		
	  	//$person = PHDB::findOneById( self::COLLECTION, $id );
	  	$persons = PHDB::find(self::COLLECTION, array( "_id" => array('$in' => $arrayId)), $fields);
	  	$res = array();
	  	foreach ($persons as $id => $person) {
	  		if (empty($person)) {
		  		//TODO Sylvain - Find a way to manage inconsistente data
	            //throw new CTKException("The person id ".$id." is unkown : contact your admin");
	        } else {
				if (!empty($person["birthDate"])) {
					date_default_timezone_set('UTC');
					$person["birthDate"] = date('Y-m-d H:i:s', $person["birthDate"]->sec);
				}
				$person = array_merge($person, Document::retrieveAllImagesUrl($id, self::COLLECTION, null, $person));
				$person["typeSig"] = "people";
				if(!isset($person["address"])) 
					$person["address"] = array( "codeInsee" => "", 
												"postalCode" => "", 
												"addressLocality" => "",
												"streetAddress" => "",
												"addressCountry" => "");
	        }
	        if($simpleUser) {
	        	$person = self::getSimpleUserById($id,$person);
	        }else if($clearAttribute) {
	        	$person = self::clearAttributesByConfidentiality($person);
	        }
	        $res[$id] = $person;
	  	}
	  
	  	return $res;
	}


	/**
	 * Retrieve a simple user (id, name, profilImageUrl) by id from DB
	 * @param String $id of the person
	 * @return array with data id, name, profilImageUrl
	 */
	public static function getMinimalUserById($id,$person=null) {
		
		$simplePerson = array();
		if(!$person)
			$person = PHDB::findOneById( self::COLLECTION ,$id, 
				array("id" => 1, "name" => 1, "username" => 1, "email" => 1, "roles" => 1, "tags" => 1, "profilImageUrl" => 1, "profilThumbImageUrl" => 1, "profilMarkerImageUrl" => 1));
		
		if (empty($person)) {
			return $simplePerson;
		}

		$simplePerson["id"] = $id;
		$simplePerson["name"] = @$person["name"];
		$simplePerson["username"] = @$person["username"];
		$simplePerson["email"] = @$person["email"];
		$simplePerson["tags"] = @$person["tags"];
		
		//images
		$simplePerson = array_merge($simplePerson, Document::retrieveAllImagesUrl($id, self::COLLECTION, null, $person));

		$simplePerson["address"] = empty($person["address"]) ? array("addressLocality" => "Unknown") : $person["address"];
		
		$simplePerson = self::clearAttributesByConfidentiality($simplePerson);

		$simplePerson["typeSig"] = "people";
	  	return $simplePerson;

	}

	/**
	 * Retrieve a simple user (id, name, profilImageUrl) by id from DB
	 * @param String $id of the person
	 * @return array with data id, name, profilImageUrl
	 */
	public static function getSimpleUserById($id,$person=null) {
		
		$simplePerson = array();
		if(!$person)
			$person = PHDB::findOneById( self::COLLECTION ,$id, 
				array("id" => 1, "name" => 1, "username" => 1, "email" => 1,  "shortDescription" => 1, "description" => 1, "address" => 1, "geo" => 1, "roles" => 1, "tags" => 1, "links" => 1, "pending" => 1, "profilImageUrl" => 1, "profilThumbImageUrl" => 1, "profilMarkerImageUrl" => 1, "profilMediumImageUrl" => 1,"numberOfInvit" => 1,"updated" => 1,"addresses" => 1, "slug" => 1));
		
		if (empty($person)) {
			return $simplePerson;
		}

		$simplePerson["id"] = $id;
		$simplePerson["name"] = @$person["name"];
		if(@$person["username"])
			$simplePerson["username"] = @$person["username"];
		if(@$person["email"])
			$simplePerson["email"] = @$person["email"];
		if(@$person["tags"])
			$simplePerson["tags"] = @$person["tags"];
		if(@$person["links"])
			$simplePerson["links"] = @$person["links"];
		if(@$person["roles"]["tobeactivated"])
			$simplePerson["tobeactivated"] = @$person["roles"]["tobeactivated"];
		if(@$person["shortDescription"])
			$simplePerson["shortDescription"] = @$person["shortDescription"];
		if(@$person["description"])
			$simplePerson["description"] = @$person["description"];
		if(@$person["pending"])
			$simplePerson["pending"] = @$person["pending"];
		if(@$person["updated"])
			$simplePerson["updated"] = @$person["updated"];
		if(@$person["slug"])
			$simplePerson["slug"] = @$person["slug"];

		
		if(@$person["links"] && @$person["links"]["follows"] && @$person["links"]["followers"])
		$simplePerson["counts"] = array("follows"=>count($person["links"]["follows"]),
										"followers"=>count($person["links"]["followers"]));
		
		//Ajouter par rapport au getAllLink
		$simplePerson["address"] = @$person["address"];
		$simplePerson["geo"] = @$person["geo"];
		
		$simplePerson["typeSig"] = "people";
	  	
		if (@Yii::app()->params['betaTest']) { 
			$simplePerson["numberOfInvit"] = @$person["numberOfInvit"];
		}
		//images
		$simplePerson = array_merge($simplePerson, Document::retrieveAllImagesUrl($id, self::COLLECTION, null, $person));
		$simplePerson = self::clearAttributesByConfidentiality($simplePerson);
	  	return $simplePerson;

	}

	//TODO SBAR => should be private ?
	public static function getWhere($params) {
	  	 return PHDB::findAndSort( self::COLLECTION,$params,array("created"),null);
	}
	
	//TODO SBAR - To delete ?
	private static function setNameByid($name, $id) {
		PHDB::update(self::COLLECTION,
			array("_id" => new MongoId($id)),
            array('$set' => array("name"=> $name))
            );
	}

	/**
	 * get all organizations details of a Person By a person Id
	 * @param type $id : is the mongoId (String) of the person
	 * @return person document as in db
	 */
	public static function getOrganizationsById($id){
		$person = self::getById($id);
	    //$person["tags"] = Tags::filterAndSaveNewTags($person["tags"]);
	    $organizations = array();
	    
	    //Load organizations
	    if (isset($person["links"]) && !empty($person["links"]["memberOf"])) 
	    {
	      foreach ($person["links"]["memberOf"] as $id => $e) 
	      {
	        $organization = PHDB::findOne( Organization::COLLECTION, array( "_id" => new MongoId($id)));
	        if (!empty($organization) && !isset($organisation["disabled"])) {
	          array_push($organizations, $organization);
	        } else {
	         // throw new CTKException("Données inconsistentes pour le citoyen : ".Yii::app()->session["userId"]);
	        }
	      }
	    }
	    return $organizations;
	}
	/**
	 * get memberOf a Person By a person Id
	 * @param type $id : is the mongoId (String) of the person
	 * @return person document as in db
	 */
	public static function getPersonMemberOfByPersonId($id) {
	  	$res = array();
	  	$person = self::getById($id);
	  	
	  	if (empty($person)) {
            throw new CTKException("The person id is unkown : contact your admin");
        }
	  	if (isset($person) && isset($person["links"]) && isset($person["links"]["memberOf"])) {
	  		$res = $person["links"]["memberOf"];
	  	}

	  	return $res;
	}

	/** NE serait-ce pas à supprimmer? appeler nul part
	 * get Knows a Person By a person Id
	 * @param type $id : is the mongoId (String) of the person
	 * @return person document as in db
	 */
	public static function getPersonKnowsByPersonId($id) {
	  	$res = array();
	  	$person = self::getById($id);
	  	
	  	if (empty($person)) {
            throw new CTKException("The person id is unkown : contact your admin");
        }
        $myContacts = array();
	  	if (isset($person) && isset($person["links"]) && isset($person["links"]["knows"])) {
	  		$myContacts = $person["links"]["knows"];
	  	}
	  	foreach ($myContacts as $key => $contact) {
			if($contact["type"] == "citoyens"){
				$contactComplet = self::getById($key);
				$myContacts[$key] = $contactComplet;
			}
			//var_dump($contactComplet);
		}
		$res = $myContacts;
	  	return $res;
	}

	
	/** De même !!
	 * get all links By a person Id
	 * @param type $id : is the mongoId (String) of the person
	 * @return person document as in db
	 */
	public static function getPersonLinksByPersonId($id) {
	  	$res = array(Person::COLLECTION => array(), Organization::COLLECTION => array(), 
	  				 Project::COLLECTION => array(), Event::COLLECTION => array());

	  	$person = Element::getElementSimpleById($id, Person::COLLECTION, null, array("links"));

	  	//error_log($id);
	  	if (empty($person)) {
            throw new CTKException("The person id is unkown : contact your admin");
        }
        
        $myContacts = array();
	  	if (isset($person) && isset($person["links"])) {
	  		$myContacts = $person["links"];
	  	}

	  	/*$valIDLink = array();
	  	foreach (array("follows", "memberOf", "projects", "events") as $n => $link) {
	  		if( isset($myContacts[$link])){
			  	foreach ($myContacts[$link] as $key => $contact) {
			  		$type = isset($contact["type"]) ? $contact["type"] : "";
			  		$valIDLink[$type][] = new MongoId($key) ;
				}
			}
		}*/
		$infos=["id", "name", "slug", "username", "shortDescription", "address", "type", "profilThumbImageUrl", "profilImageUrl", "profilMediumImageUrl","preferences", "hasRC", "endDate"];
		foreach($myContacts as $connectKey => $links){
			if(in_array($connectKey,["follows", "memberOf", "projects", "events"])){
				foreach($links as $key => $value){
					//Condition on double link in my contact (ex: user follow an association and wait for validation to become member)
					if(@$res[$type][$key]){
						if($connectKey=="follows") $res[$type][$key]["isFollowed"]=true;
						foreach($value as $label => $v){
							if($label != "type")
								$$res[$type][$key][$label]=$v;
						}
					}else{
						$contactComplet = Element::getElementSimpleById($key, $value["type"], null, $infos);
						if(!empty($contactComplet) && !@$contactComplet["disabled"]){
							if($connectKey=="follows") $contactComplet["isFollowed"]=true;
							foreach($value as $label => $v){
								if($label != "type")
									$contactComplet[$label]=$v;
							}
							
							if(@$contactComplet["endDate"]){
								date_default_timezone_set('UTC');
								$contactComplet["endDate"] = date(DateTime::ISO8601, $contactComplet["endDate"]->sec);
							}
							/*if(@$value[Link::IS_ADMIN]) $contactComplet[Link::IS_ADMIN]=true;
							if(@$value[Link::IS_ADMIN_PENDING]) $contactComplet[Link::IS_ADMIN_PENDING]=true;
							if(@$value[Link::IS_ADMIN_INVITING]) $contactComplet[Link::IS_ADMIN_INVITING]=true;
							if(@$value[Link::TO_BE_VALIDATED]) $contactComplet[Link::TO_BE_VALIDATED]=true;
							if(@$value[Link::IS_INVITING]) $contactComplet[Link::IS_INVITING]=true;
							if(@$value["roles"]) $contactComplet["roles"]=true;
							if(@$value["notifications"]) $contactComplet["notifications"]=$value["notifications"];
							if(@$value["mails"]) $contactComplet["mails"]=$value["mails"];*/
							$type= $value["type"];
							//if($type=="organizations" &&)
							if(in_array($type, ["citoyens", "projects","events","organizations"]))
								$res[$type][$key] = $contactComplet;
						}
					}
				}
			}
		}
		/*if( !empty($valIDLink) ) {
			foreach ($valIDLink as $type => $valLink) {
				$contactsComplet = null;
				if($type == self::COLLECTION){
					$contactsComplet = self::getByArrayId($valLink); 
					$type = "people"; 
				}

				if($type == Organization::COLLECTION){
					$contactsComplet = Organization::getByArrayId($valLink);
				}

				if($type == Project::COLLECTION) 	{ $contactsComplet = Project::getByArrayId($valLink); }
				if($type == Event::COLLECTION)		{ $contactsComplet = Event::getByArrayId($valLink); }

				if($contactsComplet != null)	$res[$type] = $contactsComplet;
			}
		}*/


	 //  	foreach (array("follows", "memberOf", "projects", "events") as $n => $link) {

	 //  		if( isset($myContacts[$link]))
	 //  		{
		// 	  	foreach ($myContacts[$link] as $key => $contact) {
			  		
		// 	  		$type = isset($contact["type"]) ? $contact["type"] : "";
		// 	  		$contactComplet = null;
		// 			if($type == "citoyens")		{ 
		// 				$contactComplet = self::getById($key); 
		// 				$type = "people"; 
		// 			}
		// 			if($type == "organizations"){ 
		// 				$contactComplet = Organization::getById($key);
		// 				//Do not add orga disabled
		// 				if (@$contactComplet["disabled"]) {
		// 					$contactComplet = null;
		// 				}
		// 			}
		// 			if($type == "projects")		{ $contactComplet = Project::getById($key); }
		// 			if($type == "events")		{ $contactComplet = Event::getById($key); }
					
		// 			if($contactComplet != null)	$res[$type][$key] = $contactComplet;
					
					
		// 		}
		// 	}
		// }
		//print_r($res);
		

	  	if(isset($res["citoyens"])) 	 	 $res["citoyens"]=self::sortContact($res["citoyens"], array("name"=>SORT_ASC));
	  	if(isset($res["organizations"])) $res["organizations"]=self::sortContact($res["organizations"],array("name"=>SORT_ASC));
	  	if(isset($res["projects"])) 	 $res["projects"]=self::sortContact($res["projects"],array("name"=>SORT_ASC));
	  	if(isset($res["events"])) 		 $res["events"]=self::sortContact($res["events"],array("name"=>SORT_ASC));
		if($id != Yii::app()->session["userId"])
			$res["citoyens"][(string)$id] = $person;
		
		return $res;
	}
	/**
	* Get array of news order by date of creation
	* @param array $array is the array of news to return well order
	* @param array $cols is the array indicated on which column of $array it is sorted
	**/

	public static function sortContact($array, $cols){
		$colarr = array();
	    foreach ($cols as $col => $order) {
	        $colarr[$col] = array();
	        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower(@$row[$col]); }
	    }
	    $eval = 'array_multisort(';
	    foreach ($cols as $col => $order) {
	        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
	    }
	    $eval = substr($eval,0,-1).');';
	    eval($eval);
	    $ret = array();
	    foreach ($colarr as $col => $arr) {
	        foreach ($arr as $k => $v) {
	            $k = substr($k,1);
	            if (!isset($ret[$k])) $ret[$k] = $array[$k];
	            $ret[$k][$col] = @$array[$k][$col];
	        }
	    }
	    return $ret;
	}

	public static function newPersonFromPost($person) {
		$newPerson = array();
		
		//Location
		if (isset($person['streetAddress'])) $newPerson["address"]["streetAddress"] = rtrim($person['streetAddress']);
		if (isset($person['postalCode'])) $newPerson["address"]["postalCode"] = $person['postalCode'];
		if (isset($person['addressLocality'])) $newPerson["address"]["addressLocality"] = $person['addressLocality'];
		if (isset($person['addressCountry'])) $newPerson["address"]["addressCountry"] = $person['addressCountry'];
		if (isset($person['codeInsee'])) $newPerson["address"]["codeInsee"] = $person['codeInsee'];

		if (isset($person['two_steps_register'])) $newPerson["two_steps_register"] = $person['two_steps_register'];

		if (isset($person['description'])) $newPerson["description"] = rtrim($person['description']);
		if (isset($person['role'])) $newPerson["role"] = $person['role'];

		//error_log("latitude : ".$person['geoPosLatitude']);
		if(!empty($person['geoPosLatitude']) && !empty($person["geoPosLongitude"])){
			$newPerson["geo"] = array("@type"=>"GeoCoordinates",
											"latitude" => $person['geoPosLatitude'],
											"longitude" => $person['geoPosLongitude']);

			$newPerson["geoPosition"] = array("type"=>"Point",
															"coordinates" =>
																array(
																	floatval($person['geoPosLongitude']),
																	floatval($person['geoPosLatitude']))
			);
		}
		
		return $newPerson;
	}

	/**
	 * Happens when a Person is invited or linked as a member and doesn't exist in the system
	 * It is created in a temporary state
	 * This creates and invites the email to fill extra information 
	 * into the Person profile 
	 * the email will contain the message
	 * @param ARRAY $param  
	 * @param STRING $msg Message that will be sent by mail to user invited
	 * @return type
	 */
	public static function createAndInvite($param, $msg = null, $gmail =null) {
	  	try {
	  		//var_dump("Person::createAndInvite");
	  		//Check if the person can still invite : has he got enought invitations left
	  		$invitor = self::getById($param["invitedBy"]);
	  		$res = self::insert($param, self::REGISTER_MODE_MINIMAL);
	  		//send invitation mail
			//Mail::invitePerson($res["person"], $msg);
		  		  		
	  	} catch (CTKException $e) {
	  		$res = array("result"=>false, "msg"=> $e->getMessage());
	  	}
        return $res;
	}

	/**
	 * Apply person checks and business rules before inserting
	 * Throws CTKException on error
	 * @param array $person : array with the data of the person to check
	 * @param string $mode : insert mode type. 
	 * REGISTER_MODE_MINIMAL : a person can be created using only name and email. 
	 * REGISTER_MODE_NORMAL : name, email, username, password, postalCode, city
	 * REGISTER_MODE_TWO_STEPS : name, username, email, password
	 * @param boolean $uniqueEmail : true : check if a person already exist in the db with that email
	 * @return the new person with the business rules applied
	 */
	public static function getAndcheckPersonData($person, $mode, $uniqueEmail = true) {
		$dataPersonMinimal = array("name", "email");
		
		$newPerson = array();
		if ($mode == self::REGISTER_MODE_MINIMAL) {
			//generate unique temporary userName for Meteor app when inviting
			$newPerson["username"] = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 32);
			//Add pending boolean
			$newPerson["pending"] = true;
		} else if ($mode == self::REGISTER_MODE_NORMAL) {
			array_push($dataPersonMinimal, "username", "postalCode", "city", "pwd");
		} else if ($mode == self::REGISTER_MODE_TWO_STEPS) {
			array_push($dataPersonMinimal, "username", "pwd");
			$newPerson[self::REGISTER_MODE_TWO_STEPS] = true;
		}

		//Check the minimal data
	  	foreach ($dataPersonMinimal as $data) {
	  		if ( empty( $person["$data"] ) )
	  			throw new CTKException(Yii::t("person","Problem inserting the new person : ").$data.Yii::t("person"," is missing"));
	  	}
	  	
	  	$newPerson["name"] = trim($person["name"]);

	  	//Check email
	  	$checkEmail = DataValidator::email($person["email"]);
	  	if ($checkEmail != "") {
	  		throw new CTKException(Yii::t("common",$checkEmail));
	  	}

		//Check if the email of the person is already in the database
	  	if ($uniqueEmail) {
		  	$account = PHDB::findOne(self::COLLECTION,array("email"=> new MongoRegex('/^' . preg_quote(trim($person["email"])) . '$/i')));
		  	if ($account) {
		  		throw new CTKException(Yii::t("person","Problem inserting the new person : a person with this email already exists in the plateform"));
		  	}
	  	}
	  	$newPerson["email"] = trim($person["email"]);
	  	
	  	if (!empty($person["invitedBy"])) {
	  		$newPerson["invitedBy"] = $person["invitedBy"];
	  	}

	  	if (!empty($person["preferences"])) {
	  		$newPerson["preferences"] = $person["preferences"];
	  	}

	  	if ($mode == self::REGISTER_MODE_NORMAL || $mode == self::REGISTER_MODE_TWO_STEPS) {
		  	//user name
		  	$newPerson["username"] = trim($person["username"]);
		  	if (strlen($newPerson["username"]) < 4 || strlen($newPerson["username"]) > 32) {
		  		throw new CTKException(Yii::t("person","The username length should be between 4 and 32 characters"));
		  	} 
		  	if ( ! self::isUniqueUsername($newPerson["username"]) ) {
		  		throw new CTKException(Yii::t("person","Problem inserting the new person : a person with this username already exists in the plateform"));
		  	}

		  	//Password is mandatory : it will be encoded later
		  	if (empty($person["pwd"])) 
		  		throw new CTKException(Yii::t("person","The password could not be empty on this register mode !"));
		}

		if ($mode == self::REGISTER_MODE_NORMAL) {
		  	//Manage the adress : postalCode / adressLocality / codeInsee
		  	//Get Locality label
		  	try {
		  		//Format adress 
		  		//$newPerson["address"] = SIG::getAdressSchemaLikeByCodeInsee($person["city"]);
		  		$newPerson["address"] = SIG::getAdressSchemaLikeByCodeInsee($person["city"],$person["postalCode"]);

				if(!empty($person['geoPosLatitude']) && !empty($person["geoPosLongitude"])){
					$newPerson["geo"] = array("@type"=>"GeoCoordinates",
													"latitude" => $person['geoPosLatitude'],
													"longitude" => $person['geoPosLongitude']);

					$newPerson["geoPosition"] = array("type"=>"Point",
															"coordinates" =>
															array(
																floatval($person['geoPosLongitude']),
																floatval($person['geoPosLatitude']))
													 	  	);
					//$newPerson["geo"] = empty($organization['public']) ? "" : $organization['public'];
				}
				$newPerson["geo"] = !empty($newPerson["geo"]) ? $newPerson["geo"] : SIG::getGeoPositionByInseeCode($person["city"]);
		  	} catch (CTKException $e) {
		  		throw new CTKException(Yii::t("person","Problem inserting the new person : unknown city"));
		  	}
		}
	  	return $newPerson;
	}

	/**
	 * Insert a new person from the minimal information inside the parameter
	 * @param array $person Minimal information to create a person.
	 * @param string $mode : insert mode type. 
	 * REGISTER_MODE_MINIMAL : a person can be created using only name and email. 
	 * REGISTER_MODE_NORMAL : name, email, username, password, postalCode, city
	 * REGISTER_MODE_TWO_STEPS : name, username, email, password
	 * @param string $inviteCode : invitation code
	 * @return array result : msg and id
	 */
	public static function insert($person, $mode = self::REGISTER_MODE_NORMAL, $inviteCode = null) {
		//Keep the password
		$pwd = @$person["pwd"];

	  	//Check Person data + business rules
	  	$person = self::getAndcheckPersonData($person, $mode);
	  	
	  	$person["@context"] = array("@vocab"=>"http://schema.org",
            "ph"=>"http://pixelhumain.com/ph/ontology/");

	  	$person["roles"] = Role::getDefaultRoles();

	  	self::addPersonDataForBetaTest($person, $mode, $inviteCode);
	  	
	  	$person["created"] = new mongoDate(time());
	  	//$person["preferences"] = array("seeExplanations"=> true);
	  	$person["preferences"] = Preference::initPreferences(self::COLLECTION);
	  	$person["seePreferences"] = true;
	  	$person["slug"]=Slug::checkAndCreateSlug($person["username"]);
	  	PHDB::insert(self::COLLECTION , $person);
        if (isset($person["_id"])) {
        	$newpersonId = (String) $person["_id"];
        	if (! empty($pwd)) {
	        	//Encode the password
			  	$encodedpwd = self::hashPassword($newpersonId, $pwd);
			  	self::updatePersonField($newpersonId, "pwd", $encodedpwd, $newpersonId);
			} 
	        Slug::save(Person::COLLECTION,(string)$person["_id"],$person["slug"]);
	    } else {
	    	throw new CTKException("Problem inserting the new person");
	    }

		//A mail is sent to the admin
		Mail::notifAdminNewUser($person);
		$res = array("result"=>true, "msg"=>"You are now communnected", "id"=>$newpersonId, "person"=>$person);

		if(!empty($person["invitedBy"]))
			$res['invitedBy'] = $person["invitedBy"];
	    return $res;
	}

	/**
	 * In betaTest mode, manage roles, invitation code, and invitation numbers on person data
	 * @param array $person A person ready to insert
	 * @param String $mode Register mode
	 * @param String $inviteCode Invitation code
	 * @return void
	 */
	private static function addPersonDataForBetaTest($person, $mode, $inviteCode) {
		//if we are in mode minimal it's an invitation. The invited user is then betaTester by default
	  	if( @Yii::app()->params['betaTest'] && $mode ==self::REGISTER_MODE_MINIMAL) {
	  		$person["roles"]['betaTester'] = true;
	  	}

	  	//if valid invite code , user is automatically beta tester
	  	//inviteCodes are server configs 
	  	if( @Yii::app()->params['betaTest'] && $inviteCode && in_array( $inviteCode , Yii::app()->params['validInviteCodes'] )) {
	  		$person["roles"]['betaTester'] = true;
	  		$person["inviteCode"] = $inviteCode;
	  	}
		if (@Yii::app()->params['betaTest'] || @$person["roles"]["betaTester"]==true)
	  		$person["numberOfInvit"] = empty(Yii::app()->params['numberOfInvitByPerson']) ? 0 : Yii::app()->params['numberOfInvitByPerson'];
	  	return;
	}

	/**
	 * Get a person from an id and return filter data in order to return only public data
	 * @param type $id 
	 * @return person
	 */
	public static function getPublicData($id) {
		//Public datas
		$publicData = array (
			"imagePath",
			"name",
			"city",
			"socialAccounts",
			"positions",
			"url",
			"coi"
		);
		
		//TODO SBAR = filter data to retrieve only publi data	
		$person = self::getById($id, true);
		if (empty($person)) {
			//throw new CTKException("The person id is unknown ! Check your URL");
		}

		return $person;
	}

	/**
	 * answers to show or not to show a field by it's name
	 * @param String $id : is the mongoId of the action room
	 * @param String $person : is the mongoId of the action room
	 * @return "" or the value to be shown
	 */
	public static function showField($fieldName,$person, $isLinked) {
	  	
	  	$res = null;

	  	$attConfName = $fieldName;
	  	if($fieldName == "address.streetAddress") 	$attConfName = "locality";
	  	if($fieldName == "telephone") 				$attConfName =  "phone";

	  	if( Yii::app()->session['userId'] == (string)$person["_id"]
	  		||  ( isset($person["preferences"]) && isset($person["preferences"]["publicFields"]) && in_array( $attConfName, $person["preferences"]["publicFields"]) )  
	  		|| ( $isLinked && isset($person["preferences"]) && isset($person["preferences"]["privateFields"]) && in_array( $attConfName, $person["preferences"]["privateFields"]))  )
	  	{
	  		$res = ArrayHelper::getValueByDotPath($person,$fieldName);
	  	
	  	}
	  	
	  	return $res;
	}

 	/**
		 * get all events details of a Person By a person Id
		 * @param type $id : is the mongoId (String) of the person
		 * @return person document as in db
	*/
	public static function getEventsByPersonId($id){
		$person = self::getById($id);
	    $events = array();
	    
	    //Load events
	    if (isset($person["links"]) && !empty($person["links"]["events"])) 
	    {
	      foreach ($person["links"]["events"] as $id => $e) 
	      {
	        $event = PHDB::findOne( PHType::TYPE_EVENTS, array( "_id" => new MongoId($id)));
	        if (!empty($event)) {
	          array_push($events, $event);
	        } else {
	         // throw new CTKException("Données inconsistentes pour le citoyen : ".Yii::app()->session["userId"]);
	        }
	      }
	    }
	    return $events;
	} 

	/**
	* get person Data => need to update
	* @param type $id : is the mongoId (String) of the person
	* @return a map with : Person's informations, his organizations, events,projects
	*/
	public static function getPersonMap($id){
		$person = self::getById($id);
		$organizations = self::getOrganizationsById($id);
		$events = self::getEventsByPersonId($id);
		$personMap = array(
							"person" => $person,
							"organizations" => $organizations,
							"events" => $events
						);
		return $personMap;
	}

	/**
	 * Update a person field value
	 * @param String $personId The person Id to update
	 * @param String $personFieldName The name of the field to update
	 * @param String $personFieldValue 
	 * @param String $userId 
	 * @return boolean True if the update has been done correctly. Can throw CTKException on error.
	 */
	public static function updatePersonField($personId, $personFieldName, $personFieldValue, $userId) {  
		//var_dump(Role::isSuperAdmin(Role::getRolesUserId($userId)) == true);
		//if ($personId != $userId){
		if (!Authorisation::canEditItem($userId, self::COLLECTION, $personId)) {
			throw new CTKException("Can not update the person : you are not authorized to update that person !");
		}		
		
		if(is_string($personFieldValue))
		$personFieldValue = trim($personFieldValue);
		$dataFieldName = self::getCollectionFieldNameAndValidate($personFieldName, $personFieldValue);
		//Specific case : 
		//Tags
		if ($dataFieldName == "tags") 
			$personFieldValue = Tags::filterAndSaveNewTags($personFieldValue);
		
	
		if($dataFieldName == "email" && (empty($personFieldValue) || strlen($personFieldValue) == 0)){
			throw new CTKException("L'email ".Yii::t("person", "is missing"));
		}

		if ( ($personFieldName == "mobile"|| $personFieldName == "fixe" || $personFieldName == "fax")){
			if($personFieldValue ==null)
				$personFieldValue = array();
			else
				$personFieldValue = explode(",", $personFieldValue);
		}

		//address
		$user = null;
		$thisUser = self::getById($personId);

		if ($dataFieldName == "address") 
		{
			if(!empty($personFieldValue["postalCode"]) && !empty($personFieldValue["codeInsee"])) 
			{
				$user = Yii::app()->session["user"];
				$insee = $personFieldValue["codeInsee"];
				$user["codeInsee"] = $insee;
				$postalCode = $personFieldValue["postalCode"];
				$user["postalCode"] = $postalCode;

				$address = SIG::getAdressSchemaLikeByCodeInsee($insee,$postalCode);
				if (!empty($personFieldValue["streetAddress"])) 
					$address["streetAddress"] = $personFieldValue["streetAddress"];
				if (!empty($personFieldValue["addressCountry"])) {
					$address["addressCountry"] = $personFieldValue["addressCountry"];
					$user["addressCountry"] = $personFieldValue["addressCountry"];
				}
				Yii::app()->session["user"] = $user;
				$set = array("address" => $address);

				if(empty($thisUser["geo"])){
					$geo = SIG::getGeoPositionByInseeCode($insee,$postalCode);	
					SIG::updateEntityGeoposition(self::COLLECTION,$personId,$geo["latitude"],$geo["longitude"]);
				}

				$user["address"] = $address;
				//if($userId == $personId)
				//	self::updateCookieCommunexion($userId, @$user["address"]);
				/*PHDB::update( self::COLLECTION, array("_id" => new MongoId($personId)), 
		                        array('$unset' => array("two_steps_register"=>"")));*/

			} else 
				throw new CTKException("Error updating the Person : address is not well formated !");			
		} 
		else if ($dataFieldName == "birthDate") 
		{
			date_default_timezone_set('UTC');
			$dt = DateTime::createFromFormat('Y-m-d H:i', $personFieldValue);
			if (empty($dt)) {
				$dt = DateTime::createFromFormat('Y-m-d', $personFieldValue);
			}
			$newMongoDate = new MongoDate($dt->getTimestamp());
			$set = array($dataFieldName => $newMongoDate);
		} 
		else if($dataFieldName == "socialNetwork.telegram") {
			//if(strpos($personFieldValue, "http")==false || strpos($personFieldValue, "http")>0) 
			//	if($personFieldValue != "")
			//		$personFieldValue = "https://web.telegram.org/#/im?p=@".$personFieldValue;

			$set = array($dataFieldName => $personFieldValue);
		}
		else {
			$set = array($dataFieldName => $personFieldValue);
			if ( $personFieldName == "bgClass") {
				//save to session for all page reuse
				$user = Yii::app()->session["user"];
				$user["bg"] = $personFieldValue;
				Yii::app()->session["user"] = $user;
			} else if ( $personFieldName == "bgUrl") {
				//save to session for all page reuse
				$user = Yii::app()->session["user"];
				$user["bgUrl"] = $personFieldValue;
				Yii::app()->session["user"] = $user;
			}  
		}

		//update the person
		PHDB::update( self::COLLECTION, array("_id" => new MongoId($personId)), 
		                          		array('$set' => $set));
	              
	    return array("result"=>true,"user"=>$user,"personFieldName"=>$personFieldName, "msg"=> Yii::t("person", "The person has been updated"));
	}

	//Test and Valide a field name using the data validator
	private static function getCollectionFieldNameAndValidate($personFieldName, $personFieldValue) {
		return DataValidator::getCollectionFieldNameAndValidate(self::$dataBinding, $personFieldName, $personFieldValue);
	}

    /**
     * Login with email and password. Check if the email and password match on db.
     * @param  [string] $email   email connected to the citizen account
     * @param  [string] $pwd   pwd connected to the citizen account
     * @param  [boolean] $isRegisterProcess Are we trying to login during the register process
     * @return [array] array of result as (result => boolean, msg => string)
     * msg possibles :  - accountPending => le user a été invité et il faut qu'il finisse son process de register
	 *					- betaTestNotOpen => si la plateforme est en mode Beta Test et que le user n'est pas beta tester
	 *					- notValidatedEmail => il a pas validé son email
	 * 					- emailNotFound => l'email n'a pas été trouvé en base de données
	 * 					- emailAndPassNotMatch => l'email et le password ne match pas
     */
    public static function login($emailOrUsername, $pwd, $isRegisterProcess) 
    {
        if (empty($emailOrUsername) || empty($pwd)) {
        	return array("result"=>false, "msg"=>"Cette requête ne peut aboutir. Merci de bien vouloir réessayer en complétant les champs nécessaires");
        }

        self::clearUserSessionData();
        $account = PHDB::findOne(self::COLLECTION, array( '$or' => array( 
        															array("email" => new MongoRegex('/^'.preg_quote(trim($emailOrUsername)).'$/i')),
        															array("username" => $emailOrUsername) ) ));
        
        //return an error when email does not exist
        if ($account == null) {
        	return array("result"=>false, "msg"=>"emailNotFound");
        }
        
        //Roles validation
        $res = Role::canUserLogin($account, $isRegisterProcess);
        if ($res["result"]) {
	        //Check the password
        	if ( self::checkPassword($pwd, $account) ) {
	            self::saveUserSessionData($account, $isRegisterProcess,$pwd);
	            //Update login history
	            self::updateLoginHistory((String) $account["_id"]);
	            if ($res["msg"] == "notValidatedEmail") 
	        		return $res;
	        	else{
	        		self::updateCookieCommunexion((string)$account["_id"], @$account["address"]);
	        		unset($account["pwd"]);
	            	$res = array(
	            		"result"=>true, 
	            		"id"=>(string)$account["_id"], 
	            		"isCommunected"=>isset($account["cp"]), 
	            		"account" => $account,
	            		"msg" => "Vous êtes maintenant identifié : bienvenue sur communecter.");
	            	//save login action for statistic
	            	CO2Stat::incNbLoad("co2-login");
	        	}
	        } else {
	            $res = array("result"=>false, "msg"=>"emailAndPassNotMatch");
	        }
	    }
        
        return $res;
    }

    /**
     * Update the last login date on person document
     * @param String $accountId an existing account id
     * @return boolean True if the update goes well, false else
     */
    public static function updateLoginHistory($accountId) {
    	return self::updatePersonField($accountId, "lastLoginDate", time(), $accountId);
    }

    /**
     * Check if the password is valid
     * /!\ Change the salt from email to id after login success /!\
     * @param String $pwd The password typed
     * @param array $account The account retrieve from 
     * @return boolean : true if password match
     */
    public static function checkPassword($pwd, $account) {
    	$res = false;

    	if ($account) {
    		if (@$account["pwd"] == hash('sha256', @$account["email"].$pwd)) {
    			//the password match with an "email" as salt => change the password to salt with the "id"
    			$newPassword = self::hashPassword((String) $account["_id"],$pwd);
				self::updatePersonField(@$account["_id"], "pwd", $newPassword, @$account["_id"]);
				//add a log on logs collection
				Log::save($logs =array(
					"userId" => $account["_id"],
					"browser" => @$_SERVER["HTTP_USER_AGENT"],
					"ipAddress" => @$_SERVER["REMOTE_ADDR"],
					"created" => new MongoDate(time()),
					"action" => "person/newsaltpassword"
			    ));
				$res = true;
    		//Second test : maybe the salt is already with the id
    		} else if (@$account["pwd"] == self::hashPassword((String) $account["_id"],$pwd)) {
    			$res = true;
    		}
    	}
    	return $res;
    }

	/**
	 * get actionRooms by personId
	 * @param type $id : is the mongoId (String) of the person
	 * @return person document as in db
	 */
	public static function getActionRoomsByPersonId($id,$archived=null) 
	{
		//get action Rooms I created
		$where = array( "email"=> Yii::app()->session['userEmail'] ) ;
		if(isset($archived))
			$where['status'] = ActionRoom::STATE_ARCHIVED;
		else 
			$where['status'] = array('$exists' => 0 );
	  	$actionRooms = PHDB::find(ActionRoom::COLLECTION,$where);//array();//ActionRoom::getWhereSortLimit( $where, array("created"=>1) ,1000);
	  	$actions = array();
	  	$person = self::getById($id);

	  	if (empty($person)) {
            throw new CTKException("The person id is unkown : contact your admin");
        }


	  	if ( isset($person) ) 
	  	{
	  		//get rooms connected to user communected city
	  		if( @$person["address"] && @$person["address"]["postalCode"] && @$person["address"]["codeInsee"] && @$person["address"]["addressCountry"] )
	  		{
	  			$myCityRooms = PHDB::find( ActionRoom::COLLECTION,
  										   array( 'parentType' => City::COLLECTION,
  												  'parentId' => $person["address"]["addressCountry"].'_'.$person["address"]["codeInsee"].'-'.$person["address"]["postalCode"] ) );
		  		foreach ( $myCityRooms as $roomId => $room) 
		  		{
		  			if( !isset( $actionRooms[ $roomId ] ) )
		  				$actionRooms[ $roomId ] = $room;
		  		}
		  	}
		  	//get rooms connected to all users actions
	  		if( @$person["actions"] && @$person["actions"]["surveys"] )
	  		{
		  		foreach ( $person["actions"]["surveys"] as $entryId => $action) 
		  		{
		  			$entry = Survey::getById( $entryId );
		  			$entry ['action'] = $action;
		  			$actions[ $entryId ] = $entry;

		  			if( isset( $entry['survey'] ) && !isset( $actionRooms[ $entry['survey'] ] ) )
		  			{
		  				$actionRoom = ActionRoom::getById( $entry['survey'] );
		  				$actionRooms[ $entry['survey'] ] = $actionRoom;
		  			}
		  		}
		  	}
		  	/*
		  	if(isset($person["actions"]["actions"]))
	  		{
		  		foreach ( $person["actions"]["actions"] as $entryId => $action) 
		  		{
		  			$entry = ActionRoom::getByActionId( $entryId );
		  			$entry ['action'] = $action;
		  			$actions[ $entryId ] = $entry;

		  			if( isset( $entry['room'] ) && !isset( $actionRooms[ $entry['room'] ] ) )
		  			{
		  				$actionRoom = ActionRoom::getById( $entry['room'] );
		  				$actionRooms[ $entry['room'] ] = $actionRoom;
		  			}
		  		}
		  	}*/
	  	}

	  	return array( "rooms"	=> $actionRooms , 
	  				  "actions" => $actions );
	}

	/**
	 * get actionRooms for a certain type and it's actions by personId
	 * @param type $uid : is the mongoId (String) of the person
	 * @param type $type : is the type of the parent element
	 * @param type $id : is the mongoId (String) of the parent Element
	 * @return person document as in db
	*/
	public static function getActionRoomsByPersonIdByType($uid,$type,$id,$archived=null) 
	{ 
		if(isset($type))
        	$where["parentType"] = $type;
        if(isset($id))
        	$where["parentId"] = $id;
        
        if(isset($archived))
			$where['status'] = ActionRoom::STATE_ARCHIVED;
		else
			$where['status'] = array('$exists' => 0 );

        $actionRooms = ActionRoom::getWhereSortLimit( $where, array("date"=>1), 0);

	  	$actions = array();
	  	$person = self::getById($uid);

	  	if (empty($person)) {
            throw new CTKException("The person id is unkown : contact your admin");
        }

	  	if ( isset($person) && isset($person["actions"]) && isset($person["actions"]["surveys"])) 
	  	{
	  		foreach ( $person["actions"]["surveys"] as $entryId => $action) 
	  		{
	  			$entry = Survey::getById( $entryId );
	  			if( isset( $entry['survey'] ) && isset( $actionRooms[ $entry['survey'] ] ) ){
		  			$entry ['action'] = $action;
		  			$actions[ $entryId ] = $entry;
		  		}
	  		}
	  	}

	  	return array( "rooms"	=> $actionRooms , 
	  				  "actions" => $actions );
	}

	/**
	 * Change the password of the user
	 * @param String $oldPassword 
	 * @param String $newPassword 
	 * @param String $userId 
	 * @return array of result (result, msg)
	 */
	public static function changePassword($oldPassword, $newPassword, $userId) {
		
		$person = self::getById($userId, false);

		if (! self::checkPassword($oldPassword, $person)) {
			return array("result" => false, "msg" => Yii::t("person","Your current password is incorrect"));
		} 

		if (strlen($newPassword) < 8) {
			return array("result" => false, "msg" => Yii::t("person","The new password should be 8 caracters long"));
		}
		
		$encodedPwd = self::hashPassword((String) $person["_id"],$newPassword);
		self::updatePersonField($userId, "pwd", $encodedPwd, $userId);
		
		return array("result" => true, "msg" => Yii::t("person","Your password has been changed with success !"));
	}

	/**
	 * Validate an email account depending on a validation key
	 * @param String $accountId the account to check
	 * @param String $validationKey the validation key
	 * @return array of result (result, msg)
	 */
	public static function validateEmailAccount($accountId, $validationKey) {
		assert('$accountId != ""; //The userId is mandatory');
		assert('$validationKey != ""; //The validation key is mandatory');
	
		if (self::isRightValidationKey($accountId, $validationKey)) {
	        //remove tobeactivated attribute on account
	        $res = self::validateUser($accountId);
        } else {
        	$res = array("result"=>false, "msg" => "The validation key is incorrect !");	
        }
	    
	    return $res;
	}
	public static function isRightValidationKey($accountId, $validationKey){
		$validationKeycheck = self::getValidationKeyCheck($accountId);
		return ($validationKeycheck == $validationKey);
	}
	/**
	 * remove tobeactivated attribute on account ??
	 * @param type $accountId 
	 * @param type $admin
	 * @return type
	 */
	public static function validateUser($accountId,$admin=false) {
		assert('$accountId != ""; //The userId is mandatory');
        $account = self::getSimpleUserById($accountId);
        $email=self::getEmailById($accountId);
        $account["email"]=$email["email"];
        if (!empty($account)) {
	       // if($admin==true){
	       	//Rest::json($accountId); exit;
	        PHDB::update(	self::COLLECTION,
	                    	array("_id"=>new MongoId($accountId)), 
	                        array('$unset' => array("roles.tobeactivated"=>""),
	                    			'$set'=> array("preferences.sendMail"=>true))
	                    );
	        //}
	       	$res = array("result"=>true, "account" => $account, "msg" => "The account and email is now validated !");
	    } else {
	    	$res = array("result"=>false, "msg" => "Unknown account !");	
	    }
        	//$res = array("result"=>true, "account" => $account, "msg" => "The account and email is now validated !");

        return $res;
	}

	public static function getValidationKeyCheck($accountId) {
		$account = self::getEmailById($accountId);
		if($account) {
			$validationKeycheck = hash('sha256',$accountId.$account["email"]);
		} else {
	    	throw new CTKException("The account is unknwon !");
	    }

	    return $validationKeycheck;
	}
	
	public static function updateMinimalData($personId, $person) {

		//Check if it's a minimal user
		$account = self::getById($personId, false);

		if (! @$account["pending"]) {
			throw new CTKException("Impossible to update an account not pending !");
		} else {
			$person["email"] = $account["email"];
			$pwd = self::hashPassword($personId, $person["pwd"]);

			//Update des infos minimales
			try {
				$personToUpdate = self::getAndcheckPersonData($person, self::REGISTER_MODE_TWO_STEPS, false);
			} catch (CTKException $e) {
				Rest::json(array("result" => false, "msg" => $e->getMessage()));
				die;
			}

			$personToUpdate["pwd"] = $pwd;
			// CREATE SLUG FOR CITOYENS
			$personToUpdate["slug"]=Slug::checkAndCreateSlug($personToUpdate["username"]);

			
	  	    Slug::save(Person::COLLECTION,$personId,$personToUpdate["slug"]);
			PHDB::update(self::COLLECTION, array("_id" => new MongoId($personId)), 
			                          array('$set' => $personToUpdate, '$unset' => array("pending" => "" ,"roles.tobeactivated"=>""
			                          	)));


			Preference::updatePreferences($personId, self::COLLECTION,"sendMail", true);
			
			//Send Notification to Invitor
			if(!empty($account["invitedBy"])){
				Notification::actionOnPerson(
					ActStr::VERB_SIGNIN, ActStr::ICON_SHARE, 
						array("type"=>self::COLLECTION,"id"=> $account["_id"],"name"=>$account["name"]),
						array("type"=>self::COLLECTION, "id"=> $account["invitedBy"],"name"=>"", ));
			}
			
			$res = array("result" => true, "msg" => "The pending user has been updated and is now complete","personId"=>$personId);
		}
		return $res;
	}
	
	public static function  updateNotSeeHelpCo($id){
		PHDB::update(self::COLLECTION, array("_id" => new MongoId($id)), 
			                          array('$set' => array("preferences.unseenHelpCo"=>true)));
		//Yii::app()->session["user"]["preferences"]["unseenHelpCo"]=true;
		$account=self::getById($id);
		$res=self::saveUserSessionData($account);
		return array("result" => true);
	}
	private static function hashPassword($personId, $pwd) {
		return hash('sha256', $personId.$pwd);
	}

	public static function isUniqueUsername($username) {
		$res = true;
		$checkUsername = PHDB::findOne(self::COLLECTION,array("username"=>$username));
		if ($checkUsername) {
			$res = false;	
		}
		return $res;
	}
public static function isUniqueEmail($email) {
		$res = true;
		$checkEmail = PHDB::findOne(self::COLLECTION,array("email"=>$email));
		if ($checkEmail) {
			$res = false;	
		}
		return $res;
	}
	public static function isFirstCitizen($insee) {
		$res = false;
		$checkUsername = PHDB::findOne(self::COLLECTION,array("address.codeInsee"=>$insee));
		if(empty($checkUsername))
			$res = true;
		return $res;
	}


	// TODO // Just for an id, we call a lot of datas ! To clean and delete it, function avaible in element !
	public static function getPersonIdByEmail($email) {
		//Check if the email of the person is already in the database
	  	if ($email){
		  	$account = PHDB::findOne(self::COLLECTION,array("email"=>$email));
		  	//var_dump($account);
		  	if ($account) {
		  		$id = $account["_id"] ;
			} else {
		    	$id = false ;
		    }
	  	}
	  	
	  	return $id ;
	}

	/**
    * This function checks if $userId is linked to this email
    * 
    * @autors
    **/
	public static function isLinkedEmail($email, $userId) {
    	$res = false ;
        $id = self::getPersonIdByEmail($email);
        //var_dump($id);
        if($id != false)
        {
        	$res = Link::isLinked($id, self::COLLECTION, $userId);
        	//var_dump($res);
        }	
        
        return $res;
    }

    /**
	 * getPersonFollowsByUser
	 * @return Array<Person>
	 * @author Raphael RIVIERE
	 */
  

    public static function getPersonFollowsByUser($id) {
	  	$res = array();
	  	$person = self::getById($id);
	  	
	  	if(empty($person)) {
            throw new CTKException(Yii::t("common", "Something went wrong : please contact your admin"));
        }
	  	if (isset($person) && isset($person["links"]) && isset($person["links"]["follows"])) {
	  		foreach ($person["links"]["follows"] as $key => $follow) {

				if($follow["type"] == "citoyens" || $follow["type"] == "organizations" || $follow["type"] == "projects") {
					$entity = PHDB::findOneById( $follow["type"] ,$key );
					$res[$key] = $entity;
				} else {
					error_log("[DATA-INCORRECT] - Impossible to find the ".$follow["type"]. " with the id ".$key." ! Link follow on the person id : ".$id);
				}
	  		}
	  	}
	  	return $res;
	}


	public static function getSourceAdmin($id) {
	  	$result = PHDB::findOneById( self::COLLECTION ,$id, array('sourceAdmin'));
	  	
	  	return $result['sourceAdmin'];
	}

	/**
	 * update a person in database
	 * @param String $personId : 
	 * @param array $personChangedFields fields to update
	 * @param String $userId : the userId making the update
	 * @return array of result (result => boolean, msg => string)
	 */
	public static function update($personId, $personChangedFields, $userId) {

		$person = self::getById( $personId );
		
		if (! $person) {
			error_log("Unknown person Id : ".$personId);
			return array("result"=>false, "msg"=>Yii::t("person", "Something went really wrong ! "), "id"=>$personId);
		}

		foreach ($personChangedFields as $fieldName => $fieldValue) {
				if(is_array($fieldValue) && $fieldName != "address"){
					foreach ($fieldValue as $fieldName2 => $fieldValue2) {
						self::updatePersonField($personId, $fieldName2, $fieldValue2, $userId);
					}
				}else{
					self::updatePersonField($personId, $fieldName, $fieldValue, $userId);
				}
		}

	    return array("result"=>true, "msg"=>Yii::t("person", "The person has been updated"), "id"=>$personId);
	}


	/* 	Get state an event from an OpenAgenda ID 
	*	@param string OpenAgenda ID
	*	@param string Date Update openAgenda
	*   return String ("Add", "Update" or "Delete")
	*/
	public static function createPersonFromImportData($personImportData, $warnings = null) {
		if(!empty($personImportData['name']))
			$newPerson["name"] = $personImportData["name"];

		if(!empty($personImportData['email']))
			$newPerson["email"] = $personImportData["email"];

		if(!empty($personImportData['username']))
			$newPerson["username"] = $personImportData["username"];

		if(!empty($personImportData['pwd']))
			$newPerson["pwd"] = $personImportData["pwd"];

		if(!empty($personImportData['created']))
			$newPerson["created"] = $personImportData["created"];

		if(!empty($personImportData['tags']))
			$newPerson["tags"] = $personImportData["tags"];

		if(!empty($personImportData['sourceAdmin']))
			$newPerson["sourceAdmin"] = $personImportData["sourceAdmin"];

		if(!empty($personImportData['msgInvite']))
			$newPerson["msgInvite"] = $personImportData["msgInvite"];

		if(!empty($personImportData['nameInvitor']))
			$newPerson["nameInvitor"] = $personImportData["nameInvitor"];

		if(!empty($personImportData['source'])){
			if(!empty($personImportData['source']['id']))
				$newPerson["source"]['id'] = $personImportData["source"]['id'];
			if(!empty($personImportData['source']['url']))
				$newPerson["source"]['url'] = $personImportData["source"]['url'];
			if(!empty($personImportData['source']['key']))
				$newPerson["source"]['key'] = $personImportData['source']['key'];
		}

		if(!empty($personImportData['warnings']))
			$newPerson["warnings"] = $personImportData["warnings"];

		if(!empty($personImportData['image']))
			$newPerson["image"] = $personImportData["image"];

		if(!empty($personImportData["badges"]))
			$newPerson["badges"] = $personImportData["badges"];

		if(!empty($personImportData['shortDescription']))
			$newPerson["shortDescription"] = $personImportData["shortDescription"];

		if(!empty($personImportData['geo']['latitude']))
			$newPerson['geo']['latitude'] = $personImportData['geo']['latitude'];

		if(!empty($personImportData['geo']['longitude']))
			$newPerson['geo']['longitude'] = $personImportData['geo']['longitude'];

		if(!empty($personImportData['telephone'])){
			$tel = array();
			$fixe = array();
			$mobile = array();
			$fax = array();
			if(!empty($personImportData['telephone']["fixe"])){
				foreach ($personImportData['telephone']["fixe"] as $key => $value) {
					$trimValue=trim($value);
					if(!empty($trimValue))
						$fixe[] = $trimValue;
				}
			}
			if(!empty($personImportData['telephone']["mobile"])){
				foreach ($personImportData['telephone']["mobile"] as $key => $value) {
					$trimValue=trim($value);
					if(!empty($trimValue))
						$mobile[] = $trimValue;
				}
			}
			if(!empty($personImportData['telephone']["fax"])){
				foreach ($personImportData['telephone']["fax"] as $key => $value) {
					$trimValue=trim($value);
					if(!empty($trimValue))
						$fax[] = $trimValue;
				}
			}
			if(count($mobile) != 0)
				$tel["mobile"] = $mobile ;
			if(count($fixe) != 0)
				$tel["fixe"] = $fixe ;
			if(count($fax) != 0)
				$tel["fax"] = $fax ;
			if(count($tel) != 0)	
				$newPerson['telephone'] = $tel;
		}
		//var_dump($personImportData['address']);
		if(!empty($personImportData['address'])){
			$details = Import::getAndCheckAddressForEntity($personImportData['address'], (empty($newPerson['geo']) ? null : $newPerson['geo']), $warnings) ;
			$newPerson['address'] = $details['address'];
			//var_dump($newPerson['address']);
			if(!empty($newPerson['warnings']))
				$newPerson['warnings'] = array_merge($newPerson['warnings'], $details['warnings']);
			else
				$newPerson['warnings'] = $details['warnings'];
		}
		//var_dump("---------------------------------");	
		return $newPerson;
	}


	public static function getAndCheckPersonFromImportData($person, $invite=null, $insert=null, $update=null, $warnings = null) {
		
		$newPerson = array();
		if (empty($person['name'])) {
			if($warnings)
				$newPerson["warnings"][] = "201" ;
			else
				throw new CTKException(Yii::t("import","201", null, Yii::app()->controller->module->id));
		}else
			$newPerson['name'] = $person['name'];


	  	

		if (empty($person['email'])) {
			throw new CTKException(Yii::t("import","203"));
		}else{
			//Check email
		  	$checkEmail = DataValidator::email($person["email"]);
		  	if ($checkEmail != "") {
		  		throw new CTKException(Yii::t("import","205", null, Yii::app()->controller->module->id));
		  	}
			//Check if the email of the person is already in the database
		  	$account = PHDB::findOne(self::COLLECTION,array("email"=> new MongoRegex('/^' . preg_quote(trim($person["email"])) . '$/i')));
		  	//$account = PHDB::findOne(self::COLLECTION,array("email"=>$person["email"]));
		  	if($account){
		  		throw new CTKException(Yii::t("import","206", null, Yii::app()->controller->module->id));
		  	}
		  	$newPerson['email'] = $person['email'];
		}
			
		if(!$update)
			$newPerson["created"] = new MongoDate(time());

		if(empty($person['username'])){
			if(!empty($person['email'])){
				$newPerson['username'] = self::generedUserNameByEmail($person['email'], true) ;
				if($warnings)
					$newPerson["warnings"][] = "211" ;
			}else{
				if($warnings)
					$newPerson["warnings"][] = "210" ;
				else
					throw new CTKException(Yii::t("import","210", null, Yii::app()->controller->module->id));
			}
		}else{	
			if(!self::isUniqueUsername($person["username"]) ) {
				if(!empty($invite)){
					$newPerson['username'] = self::generedUserNameByEmail($person['email'], true) ;
				}
				else
					throw new CTKException(Yii::t("import","207", null, Yii::app()->controller->module->id));
		  	}else{
		  		$newPerson['username'] = $person['username'];
		  	}
		}



		if(!empty($person["badges"]))
			$newPerson["badges"] = Badge::conformeBadges($person["badges"]);

		if(empty($invite)){

			if (empty($person['pwd'])) {
				if($warnings)
					$newPerson["warnings"][] = "204" ;
				else
					throw new CTKException(Yii::t("import","204", null, Yii::app()->controller->module->id));
			}else
				$newPerson['pwd'] = $person['pwd'];
		
			if(!empty($person['geo']) && !empty($person["geoPosition"])){
				$newPerson["geo"] = $person['geo'];
				$newPerson["geoPosition"] = $person['geoPosition'];

			}else if(!empty($person["geo"]['latitude']) && !empty($person["geo"]["longitude"])){
				$newPerson["geo"] = 	array(	"@type"=>"GeoCoordinates",
							"latitude" => $person["geo"]['latitude'],
							"longitude" => $person["geo"]["longitude"]);

				$newPerson["geoPosition"] = array("type"=>"Point",
														"coordinates" =>
															array(
																floatval($person["geo"]['latitude']),
																floatval($person["geo"]['longitude']))
													 	  	);
			}
			else if($insert){
				if($warnings)
					$newPerson["warnings"][] = "150" ;
				else
					throw new CTKException(Yii::t("import","150", null, Yii::app()->controller->module->id));
			}else if($warnings)
				$newPerson["warnings"][] = "150" ;
				
			if(!empty($person['address'])) {
				if(empty($person['address']['postalCode']) /*&& $insert*/){
					if($warnings)
						$newPerson["warnings"][] = "101" ;
					else
						throw new CTKException(Yii::t("import","101", null, Yii::app()->controller->module->id));
				}
				if(empty($person['address']['codeInsee'])/*&& $insert*/){
					if($warnings)
						$newPerson["warnings"][] = "102" ;
					else
						throw new CTKException(Yii::t("import","102", null, Yii::app()->controller->module->id));
				}
				if(empty($person['address']['addressCountry']) /*&& $insert*/){
					if($warnings)
						$newPerson["warnings"][] = "104" ;
					else
						throw new CTKException(Yii::t("import","104", null, Yii::app()->controller->module->id));
				}
				if(empty($person['address']['addressLocality']) /*&& $insert*/){
					if($warnings)
						$newPerson["warnings"][] = "105" ;
					else
						throw new CTKException(Yii::t("import","105", null, Yii::app()->controller->module->id));
				}


				$newPerson['address'] = $person['address'] ;

			}else {
				if(!empty($newPerson["geo"])){
					
					$resLocality = json_decode(Import::getLocalityByLatLonNominatim($newPerson["geo"]["latitude"], $newPerson["geo"]["longitude"]),true);
					
					//var_dump($resLocality);
					if(!empty($resLocality["address"])){
						
						$newPerson['address']['addressCountry'] = "FR";
						$city = SIG::getInseeByLatLngCp($newPerson["geo"]["latitude"], $newPerson["geo"]["longitude"], (empty($resLocality["address"]["postcode"])?null:$resLocality["address"]["postcode"]));
						/*if($city != null){
							foreach ($city as $key => $value) {
								$insee = $value["insee"];
							}
							$newPerson['address']['codeInsee'] = $insee ;
							$newPerson['address']['postalCode'] = (empty($resLocality["address"]["postcode"])?"":$resLocality["address"]["postcode"]);
							$newPerson['address']['streetAddress'] = (empty($resLocality["address"]["road"])?"":$resLocality["address"]["road"]);
							$locality = City::getAlternateNameByInseeAndCP($newPerson['address']['codeInsee'], $newPerson['address']['postalCode']);
							$newPerson['address']['addressLocality'] = $locality['alternateName'];
							

						}*/
						if(!empty($city)){
	                        foreach ($city["postalCodes"] as $keyCp => $valueCp){
								if(!empty($resLocality["address"]["postcode"]) && $valueCp["postalCode"] == $resLocality["address"]["postcode"]){
	                            	$newPerson['address']['addressCountry'] = "FR";
	                            	$newAddress["codeInsee"] = $city["insee"];
	                        		$newAddress['addressCountry'] = $city["country"];
	                                $newAddress['addressLocality'] = $valueCp["name"];
	                                $newAddress['postalCode'] = $valueCp["postalCode"];
	                                $erreur = false ;
	                                break;
	                            }
	                        }
	                        if(!empty($newAddress))
	                        	$newPerson['address'] = $newAddress;
	               	 	}


						//Result DataGouv
						/*$newPerson['address']['addressCountry'] = "FR";
						$newPerson['address']['codeInsee'] = $resLocality["features"][0]["properties"]["citycode"];
						$newPerson['address']['postalCode'] = $resLocality["features"][0]["properties"]["postcode"];
						$newPerson['address']['streetAddress'] = $resLocality["features"][0]["properties"]["street"];
						$newPerson['address']['addressLocality'] = City::getAlternateNameByInseeAndCP($newPerson['address']['codeInsee'], $newPerson['address']['postalCode']);
						*/
					}
					else if($warnings)
						$newPerson["warnings"][] = "100" ;
					else
						throw new CTKException(Yii::t("import","100", null, Yii::app()->controller->module->id));
				}
				else if($warnings)
					$newPerson["warnings"][] = "100" ;
				else
					throw new CTKException(Yii::t("import","100", null, Yii::app()->controller->module->id));
			}
		}else{
			if (!empty($person['msgInvite']))
				$newPerson["msgInvite"] = $person['msgInvite'];
			if (!empty($person['nameInvitor']))
				$newPerson["nameInvitor"] = $person['nameInvitor'];
		}
			
		
		
		
			
		if (!empty($person["invitedBy"])) {
	  		$newPerson["invitedBy"] = $person["invitedBy"];
	  	}
		
		if (!empty($person['telephone']))
			$newPerson["telephone"] = $person['telephone'];

		if (!empty($person['sourceAdmin']))
			$newPerson["sourceAdmin"] = $person['sourceAdmin'];
		
		if (!empty($person['source']))
			$newPerson["source"] = $person['source'];

		if (!empty($person['image']))
			$newPerson["image"] = $person['image'];

		if(!empty($person['shortDescription']))
			$newPerson["shortDescription"] = $person["shortDescription"];

		//Tags
		if (isset($person['tags']) ) {
			if ( is_array( $person['tags'] ) ) {
				$tags = $person['tags'];
			} else if ( is_string($person['tags']) ) {
				$tags = explode(",", $person['tags']);
			}
			$newPerson["tags"] = $tags;
		}

		if (!empty($person['source']))
			$newPerson["source"] = $person['source'];

		return $newPerson;
	}

	/**
	 * Insert a new project, checking if the project is well formated
	 * @param array $params Array with all fields for a project
	 * @param string $userId UserId doing the insertion
	 * @return array as result type
	 */
	public static function insertPersonFromImportData($person, $warnings, $invite=null, $isKissKiss = null, $invitorUrl=null, $pathFolderImage = null, $moduleId = null, $paramsLink,  $sendMail){
	    
		$account = PHDB::findOne(self::COLLECTION,array("email"=>$person["email"]));
		if($account){
			$msg = "Déja inscrits :" ;
			if(!empty($sendMail)){
				$personmail["_id"] = (String)$account["_id"];
				$personmail["email"] = $account["email"];
				if(empty($account["roles"]["tobeactivated"]) || $account["roles"]["tobeactivated"] == false){
					if(!empty($invite)){
						if(!empty($isKissKiss))
							Mail::inviteKKBB($personmail, false);
					}
					$msg .="Compte déjà activé";
				}else{
					if(!empty($invite)){
						if(empty($isKissKiss) && !empty($account["roles"]["tobeactivated"]) && $account["roles"]["tobeactivated"] == true){
							Mail::invitePerson($personmail, $person["msgInvite"], $person["nameInvitor"], $invitorUrl);
						}
						else if(!empty($isKissKiss))
							Mail::inviteKKBB($personmail, true);
					}
				}

			}
			
			if(!empty($person["badges"])){
				$badges = Badge::conformeBadges($person["badges"]);
				$res = Badge::addAndUpdateBadges($badges, (String)$account["_id"], self::COLLECTION);
				$msg .=" ".$res["msg"];
			}

			if(!empty($paramsLink) && $paramsLink["link"] == true){
				if($paramsLink["typeLink"] == "Organization"){
					if(Link::isLinked($paramsLink["idLink"],  Organization::COLLECTION, $personmail["_id"]) == false){
						Link::connect($paramsLink["idLink"], Organization::COLLECTION, $personmail["_id"], self::COLLECTION, Yii::app()->session["userId"],"members", false);
						Link::connect($personmail["_id"], self::COLLECTION, $paramsLink["idLink"], Organization::COLLECTION, Yii::app()->session["userId"],"memberOf",false);
						//Link::addMember($paramsLink["idLink"], Organization::COLLECTION, $personmail["_id"], self::COLLECTION, Yii::app()->session["userId"], $paramsLink["isAdmin"]);
					}
						
					//Link::addMember($paramsLink["idLink"], Organization::COLLECTION, $newpersonId, self::COLLECTION, Yii::app()->session["userId"], $paramsLink["isAdmin"]);
				}
			}

			/*if(!empty($person["source"]["key"])){
				//var_dump($person["source"]["key"]);
				$res = Import::addAndUpdateSourceKey($person["source"]["key"], (String)$account["_id"], self::COLLECTION);
				$msg +=" "+$res["msg"];
			}*/

			return array("result"=>true, "msg"=>$msg, "id" => $personmail["_id"]);

		}else{
			$newPerson = self::getAndCheckPersonFromImportData($person, $invite, null, null, $warnings);
	    
		    if(!empty($newPerson["warnings"]) && $warnings == true)
		    	$newPerson["warnings"] = Import::getAndCheckWarnings($newPerson["warnings"]);
		    
		    $newPerson["@context"] = array("@vocab"=>"http://schema.org",
	            							"ph"=>"http://pixelhumain.com/ph/ontology/");
		    $newPerson["roles"] = Role::getDefaultRoles();
		  	$newPerson["created"] = new mongoDate(time());
		  	$newPerson["preferences"] = array("seeExplanations"=> true);	  		

		  	if(!empty($newPerson["image"])){
				$nameImage = $newPerson["image"];
				unset($newPerson["image"]);
			}

			if(!empty($invite)){
				$msgMail = $person["msgInvite"];
				$nameInvitor = (empty($person["nameInvitor"])?"Communecter":$person["nameInvitor"]);
				$newPerson["roles"]['betaTester'] = true;
				$newPerson["pending"] = true;
				$newPerson["numberOfInvit"] = 10 ;
	        	unset($newPerson["msgInvite"]);
	        	unset($newPerson["nameInvitor"]);
			}

			PHDB::insert(self::COLLECTION , $newPerson);

		    if (isset($newPerson["_id"]))
		    	$newpersonId = (String) $newPerson["_id"];
		    else
		    	throw new CTKException("Problem inserting the new person");

		    if(!empty($nameImage)){
				try{
					$res = Document::uploadDocumentFromURL($moduleId, self::COLLECTION, $newpersonId, "avatar", false, $pathFolderImage, $nameImage);
					if(!empty($res["result"]) && $res["result"] == true){
						$params = array();
						$params['id'] = $newpersonId;
						$params['type'] = self::COLLECTION;
						$params['moduleId'] = $moduleId;
						$params['folder'] = self::COLLECTION."/".$newpersonId;
						$params['name'] = $res['name'];
						$params['author'] = Yii::app()->session["userId"] ;
						$params['size'] = $res["size"];
						$params["contentKey"] = "profil";
						$res2 = Document::save($params);
						if($res2["result"] == false)
							throw new CTKException("Impossible de save.");

					}else{
						throw new CTKException("Impossible uploader le document.");
					}
				}catch (CTKException $e){
					throw new CTKException($e);
				}	
			}


			if(!empty($paramsLink) && $paramsLink["link"] == true){
				if($paramsLink["typeLink"] == "Organization"){
					if(Link::isLinked($paramsLink["idLink"], Organization::COLLECTION, $newpersonId) == false){
						Link::connect($paramsLink["idLink"], Organization::COLLECTION, $newpersonId, self::COLLECTION, Yii::app()->session["userId"],"members", false);
						Link::connect($newpersonId, self::COLLECTION, $paramsLink["idLink"], Organization::COLLECTION, Yii::app()->session["userId"],"memberOf",false);
						//Link::addMember($paramsLink["idLink"], Organization::COLLECTION, $newpersonId, self::COLLECTION, Yii::app()->session["userId"], $paramsLink["isAdmin"]);
					}
				}
				if($paramsLink["typeLink"] == "Person"){
					//Link::connect($newOrganizationId, Organization::COLLECTION, $paramsLink["idLink"], self::COLLECTION, $creatorId,"members",$paramsLink["isAdmin"]);
					//Link::connect($paramsLink["idLink"], self::COLLECTION, $newOrganizationId, Organization::COLLECTION, $creatorId,"memberOf",$paramsLink["isAdmin"]);
				   //Link::addMember($newOrganizationId, Organization::COLLECTION, $paramsLink["idLink"], self::COLLECTION, $creatorId, $paramsLink["isAdmin"]);
				}
		
			}
			if(!empty($sendMail)){
				if(!empty($invite)){
					if(empty($isKissKiss))
						Mail::invitePerson($newPerson, $msgMail, $nameInvitor, $invitorUrl);
					else
						Mail::inviteKKBB($newPerson, true);
				}
			}
			return array("result"=>true, "msg"=>"Cette personne est communecté.", "id" => $newPerson["_id"]);

		} 
	}



	public static function generedUserNameByEmail($chaine, $isEmail = null){
		if($isEmail == true){
			$arrayEmail = explode("@", $chaine);
			$name = $arrayEmail[0];
		}else
			$name = $chaine;	
		$name = strtr($name,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ._','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY--'); // Replaces all spaces with hyphens.
		$name = preg_replace('/[^A-Za-z0-9\-]/', '', $name);
		
		if(strlen($name) >= 4 && strlen($name) <= 20 ){
			if ( !self::isUniqueUsername($name) ) {
				 $name = self::generedUserNameByEmail($name."1");
		  	}
		}else{
			if(strlen($name) < 4){

				while(strlen($name) < 4){
					$name = $name."1" ;
				}
			}

			if(strlen($name) > 20){
				$name = substr($name ,0 , 20);
			} 

		}
		return $name;
	}

	public static function clearAttributesByConfidentiality($entity){

		//si l'entité n'est pas valable on ne fait rien
		if(!isset($entity) || $entity == NULL) return $entity;

		//recupere l'id de l'entité (2 cas possibles)
		$id = isset($entity['$id']) ? $entity['$id'] : "";
		//if($id == "") $id = isset($entity['_id'])&&isset($entity['_id']['$id']) ? $entity['_id']['$id'] : "";
		if($id == "") $id = isset($entity['_id']) ? $entity['_id'] : "";
		if($id == "") $id = isset($entity['id']) ? $entity['id'] : "";
		if($id == "") return $entity;
		//var_dump($id);
		$isLinked = Link::isLinked((string)$id,self::COLLECTION, Yii::app()->session['userId']);
		
		$entity = Preference::clearByPreference($entity, self::COLLECTION, Yii::app()->session["userId"]);	

	  	if(!empty($entity["pwd"]))
	  		unset($entity["pwd"]);
		return $entity;
	}
	/**
     * get Mail Person By Id
     * @param type $id : is the mongoId of the person
     * @return type
     */
    public static function getEmailById($id) { 
        $person = PHDB::findOneById( self::COLLECTION ,$id, array("email"=>1));
        return $person;
    }



     public static function updateWithJson($json) { 
        $data = json_decode($json, true);
        $user = self::getById(Yii::app()->session['userId']);
        $res = array();
        if(!empty($data["identity"]["name"]) && $data["identity"]["name"] != $user["name"])
        	$res[] = self::updatePersonField(Yii::app()->session['userId'], "name", $data["identity"]["name"], Yii::app()->session["userId"]);

        if(!empty($data["identity"]["email"]) && $data["identity"]["email"] != $user["email"])
        	$res[] = self::updatePersonField(Yii::app()->session['userId'], "email", $data["identity"]["email"], Yii::app()->session["userId"]);

        if(!empty($data["identity"]["username"]) && $data["identity"]["username"] != $user["username"])
        	$res[] = self::updatePersonField(Yii::app()->session['userId'], "username", $data["identity"]["username"], Yii::app()->session["userId"]);

        if(!empty($data["identity"]["geo"]) && $data["identity"]["geo"] != $user["geo"]){
        	$res[] = self::updatePersonField(Yii::app()->session['userId'], "geo", $data["identity"]["geo"], Yii::app()->session["userId"]);
        }

        if(!empty($data["identity"]["address"]) && $data["identity"]["address"] != $user["address"]){
        	$res[] = self::updatePersonField(Yii::app()->session['userId'], "address", $data["identity"]["address"], Yii::app()->session["userId"]);
        }

        /*if(!empty($data["identity"]["birthDate"]) && $data["identity"]["birthDate"] != $user["birthDate"]){
        	$res[] = self::updatePersonField(Yii::app()->session['userId'], "birthDate", $data["identity"]["birthDate"], Yii::app()->session["userId"]);
        }*/
       	
        if(!empty($data["identity"]["telephone"]) && $data["identity"]["telephone"] != $user["telephone"]){
        	$res[] = self::updatePersonField(Yii::app()->session['userId'], "telephone", $data["identity"]["telephone"], Yii::app()->session["userId"]);
        }

        if(!empty($data["identity"]["tags"]) && $data["identity"]["tags"] != $user["tags"]){
        	$res[] = self::updatePersonField(Yii::app()->session['userId'], "tags", $data["identity"]["tags"], Yii::app()->session["userId"]);
        }
       	
       	if(!empty($data["identity"]["shortDescription"]) && $data["identity"]["shortDescription"] != $user["shortDescription"]){
        	$res[] = self::updatePersonField(Yii::app()->session['userId'], "shortDescription", $data["identity"]["shortDescription"], Yii::app()->session["userId"]);
        }
	    
	    if(!empty($data["identity"]["socialNetwork"]) && $data["identity"]["socialNetwork"] != $user["socialNetwork"]){
        	$res[] = self::updatePersonField(Yii::app()->session['userId'], "socialNetwork", $data["identity"]["socialNetwork"], Yii::app()->session["userId"]);
        }

       	return $res;
    }

    /**
     * Check if the user with the email exists on db and is pending
     * @param string $email the email of the user
     * @return string : id of the user with this email and pending else empty string
     */
    public static function getPendingUserByEmail($email) {
		$res = "";
		if ($email){
		  	$account = PHDB::findOne(self::COLLECTION,array("email"=>$email));
		  	if ($account && @$account["pending"]) {
		  		return (String) $account["_id"];
		  	}
		} else {
			throw new CTKException("Please fill the email of the user");
		}
		return $res;

    }


    public static function getDataBinding() {
	  	return self::$dataBinding;
	}

    /**
     * Delete an existing person.
     * - remove links on person/orga/events/projects/needs
     * - if no activity (news/cote/comments) => delete the user
     * - else anonymize the user (remove all identity field) but keep the document in person collection
     * like that the activity of element is kept.
     * @param string $id : id of the person you want to be deleted
     * @param string $userId : id of the user making the action. Can be done only with super admins user
     * @return array res : boolean, msg : string
     */
    public static function deletePerson($id, $userId) {
		//Only super admin can delete a person or user himself
    	if (! Authorisation::isUserSuperAdmin($userId) && $id != $userId ) {
    		return array("result" => false, "msg" => "You must be a superadmin to delete a person");
    	}

		$person = self::getById($id);
		if (empty($person)) return array("result" => false, "msg" => "Unknown person id");

		//Delete email 
		PHDB::remove(Cron::COLLECTION, array("to" => $person["email"]));

    	//Delete links on elements collections		
		$links2collection = array(
			//Person => Person that follows the user we want to delete and the 
			self::COLLECTION => array("follows","followers"),
			//Organization => members, followers
			Organization::COLLECTION => array("followers","members"),
			//Projects => contibutors
			Project::COLLECTION => array("contributors", "followers"),
			//Events => attendees / organizer
			Event::COLLECTION => array("attendees", "organizer"),
			//Needs => links/helpers
			Need::COLLECTION => array("helpers"),
			//Form => links/members
			Form::COLLECTION => array("members")
		);
		//$resDisconnect = array();
    	foreach ($links2collection as $collection => $linkTypes) {
    		foreach ($linkTypes as $linkType) {    		
	    		$where = array("links.".$linkType.".".$id => array('$exists' => true));
	    		$action = array('$unset' => array("links.".$linkType.".".$id => ""));

	    		$elt = PHDB::find($collection, $where, array("name"));

	    		foreach ($elt as $keyElt => $valueElt) {
	    			PHDB::update($collection, array("_id"=>new MongoId($keyElt)), $action);
	    		}
	    	}
    	}


    	//PHDB::update( Form::ANSWER_COLLECTION, array("email"=> $person["email"]), $action);

    	//Delete Notifications
    	ActivityStream::removeNotificationsByUser($id);

    	Slug::removeByParentIdAndType($id, self::COLLECTION);

    	$paramsMail = array("tpl" => "deleted",
    						"tplObject" => "[COmmunecter] Votre compte a été supprimer",
    						"tplMail" => $person["email"],
    						"name" => $person["name"] );

    	Mail::createAndSend($paramsMail);

    	//Check if the user got activity (news, comments, votes)
		$res = self::checkActivity($id);
		if ($res["result"]) {
			//Anonymize the user : Remove all fields from the person
			$where = array("_id" => new MongoId($id));
			$action = array("username" => $id, "email" => $id."@communecter.org", "name" => "Citoyen supprimé", "deletedDate" => new mongoDate(time()), "status" => "deleted");
			PHDB::update(self::COLLECTION, $where, $action);
			Log::save(array("userId" => $userId, "browser" => @$_SERVER["HTTP_USER_AGENT"], "ipAddress" => @$_SERVER["REMOTE_ADDR"], "created" => new MongoDate(time()), "action" => "deleteUser", "params" => array("id" => $id)));
		} else {
			//Delete the person
			$where = array("_id" => new MongoId($id));
	    	PHDB::remove(self::COLLECTION, $where);
		}

    	//Documents => Profil Images
    	$docType = array(Document::IMG_PROFIL, Document::IMG_BANNER, Document::IMG_SLIDER, Document::IMG_SLIDER);
    	$profilImages = Document::listMyDocumentByIdAndType($id, self::COLLECTION, $docType, Document::DOC_TYPE_IMAGE, array( 'created' => -1 ));

    	foreach ($profilImages as $docId => $document) {
    		Document::removeDocumentById($docId, $userId);
    	}
    	
    	if($id == $userId)
    		Person::clearUserSessionData();

    	return array("result" => true, "msg" => "The person has been deleted succesfully");
    }

    private static function checkActivity($id) {
    	//Check if the person got news/comments/votes
		//Comments => author
		$where = array("author" => $id);
		$count = PHDB::count(Comment::COLLECTION, $where);
		if ($count > 0) return array("result" => true, "msg" => "This person had made comments");
		//news => author ou target (type = citoyens && id = $id) ou mentions.id contient $id
		$where = array('$or' => array(
							array("author" => $id), 
							array("mentions.id" => $id), 
							array('$and' => array(
								array("target.type" => self::COLLECTION), 
								array("target.id" => $id))
							)));
		$count = PHDB::count(News::COLLECTION, $where);
		if ($count > 0) return array("result" => true, "msg" => "This person had made news");
		//surveys => VoteUp, VoteMoreInfo, VoteDown...
		$where = array('$or' => array(
						array("vote.".Action::ACTION_VOTE_UP.".".$id => array('$exists'=>1)),
						array("vote.".Action::ACTION_VOTE_ABSTAIN.".".$id => array('$exists'=>1)),
						array("vote.".Action::ACTION_VOTE_UNCLEAR.".".$id => array('$exists'=>1)),
						array("vote.".Action::ACTION_VOTE_MOREINFO.".".$id => array('$exists'=>1)),
						array("vote.".Action::ACTION_VOTE_DOWN.".".$id => array('$exists'=>1)))
					);
		$count = PHDB::count(Survey::COLLECTION, $where);
		if ($count > 0) return array("result" => true, "msg" => "This person had made votes");

		return array("result" => false, "msg" => "No activity");
    }


    public static function updateCookieCommunexion($userId, $address) {
    	$result = array("result" => false, "msg" => "User not connected");
    	

    	if(!empty($userId)){
    		try{
    			if(!empty($address)){
    				CookieHelper::setCookie("communexion",  json_encode(City::detailsLocality($address)));
    				$where = array("insee" => $address["codeInsee"]);
					$citiesResult = PHDB::findOne( City::COLLECTION , $where, array("postalCodes") );
					if(!empty($citiesResult)){
						foreach($citiesResult as $v){
							if(!empty($citiesResult["postalCodes"]) && count($citiesResult["postalCodes"]) == 1)
								CookieHelper::setCookie("communexionType", "city");
							else
								CookieHelper::setCookie("communexionType", "cp");
						}
					}else
						CookieHelper::setCookie("communexionType", "city");

    			}else{
    				//var_dump($address);
    				CookieHelper::setCookie("communexionType", null);
    				CookieHelper::setCookie("communexion",  null);
    				CookieHelper::setCookie("communexionActivated", false);
    			}
    			
	    		$result = array("result" => true, "msg" => "Cookies is updated");
			}catch (CTKException $e) {
				$result = array("result" => false, "msg" => $e->getMessage());
			}
    	}

    	return $result ;
    }


    /*public static function removeCookie($person){
    	if(!empty($person["removeCookie"]) && $person["removeCookie"] == true ){
    		CookieHelper::removeCookie("communexionType");
			CookieHelper::removeCookie("communexionValue");
			CookieHelper::removeCookie("communexionName");
			CookieHelper::removeCookie("communexionLevel");    				
			CookieHelper::removeCookie("inseeCommunexion");
			CookieHelper::removeCookie("cpCommunexion");
			CookieHelper::removeCookie("cityNameCommunexion");
    	}
    }*/

    public static function getCurrentSuperAdmins() {
    	$superAdmins = array();
    	$superAdmins = PHDB::find(self::COLLECTION, array('roles.superAdmin' => true), array("_id"));
    	return $superAdmins;
    }


    public static function updateScopeInter($id) {
    	$res = array("result" => false , "msg"=>"You are not connected");
    	$find = self::getById($id);

    	if(!empty($find)){
    		$res = PHDB::update(Person::COLLECTION, 
						array("_id"=>new MongoId($id)),
						array(	'$unset' 	=> array(	"inter" => null) ) );

    		$res = array("result" => true , "msg"=>"Ce message ne s'affichera plus");
    	}

    	return $res;
    }


    public static function getCommunexion($address) {
    	$res = array();
    	

    	if(!empty($address) && !empty($address["localityId"])){
			
    		$res = $address ;

    		$city = City::getById($address["localityId"]);
			$res["type"] = "city" ;
			$res["id"] = $address["localityId"];
			$res["city"] = $address["localityId"];
			if(!empty($city["postalCodes"]))
				$res["postalCodes"] = $city["postalCodes"] ;

			if(!empty($address["addressCountry"]))
				$res["country"] = $address["addressCountry"] ;

			if(!empty($address["addressCountry"]))
				$res["name"] = $address["addressLocality"] ;

			$res["cityName"] = City::getNameCity($res["localityId"]);

			unset($res["@type"]);
			unset($res["codeInsee"]);
			unset($res["addressCountry"]);
			unset($res["addressLocality"]);
			unset($res["streetAddress"]);


			if(!empty($res["postalCodes"]) && count($res["postalCodes"]) == 1 ){
				$res["allCP"] = true ;

				if(!empty($res["postalCodes"][0]["postalCode"])){
	                $where = array(	"country" => $res["country"], 
	                				"postalCodes.postalCode" => $res["postalCodes"][0]["postalCode"]);
	                $countCP = PHDB::count( City::COLLECTION , $where);
	                $res["uniqueCp"] = ( ($countCP > 1) ? false : true ) ;
	            }
			}else{
				$res["allCP"] = false;
			}
    	}

    	return $res ;
    }

}
?>
