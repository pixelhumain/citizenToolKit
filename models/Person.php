<?php 
class Person {
	public $jsonLD= array();
	const COLLECTION = "citoyens";
	const CONTROLLER = "person";
	const ICON = "fa-user";

	//From Post/Form name to database field name with rules
	private static $dataBinding = array(
	    "name" => array("name" => "name", "rules" => array("required")),
	    "birthDate" => array("name" => "birthDate", "rules" => array("required")),
	    "email" => array("name" => "email", "rules" => array("email")),
	    "address" => array("name" => "address"),
	    "streetAddress" => array("name" => "address.streetAddress"),
	    "postalCode" => array("name" => "address.postalCode"),
	    "city" => array("name" => "address.codeInsee"),
	    "addressLocality" => array("name" => "address.addressLocality"),
	    "addressCountry" => array("name" => "address.addressCountry"),
	    "telephone" => array("name" => "telephone"),
	    "tags" => array("name" => "tags"),
	    "description" => array("name" => "description"),
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
    	$user = PHDB::findOneById( self::COLLECTION ,Yii::app()->session["userId"]);
    	
    	$valid = Roles::canUserLogin($user);
    	$isLogguedAndValid = (isset( Yii::app()->session["userId"]) && $valid["result"]);
    	
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

	    if( isset($account["isAdmin"]) && $account["isAdmin"] )
            Yii::app()->session["userIsAdmin"] = $account["isAdmin"]; 

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
    }

	/**
	 * get a Person By Id
	 * @param type $id : is the mongoId of the person
	 * @return type
	 */
	public static function getById($id) {
	  	$person = PHDB::findOneById( self::COLLECTION ,$id );
	  	
	  	if (empty($person)) {
	  		//TODO Sylvain - Find a way to manage inconsistente data
            //throw new CTKException("The person id ".$id." is unkown : contact your admin");
        } else {
			$person["publicURL"] = '/person/public/id/'.$id;
			if (!empty($person["birthDate"])) {
				date_default_timezone_set('UTC');
				$person["birthDate"] = date('Y-m-d H:i:s', $person["birthDate"]->sec);
			}
			$profil = Document::getLastImageByKey($id, self::COLLECTION, Document::IMG_PROFIL);
			$person["profilImageUrl"] = $profil;
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
		$person = PHDB::findOneById( self::COLLECTION ,$id, array("id" => 1, "name" => 1, "email" => 1, "address" => 1) );

		$simplePerson["id"] = $id;
		$simplePerson["name"] = @$person["name"];
		$simplePerson["email"] = @$person["email"];
		$profil = Document::getLastImageByKey($id, self::COLLECTION, Document::IMG_PROFIL);
		$simplePerson["profilImageUrl"] = $profil;
		$simplePerson["address"] = @$person["address"];
		
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
	        if (!empty($organization)) {
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

	/**
	 * Happens when a Person is invited or linked as a member and doesn't exist in the system
	 * It is created in a temporary state
	 * This creates and invites the email to fill extra information 
	 * into the Person profile 
	 * @param type $param 
	 * @return type
	 */
	public static function createAndInvite($param) {
	  	try {
	  		$res = self::insert($param, true);
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
	 * @return the new person with the business rules applied
	 */
	public static function getAndcheckPersonData($person, $minimal) {
		$dataPersonMinimal = array("name", "email");
		$newPerson = array();
		if (! $minimal) {
			array_push($dataPersonMinimal, "postalCode", "city", "pwd");
		}
		//Check the minimal data
	  	foreach ($dataPersonMinimal as $data) {
	  		if (empty($person["$data"])) 
	  			throw new CTKException(Yii::t("person","Problem inserting the new person : ").$data.Yii::t("person"," is missing"));
	  	}
	  	
	  	$newPerson["name"] = $person["name"];

	  	if(! preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#',$person["email"])) { 
	  		throw new CTKException(Yii::t("person","Problem inserting the new person : email is not well formated"));
        } else {
        	$newPerson["email"] = $person["email"];
        }

		//Check if the email of the person is already in the database
	  	$account = PHDB::findOne(Person::COLLECTION,array("email"=>$person["email"]));
	  	if ($account) {
	  		throw new CTKException(Yii::t("person","Problem inserting the new person : a person with this email already exists in the plateform"));
	  	}
	  	
	  	if (!empty($person["invitedBy"])) {
	  		$newPerson["invitedBy"] = $person["invitedBy"];
	  	}

	  	if (! $minimal) {
		  	//Encode the password
		  	$newPerson["pwd"] = hash('sha256', $person["email"].$person["pwd"]);
		  	
		  	//Manage the adress : postalCode / adressLocality / codeInsee
		  	//Get Locality label
		  	try {
		  		//Format adress 
		  		$newPerson["address"] = SIG::getAdressSchemaLikeByCodeInsee($person["city"]);
		  		$newPerson["geo"] = SIG::getGeoPositionByInseeCode($person["city"]);
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

	  	$person["created"] = time();

	  	PHDB::insert( Person::COLLECTION , $person);
 
        if (isset($person["_id"])) {
	    	$newpersonId = (String) $person["_id"];
	    } else {
	    	throw new CTKException("Problem inserting the new person");
	    }

	    //send validation mail
		Mail::validatePerson($person);
		//A mail is sent to the admin
		Mail::notifAdminNewUser($person);

	    return array("result"=>true, "msg"=>"You are now communnected", "id"=>$newpersonId); 
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
			throw new CTKException("The person id is unknown ! Check your URL");
		}

		return $person;
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

		if ($personId != $userId) 
			throw new CTKException("Can not update the person : you are not authorized to update that person !");	


		$dataFieldName = Person::getCollectionFieldNameAndValidate($personFieldName, $personFieldValue);
	
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
				$set = array("address" => $address, "geo" => SIG::getGeoPositionByInseeCode($insee));
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
			if ( $personFieldName == "bgClass") 
			{
				//save to session for all page reuse
				$user = Yii::app()->session["user"];
				$user["bg"] = $personFieldValue;
				Yii::app()->session["user"] = $user;
			} else if ( $personFieldName == "bgUrl") 
			{
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
     * Register or Login
     * on PH registration requires only an email 
     * test exists 
     * if exists > request pwd
     * otherwise add to DB 
     * send validation mail 
     * @param  [string] $email   email connected to the citizen account
     * @param  [string] $pwd   pwd connected to the citizen account
     * @return [type] [description]
     */
    public static function login($email, $pwd) 
    {
        if (! Yii::app()->request->isAjaxRequest || empty($email) || empty($pwd)) {
        	return array("result"=>false, "msg"=>"Cette requête ne peut aboutir. Merci de bien vouloir réessayer en complétant les champs nécessaires");
        }

        Person::clearUserSessionData();
        $account = PHDB::findOne(self::COLLECTION, array("email"=>$email));
        
        //Roles validation
        $res = Role::canUserLogin($account);
        if ($res["result"]) {
	        //Check the password
	        if ($account && !empty($pwd) && $account["pwd"] == hash('sha256', $email.$pwd)) {
	            Person::saveUserSessionData($account);
	            $res = array("result"=>true,  "id"=>$account["_id"],"isCommunected"=>isset($account["cp"]));
	        } else {
	            $res = array("result"=>false, "msg"=>"Email ou Mot de Passe ne correspondent pas, rééssayez.");
	        }
	    }
        
        return $res;
    }

	/**
	 * get actionRooms by personId
	 * @param type $id : is the mongoId (String) of the person
	 * @return person document as in db
	 */
	public static function getActionRoomsByPersonId($id) 
	{
		//get action Rooms I created
		$where = array("created"=>array('$exists'=>1) ) ;
	  	$actionRooms = ActionRoom::getWhereSortLimit( $where, array("date"=>1) ,1000);
	  	$actions = array();
	  	$person = self::getById($id);

	  	if (empty($person)) {
            throw new CTKException("The person id is unkown : contact your admin");
        }

	  	if ( isset($person) && isset($person["actions"]) && isset($person["actions"]["surveys"])) 
	  	{
	  		$actionRooms = array();
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

	  	return array( "rooms" => $actionRooms , 
	  				  "actions"     => $actions );
	}

}
?>