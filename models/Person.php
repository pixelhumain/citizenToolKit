<?php 
class Person {
	public $jsonLD= array();
	const COLLECTION = "citoyens";
	const CONTROLLER = "person";
	const ICON = "fa-user";
	const COLOR = "#F5E740";

	//From Post/Form name to database field name with rules
	private static $dataBinding = array(
	    "name" => array("name" => "name", "rules" => array("required")),
	    "username" => array("name" => "username", "rules" => array("required", "uniqueUsername")),
	    "birthDate" => array("name" => "birthDate", "rules" => array("required")),
	    "email" => array("name" => "email", "rules" => array("email")),
	    "pwd" => array("name" => "pwd"),
	    "address" => array("name" => "address"),
	    "streetAddress" => array("name" => "address.streetAddress"),
	    "postalCode" => array("name" => "address.postalCode"),
	    "city" => array("name" => "address.codeInsee"),
	    "addressLocality" => array("name" => "address.addressLocality"), 
	    "addressCountry" => array("name" => "address.addressCountry"),
	    "geo" => array("name" => "geo"),
	    "geoPosition" => array("name" => "geoPosition"),
	    "telephone" => array("name" => "telephone"),
	    "tags" => array("name" => "tags"),
	    "shortDescription" => array("name" => "shortDescription"),
	    "facebookAccount" => array("name" => "socialNetwork.facebook"),
	    "twitterAccount" => array("name" => "socialNetwork.twitter"),
	    "gpplusAccount" => array("name" => "socialNetwork.googleplus"),
	    "gitHubAccount" => array("name" => "socialNetwork.github"),
	    "skypeAccount" => array("name" => "socialNetwork.skype"),
	    "bgClass" => array("name" => "preferences.bgClass"),
	    "bgUrl" => array("name" => "preferences.bgUrl"),
	    "roles" => array("name" => "roles"),
	);

	public static function logguedAndValid() {
    	if (isset(Yii::app()->session["userId"])) {
	    	$user = PHDB::findOneById( self::COLLECTION ,Yii::app()->session["userId"]);
	    	
	    	$valid = Role::canUserLogin($user);
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
	 */
	public static function saveUserSessionData($account)
    {
	  	Yii::app()->session["userId"] = (string)$account["_id"];
	  	Yii::app()->session["userEmail"] = $account["email"];

	  	$name = (isset($account["name"])) ? $account["name"] : "Anonymous" ;
	    $user = array("name"=>$name);
		
		if(isset( $account["username"] )) 
	      	$user ["username"] = $account["username"];
	    if(isset( $account["cp"] )) 
	      	$user ["postalCode"] = $account["cp"];
	    if( isset( $account["address"]) && isset( $account["address"]["postalCode"]) )
	     	$user ["postalCode"] = $account["address"]["postalCode"];
	    if( isset( $account["address"]) && isset( $account["address"]["codeInsee"]) )
	     	$user ["codeInsee"] = $account["address"]["codeInsee"];
	    if( isset( $account["profilImageUrl"]))
	     	$user ["profilImageUrl"] = $account["profilImageUrl"];
	    if( isset( $account["preferences"]) && isset($account["preferences"]["bgClass"]) )
	     	$user ["bg"] = $account["preferences"]["bgClass"];
	    if( isset( $account["preferences"]) && isset($account["preferences"]["bgUrl"]) )
	     	$user ["bgUrl"] = $account["preferences"]["bgUrl"];
		
		//Image profil
	    $simpleUser = self::getSimpleUserById((string)$account["_id"]);
	    if( isset( $simpleUser["profilImageUrl"]))
	     	$user ["profilImageUrl"] = $simpleUser["profilImageUrl"];

	    Yii::app()->session["user"] = $user;

        Yii::app()->session["userIsAdmin"] = Role::isUserSuperAdmin(@$account["roles"]); 

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
    }

	/**
	 * get a Person By Id
	 * @param type $id : is the mongoId of the person
	 * @return type
	 */
	public static function getById($id) { 
		/*echo "yoyo";
		var_dump($id);*/
	  	$person = PHDB::findOneById( self::COLLECTION ,$id );
	  	
	  	if (empty($person)) {
	  		//TODO Sylvain - Find a way to manage inconsistente data
            //throw new CTKException("The person id ".$id." is unkown : contact your admin");
        } else {
			if (!empty($person["birthDate"])) {
				date_default_timezone_set('UTC');
				$person["birthDate"] = date('Y-m-d H:i:s', $person["birthDate"]->sec);
			}
			$person = array_merge($person, Document::retrieveAllImagesUrl($id, self::COLLECTION));
			$person["typeSig"] = "people";
        }
        
	  	return $person;
	}

	/**
	 * Retrieve a simple user (id, name, profilImageUrl) by id from DB
	 * @param String $id of the person
	 * @return array with data id, name, profilImageUrl
	 */
	public static function getSimpleUserById($id) {
		
		$simplePerson = array();
		$person = PHDB::findOneById( self::COLLECTION ,$id, array("id" => 1, "name" => 1, "username" => 1, "email" => 1,  "shortDescription" => 1, "description" => 1,
																  "address" => 1, "geo" => 1, "roles" => 1, "tags" => 1) );
		$simplePerson["id"] = $id;
		$simplePerson["name"] = @$person["name"];
		$simplePerson["username"] = @$person["username"];
		$simplePerson["email"] = @$person["email"];
		$simplePerson["geo"] = @$person["geo"];
		$simplePerson["tags"] = @$person["tags"];
		$simplePerson["tobeactivated"] = @$person["roles"]["tobeactivated"];
		$simplePerson["shortDescription"] = @$person["shortDescription"];
		$simplePerson["description"] = @$person["description"];
		
		//images
		$simplePerson = array_merge($simplePerson, Document::retrieveAllImagesUrl($id, self::COLLECTION));

		$simplePerson["address"] = empty($person["address"]) ? array("addressLocality" => "Unknown") : $person["address"];
		
		return $simplePerson;

	}

	//TODO SBAR => should be private ?
	public static function getWhere($params) {
	  	 return PHDB::findAndSort( self::COLLECTION,$params,array("created"),null);
	}
	
	//TODO SBAR - To delete ?
	private static function setNameByid($name, $id) {
		PHDB::update(Person::COLLECTION,
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
	  	$res = array("people" => array(), "organizations" => array(), 
	  				 "projects" => array(), "events" => array());

	  	$person = self::getById($id);
	  	
	  	if (empty($person)) {
            throw new CTKException("The person id is unkown : contact your admin");
        }
        $myContacts = array();
	  	if (isset($person) && isset($person["links"])) {
	  		$myContacts = $person["links"];
	  	}

	  	foreach (array("knows", "memberOf", "projects", "events") as $n => $link) {
	  		if( isset($myContacts[$link]))
	  		{
			  	foreach ($myContacts[$link] as $key => $contact) {
			  		//error_log(var_dump($contact));
			  		$type = isset($contact["type"]) ? $contact["type"] : "";
			  		$contactComplet = null;
					if($type == "citoyens")		{ $contactComplet = self::getById($key); $type = "people"; }
					if($type == "organizations"){ $contactComplet = Organization::getById($key); }
					if($type == "projects")		{ $contactComplet = Project::getById($key); }
					if($type == "events")		{ $contactComplet = Event::getById($key); }
					
					if($contactComplet != null)	$res[$type][$key] = $contactComplet;
					
					//var_dump($contactComplet);
				}
			}
		}

		//trie les éléments dans l'ordre alphabetique par name
	  	function mySort($a, $b){ 
		  	if( isset($a['name']) && isset($b['name']) ){
		    	return (strtolower($b['name']) < strtolower($a['name']));
			}else{
				return false;
			}
		}

	  	if(isset($res["citoyens"])) 	 usort($res["people"], 		  "mySort");
	  	if(isset($res["organizations"])) usort($res["organizations"], "mySort");
	  	if(isset($res["projects"])) 	 usort($res["projects"], 	  "mySort");
	  	if(isset($res["events"])) 		 usort($res["events"], 		  "mySort");

		if($id != Yii::app()->session["userId"])
			$res["people"][(string)$id] = $person;
		
		return $res;
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
	public static function createAndInvite($param, $msg = null) {
	  	try {
	  		$res = self::insert($param, true);
	  		//send invitation mail
			Mail::invitePerson($res["person"], $msg);
	  	} catch (CTKException $e) {
	  		$res = array("result"=>false, "msg"=> $e->getMessage());
	  	}
        //TODO TIB : mail Notification 
        //for the organisation owner to subscribe to the network 
        //and complete the Organisation Profile
        return $res;
	}

	/**
	 * Apply person checks and business rules before inserting
	 * Throws CTKException on error
	 * @param array $person : array with the data of the person to check
	 * @param boolean $minimal : true : a person can be created using only name and email. 
	 * Else : postalCode, city and pwd are also requiered
	 * @param boolean $uniqueEmail : true : check if a person already exist in the db with that email
	 * @return the new person with the business rules applied
	 */
	public static function getAndcheckPersonData($person, $minimal, $uniqueEmail = true) {
		$dataPersonMinimal = array("name", "email");
		
		$newPerson = array();
		if (! $minimal) {
			array_push($dataPersonMinimal, "username", "postalCode", "city", "pwd");
		} else {
			$newPerson["pending"] = true;
		}

		//Check the minimal data
	  	foreach ($dataPersonMinimal as $data) {
	  		if ( empty( $person["$data"] ) )
	  			throw new CTKException(Yii::t("person","Problem inserting the new person : ").$data.Yii::t("person"," is missing"));
	  	}
	  	
	  	$newPerson["name"] = $person["name"];

	  	if(! preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#',$person["email"])) { 
	  		throw new CTKException(Yii::t("person","Problem inserting the new person : email is not well formated"));
        } else {
        	$newPerson["email"] = $person["email"];
        }

		//Check if the email of the person is already in the database
	  	if ($uniqueEmail) {
		  	$account = PHDB::findOne(Person::COLLECTION,array("email"=>$person["email"]));
		  	if ($account) {
		  		throw new CTKException(Yii::t("person","Problem inserting the new person : a person with this email already exists in the plateform"));
		  	}
	  	}

	  	if (!empty($person["invitedBy"])) {
	  		$newPerson["invitedBy"] = $person["invitedBy"];
	  	}

	  	if (! $minimal) {
		  	//user name
		  	$newPerson["username"] = $person["username"];
		  	if ( ! self::isUniqueUsername($newPerson["username"]) ) {
		  		throw new CTKException(Yii::t("person","Problem inserting the new person : a person with this username already exists in the plateform"));
		  	}

		  	//Encode the password
		  	$newPerson["pwd"] = hash('sha256', $person["email"].$person["pwd"]);
		  	
		  	//Manage the adress : postalCode / adressLocality / codeInsee
		  	//Get Locality label
		  	try {
		  		//Format adress 
		  		$newPerson["address"] = SIG::getAdressSchemaLikeByCodeInsee($person["city"]);

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
	 * @param boolean $minimal : true : a person can be created using only "name" and "email". Else : "postalCode" and "pwd" are also requiered
	 * @return array result, msg and id
	 */
	public static function insert($person, $minimal = false) {

	  	//Check Person data + business rules
	  	$person = self::getAndcheckPersonData($person, $minimal);
	  	
	  	$person["@context"] = array("@vocab"=>"http://schema.org",
            "ph"=>"http://pixelhumain.com/ph/ontology/");

	  	$person["roles"] = Role::getDefaultRoles();

	  	$person["created"] = new mongoDate(time());

	  	PHDB::insert( Person::COLLECTION , $person);
 
        if (isset($person["_id"])) {
	    	$newpersonId = (String) $person["_id"];
	    } else {
	    	throw new CTKException("Problem inserting the new person");
	    }

		//A mail is sent to the admin
		Mail::notifAdminNewUser($person);
	    return array("result"=>true, "msg"=>"You are now communnected", "id"=>$newpersonId, "person"=>$person);
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
		$person = self::getById($id);
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

	  	if( Yii::app()->session['userId'] == (string)$person["_id"]
	  		||  ( isset($person["preferences"]) && isset($person["preferences"]["publicFields"]) && in_array( $fieldName, $person["preferences"]["publicFields"]) )  
	  		|| ( $isLinked && isset($person["preferences"]) && isset($person["preferences"]["privateFields"]) && in_array( $fieldName, $person["preferences"]["privateFields"]))  )
	  		$res = ArrayHelper::getValueByDotPath($person,$fieldName); 
	  	
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
		if ($personId != $userId && Role::isSuperAdmin(Role::getRolesUserId($userId)) == false){
			throw new CTKException("Can not update the person : you are not authorized to update that person !");
		}		


		$dataFieldName = Person::getCollectionFieldNameAndValidate($personFieldName, $personFieldValue);
		var_dump($dataFieldName);
		//Specific case : 
		//Tags
		if ($dataFieldName == "tags") 
			$personFieldValue = Tags::filterAndSaveNewTags($personFieldValue);

		//address
		$user = null;
		if ($dataFieldName == "address") 
		{
			if(!empty($personFieldValue["postalCode"]) && !empty($personFieldValue["codeInsee"])) 
			{
				$insee = $personFieldValue["codeInsee"];
				$address = SIG::getAdressSchemaLikeByCodeInsee($insee);
				$geo = SIG::getGeoPositionByInseeCode($insee);
				$set = array("address" => $address, "geo" => $geo);
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
	              
	    return array("result"=>true,"user"=>$user,"personFieldName"=>$personFieldName);
	}

	//Test and Valide a field name using the data validator
	private static function getCollectionFieldNameAndValidate($personFieldName, $personFieldValue) {
		return DataValidator::getCollectionFieldNameAndValidate(self::$dataBinding, $personFieldName, $personFieldValue);
	}

    /**
     * Login with email and password. Check if the email and password match on db.
     * @param  [string] $email   email connected to the citizen account
     * @param  [string] $pwd   pwd connected to the citizen account
     * @param  [string] $publicPage is the page requested public or not. If true, the betaTest option will not be ignored
     * @return [array] array of result as (result => boolean, msg => string)
     */
    public static function login($email, $pwd, $publicPage) 
    {
        if (empty($email) || empty($pwd)) {
        	return array("result"=>false, "msg"=>"Cette requête ne peut aboutir. Merci de bien vouloir réessayer en complétant les champs nécessaires");
        }

        Person::clearUserSessionData();
        $account = PHDB::findOne(self::COLLECTION, array( '$or' => array( 
        															array( "email" => $email),
        															array("username" => $email) ) ));
        
        //return an error when email does not exist
        if ($account == null) {
        	return array("result"=>false, "msg"=>"Email ou Mot de Passe ne correspondent pas, rééssayez.");
        }
        
        //Roles validation
        $res = Role::canUserLogin($account, $publicPage);
        if ($res["result"]) {
	        //Check the password
	        if (self::checkPassword($pwd, $account)) {
	            Person::saveUserSessionData($account);
	            $res = array("result"=>true, "id"=>$account["_id"],"isCommunected"=>isset($account["cp"]));
	        } else {
	            $res = array("result"=>false, "msg"=>"Email ou Mot de Passe ne correspondent pas, rééssayez.");
	        }
	    }
        
        return $res;
    }

    private static function checkPassword($pwd, $account) {
    	return ($account && @$account["pwd"] == hash('sha256', @$account["email"].$pwd)) ;
    }

	/**
	 * get actionRooms by personId
	 * @param type $id : is the mongoId (String) of the person
	 * @return person document as in db
	 */
	public static function getActionRoomsByPersonId($id) 
	{
		//get action Rooms I created
		$where = array( "email"=> Yii::app()->session['userEmail'] ) ;
	  	$actionRooms = PHDB::find(ActionRoom::COLLECTION,$where);//array();//ActionRoom::getWhereSortLimit( $where, array("created"=>1) ,1000);
	  	$actions = array();
	  	$person = self::getById($id);

	  	if (empty($person)) {
            throw new CTKException("The person id is unkown : contact your admin");
        }

	  	if ( isset($person) && isset($person["actions"]) && isset($person["actions"]["surveys"])) 
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
		
		$person = Person::getById($userId);

		if (! self::checkPassword($oldPassword, $person)) {
			return array("result" => false, "msg" => "Your current password is incorrect");
		} 

		if (strlen($newPassword) < 8) {
			return array("result" => false, "msg" => "The new password should be 8 caracters long");
		}
		
		$encodedPwd = hash('sha256', $person["email"].$newPassword);
		self::updatePersonField($userId, "pwd", $encodedPwd, $userId);
		
		return array("result" => true, "msg" => "Your password has been changed with success !");
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
        if (!empty($account)) {
	       // if($admin==true){
	        PHDB::update(	Person::COLLECTION,
	                    	array("_id"=>new MongoId($accountId)), 
	                        array('$unset' => array("roles.tobeactivated"=>""))
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
		$account = self::getSimpleUserById($accountId);
		if($account) {
			$validationKeycheck = hash('sha256',$accountId.$account["email"]);
		} else {
	    	throw new CTKException("The account is unknwon !");
	    }

	    return $validationKeycheck;
	}

	public static function updateMinimalData($personId, $person) {

		//Check if it's a minimal user
		$account = self::getById($personId);
		if (! @$account["pending"]) {
			throw new CTKException("Impossible to update an account not pending !");
		} else {
			$person["email"] = $account["email"];
			//Update des infos minimal
			$personToUpdate = self::getAndcheckPersonData($person, false, false);

			PHDB::update(Person::COLLECTION, array("_id" => new MongoId($personId)), 
			                          array('$set' => $personToUpdate, '$unset' => array("pending" => "","roles.tobeactivated"=>"")));

			$res = array("result" => true, "msg" => "The pending user has been updated and is now complete");

		}
		return $res;
	}

	public static function isUniqueUsername($username) {
		$res = true;
		$checkUsername = PHDB::findOne(Person::COLLECTION,array("username"=>$username));
		if ($checkUsername) {
			$res = false;	
		}
		return $res;
	}



	public static function getPersonIdByEmail($email) {
		//Check if the email of the person is already in the database
	  	if ($email){
		  	$account = PHDB::findOne(Person::COLLECTION,array("email"=>$email));
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
        $id = Person::getPersonIdByEmail($email);
        //var_dump($id);
        if($id != false)
        {
        	$res = Link::isLinked($id, Person::COLLECTION, $userId);
        	//var_dump($res);
        }	
        
        return $res;
    }

    /**
	 * get all person badly geoLocalited
	 * @return Array
	 * @author Raphael RIVIERE
	 */
    public static function getPersonBadlyGeoLocalited() {
    	$res = array() ;
       	$persons = PHDB::find(self::COLLECTION);
       	foreach ($persons as $key => $person) {
       		if(!empty($person['address'])){
       			if(!empty($person['address']["codeInsee"]) && !empty($person['address']["postalCode"])){
       				$insee = $person['address']["codeInsee"];
       				if(!empty($person['geo'])){
       					$find = false;
       					$city = SIG::getInseeByLatLngCp($person['geo']["latitude"], $person['geo']["longitude"], $person['address']["postalCode"]);
     					/*var_dump($person["name"]);
     					var_dump($person['geo']["latitude"]);
     					var_dump($person['geo']["longitude"]);
     					var_dump($person['address']["postalCode"]);
     					var_dump($city);*/
     					if(!empty($city)){
       						
       						foreach ($city as $key => $value) {
       							if($value["insee"] == $insee)
       								$find = true;
       						}
       					}
       					if($find == false){
       						//var_dump("here");
       						$result["person"] = $person;
	       					$result["error"] = "Cette entité est mal géolocalisé";
	       					$res[]= $result ;
       					}
       				}else{
	       				$result["person"] = $person;
	       				$result["error"] = "Cette entité n'a pas de géolocalisation";
	       				$res[]= $result ;
	       			}
       			}else{
       				$result["citoyen"] = $person;
       				$result["error"] = "Cette entité n'a pas de code Insee et/ou de code postal";
       				$res[]= $result ;
       			}	
       		}
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
	  	$person = Person::getById($id);
	  	
	  	if (empty($person)) {
            throw new CTKException(Yii::t("organization", "The organization id is unkown : contact your admin"));
        }
	  	if (isset($person) && isset($person["links"]) && isset($person["links"]["follows"])) {
	  		foreach ($person["links"]["follows"] as $key => $follow) {

	  					if($follow["type"] == "citoyens")
	  						$entity = PHDB::findOneById( self::COLLECTION ,$key );
	  					else if($follow["type"] == "organizations")
	  						$entity = PHDB::findOneById(ORGANIZATION::COLLECTION ,$key );

		                $res[$key] = $entity;
	  		}
	  	}
	  	return $res;
	}




}
?>