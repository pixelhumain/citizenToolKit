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
	private static $dataBinding = array(
	    "name" => array("name" => "name", "rules" => array("required")),
	    "username" => array("name" => "username", "rules" => array("required", "checkUsername")),
	    "birthDate" => array("name" => "birthDate", "rules" => array("required")),
	    "email" => array("name" => "email", "rules" => array("email")),
	    "pwd" => array("name" => "pwd"),
	    "address" => array("name" => "address"),
	    "streetAddress" => array("name" => "address.streetAddress"),
	    "postalCode" => array("name" => "address.postalCode"),
	    "codeInsee" => array("name" => "address.codeInsee"),
	    "addressLocality" => array("name" => "address.addressLocality"), 
	    "addressCountry" => array("name" => "address.addressCountry"),
	    "geo" => array("name" => "geo"),
	    "geoPosition" => array("name" => "geoPosition"),
	    "telephone" => array("name" => "telephone"),
	    "mobile" => array("name" => "telephone.mobile"),
	    "fixe" => array("name" => "telephone.fixe"),
	    "fax" => array("name" => "telephone.fax"),
	    "tags" => array("name" => "tags"),
	    "shortDescription" => array("name" => "shortDescription"),
	    "facebookAccount" => array("name" => "socialNetwork.facebook"),
	    "twitterAccount" => array("name" => "socialNetwork.twitter"),
	    "gpplusAccount" => array("name" => "socialNetwork.googleplus"),
	    "gitHubAccount" => array("name" => "socialNetwork.github"),
	    "skypeAccount" => array("name" => "socialNetwork.skype"),
	    "telegramAccount" => array("name" => "socialNetwork.telegram"),
	    "bgClass" => array("name" => "preferences.bgClass"),
	    "bgUrl" => array("name" => "preferences.bgUrl"),
	    "roles" => array("name" => "roles"),
	    "two_steps_register" => array("name" => "two_steps_register"),
	    "source" => array("name" => "source"),
	    "warnings" => array("name" => "warnings"),
	    "modules" => array("name" => "modules"),
	    "badges" => array("name" => "badges"),
	    "source" => array("name" => "source")
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
	public static function saveUserSessionData($account, $isRegisterProcess = false) {
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
		if( isset( $account["roles"]))
	     	$user ["roles"] = $account["roles"];

		//Image profil
	    $simpleUser = self::getById((string)$account["_id"]);
	    $user ["profilImageUrl"] = $simpleUser["profilImageUrl"];
	    $user ["profilThumbImageUrl"] = $simpleUser["profilThumbImageUrl"];
	    $user ["profilMarkerImageUrl"] = $simpleUser["profilMarkerImageUrl"];
	    $user ["gamification"]['total'] = $simpleUser["gamification"]['total'];
	    
	    Yii::app()->session["user"] = $user;
	    Yii::app()->session["isRegisterProcess"] = $isRegisterProcess;

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
	 * @param String $id : is the mongoId of the person
	 * @param boolean $clearAttribute : by default true. Will clear the confidential attributes
	 * @return type
	 */
	public static function getById($id, $clearAttribute = true) { 
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
			$person = array_merge($person, Document::retrieveAllImagesUrl($id, self::COLLECTION, null, $person));
			$person["typeSig"] = "people";
			if(!isset($person["address"])) 
				$person["address"] = array( "codeInsee" => "", 
											"postalCode" => "", 
											"addressLocality" => "",
											"streetAddress" => "",
											"addressCountry" => "");
        
			
        }
        
        if ($clearAttribute) {
        	$person = self::clearAttributesByConfidentiality($person);
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
		$person = PHDB::findOneById( self::COLLECTION ,$id, 
				array("id" => 1, "name" => 1, "username" => 1, "email" => 1,  "shortDescription" => 1, "description" => 1, "address" => 1, "geo" => 1, "roles" => 1, "tags" => 1, "pending" => 1, "profilImageUrl" => 1, "profilThumbImageUrl" => 1, "profilMarkerImageUrl" => 1));
		
		if (empty($person)) {
			return $simplePerson;
		}

		$simplePerson["id"] = $id;
		$simplePerson["name"] = @$person["name"];
		$simplePerson["username"] = @$person["username"];
		$simplePerson["email"] = @$person["email"];
		$simplePerson["geo"] = @$person["geo"];
		$simplePerson["tags"] = @$person["tags"];
		$simplePerson["tobeactivated"] = @$person["roles"]["tobeactivated"];
		$simplePerson["shortDescription"] = @$person["shortDescription"];
		$simplePerson["description"] = @$person["description"];
		$simplePerson["pending"] = @$person["pending"];
		
		//images
		$simplePerson = array_merge($simplePerson, Document::retrieveAllImagesUrl($id, self::COLLECTION, null, $person));

		$simplePerson["address"] = empty($person["address"]) ? array("addressLocality" => "Unknown") : $person["address"];
		
		$simplePerson = self::clearAttributesByConfidentiality($simplePerson);
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

	  	foreach (array("follows", "memberOf", "projects", "events") as $n => $link) {

	  		if( isset($myContacts[$link]))
	  		{
			  	foreach ($myContacts[$link] as $key => $contact) {
			  		//error_log(var_dump($contact));
			  		$type = isset($contact["type"]) ? $contact["type"] : "";
			  		$contactComplet = null;
					if($type == "citoyens")		{ $contactComplet = self::getById($key); $type = "people"; }
					//if ($link != "follows"){
					if($type == "organizations"){ 
						$contactComplet = Organization::getById($key);
						//Do not add orga disabled
						if (@$contactComplet["disabled"]) {
							$contactComplet = null;
						}
					}
					if($type == "projects")		{ $contactComplet = Project::getById($key); }
					if($type == "events")		{ $contactComplet = Event::getById($key); }
					//}
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

	  	if(isset($res["people"])) 	 	 usort($res["people"], 		  "mySort");
	  	if(isset($res["organizations"])) usort($res["organizations"], "mySort");
	  	if(isset($res["projects"])) 	 usort($res["projects"], 	  "mySort");
	  	if(isset($res["events"])) 		 usort($res["events"], 		  "mySort");

		if($id != Yii::app()->session["userId"])
			$res["people"][(string)$id] = $person;
		
		return $res;
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
	public static function createAndInvite($param, $msg = null) {
	  	try {
	  		//Check if the person can still invite : has he got enought invitations left
	  		$invitor = self::getById($param["invitedBy"]);
	  		if (@$invitor["numberOfInvit"] > 0 || Role::isSuperAdmin($invitor["roles"])) {
	  			$res = self::insert($param, self::REGISTER_MODE_MINIMAL);
		  		//Decrease number of invitations left for the invitor
		  		PHDB::update(self::COLLECTION, array("_id" => new MongoId($param["invitedBy"])), 
		  			array('$inc' => array("numberOfInvit" => -1)));
		  		//send invitation mail
				Mail::invitePerson($res["person"], $msg);
		  	} else {
		  		$res = array("result"=>false, "msg"=> Yii::t("person","Sorry, you can't invite more people to join the platform. You do not have enough invitations left."));
		  	}
	  		
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

	  	if ($mode == self::REGISTER_MODE_NORMAL || $mode == self::REGISTER_MODE_TWO_STEPS) {
		  	//user name
		  	$newPerson["username"] = $person["username"];
		  	if (strlen($newPerson["username"]) < 4 || strlen($newPerson["username"]) > 32) {
		  		throw new CTKException(Yii::t("person","The username length should be between 4 and 32 characters"));
		  	} 
		  	if ( ! self::isUniqueUsername($newPerson["username"]) ) {
		  		throw new CTKException(Yii::t("person","Problem inserting the new person : a person with this username already exists in the plateform"));
		  	}

		  	//Encode the password
		  	$newPerson["pwd"] = hash('sha256', $person["email"].$person["pwd"]);
		}

		if ($mode == self::REGISTER_MODE_NORMAL) {
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
	 * @param string $mode : insert mode type. 
	 * REGISTER_MODE_MINIMAL : a person can be created using only name and email. 
	 * REGISTER_MODE_NORMAL : name, email, username, password, postalCode, city
	 * REGISTER_MODE_TWO_STEPS : name, username, email, password
	 * @param string $inviteCode : invitation code
	 * @return array result : msg and id
	 */
	public static function insert($person, $mode = self::REGISTER_MODE_NORMAL, $inviteCode = null) {

	  	//Check Person data + business rules
	  	$person = self::getAndcheckPersonData($person, $mode);
	  	
	  	$person["@context"] = array("@vocab"=>"http://schema.org",
            "ph"=>"http://pixelhumain.com/ph/ontology/");

	  	$person["roles"] = Role::getDefaultRoles();

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

	  	$person["created"] = new mongoDate(time());
	  	$person["preferences"] = array("seeExplanations"=> true);
	  	
	  	if (@Yii::app()->params['betaTest'])
	  		$person["numberOfInvit"] = empty(Yii::app()->params['numberOfInvitByPerson']) ? 0 : Yii::app()->params['numberOfInvitByPerson'];

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
		$dataFieldName = Person::getCollectionFieldNameAndValidate($personFieldName, $personFieldValue);
		//var_dump($dataFieldName);
		//Specific case : 
		//Tags
		if ($dataFieldName == "tags") 
			$personFieldValue = Tags::filterAndSaveNewTags($personFieldValue);
		
		if($dataFieldName == "email" && empty($personFieldValue))
			throw new CTKException("L'email ".Yii::t("person", "is missing"));


		if ( ($personFieldName == "mobile"|| $personFieldName == "fixe" || $personFieldName == "fax") && $personFieldValue ==null  ) 
			$personFieldValue = array();

		error_log($dataFieldName);
		
		//address
		$user = null;
		$thisUser = self::getById($personId);
		if ($dataFieldName == "address") 
		{
			error_log(implode(",", $personFieldValue));
			if(!empty($personFieldValue["postalCode"]) && !empty($personFieldValue["codeInsee"])) 
			{
				$insee = $personFieldValue["codeInsee"];
				$postalCode = $personFieldValue["postalCode"];
				$address = SIG::getAdressSchemaLikeByCodeInsee($insee,$postalCode);
				if (!empty($personFieldValue["streetAddress"])) $address["streetAddress"] = $personFieldValue["streetAddress"];
				if (!empty($personFieldValue["addressCountry"])) $address["addressCountry"] = $personFieldValue["addressCountry"];
				
				$set = array("address" => $address);

				if(empty($thisUser["geo"])){
					$geo = SIG::getGeoPositionByInseeCode($insee,$postalCode);	
					SIG::updateEntityGeoposition(Person::COLLECTION,$personId,$geo["latitude"],$geo["longitude"]);
				}

				PHDB::update( self::COLLECTION, array("_id" => new MongoId($personId)), 
		                        array('$unset' => array("two_steps_register"=>"")));

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
			if(strpos($personFieldValue, "http")==false || strpos($personFieldValue, "http")>0) 
				if($personFieldValue != "")
					$personFieldValue = "https://web.telegram.org/#/im?p=@".$personFieldValue;

			$set = array($dataFieldName => $personFieldValue);
		}
		else {
			$set = array($dataFieldName => $personFieldValue);	
			//var_dump($set);
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
     */
    public static function login($email, $pwd, $isRegisterProcess) 
    {
        if (empty($email) || empty($pwd)) {
        	return array("result"=>false, "msg"=>"Cette requête ne peut aboutir. Merci de bien vouloir réessayer en complétant les champs nécessaires");
        }

        Person::clearUserSessionData();
        $account = PHDB::findOne(self::COLLECTION, array( '$or' => array( 
        															array("email" => new MongoRegex('/^'.preg_quote($email).'$/i')),
        															array("username" => $email) ) ));
        
        //return an error when email does not exist
        if ($account == null) {
        	return array("result"=>false, "msg"=>"Erreur : impossible de trouver un compte avec ce nom d'utilisateur ou cet email.");
        }
        
        //Roles validation
        $res = Role::canUserLogin($account, $isRegisterProcess);
        if ($res["result"]) {
	        //Check the password
        	if (self::checkPassword($pwd, $account)) {
	            Person::saveUserSessionData($account, $isRegisterProcess);
	            if ($res["msg"] == "notValidatedEmail") 
	        		return $res;
	        	else
	            	$res = array("result"=>true, "id"=>$account["_id"], "isCommunected"=>isset($account["cp"]), "msg" => "Vous êtes maintenant identifié : bienvenue sur communecter.");
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

	  	if ( isset($person) && isset($person["actions"]) ) 
	  	{
	  		if(isset($person["actions"]["surveys"]))
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
	public static function getActionRoomsByPersonIdByType($uid,$type,$id) 
	{
		if(isset($type))
        	$where["parentType"] = $type;
        if(isset($id))
        	$where["parentId"] = $id;

        $actionRooms = ActionRoom::getWhereSortLimit( $where, array("date"=>1), 15);

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
		
		$person = Person::getById($userId);

		if (! self::checkPassword($oldPassword, $person)) {
			return array("result" => false, "msg" => Yii::t("person","Your current password is incorrect"));
		} 

		if (strlen($newPassword) < 8) {
			return array("result" => false, "msg" => Yii::t("person","The new password should be 8 caracters long"));
		}
		
		$encodedPwd = hash('sha256', $person["email"].$newPassword);
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
		$account = self::getById($personId, false);
		if (! @$account["pending"]) {
			throw new CTKException("Impossible to update an account not pending !");
		} else {
			$person["email"] = $account["email"];
			//Update des infos minimal
			$personToUpdate = self::getAndcheckPersonData($person, self::REGISTER_MODE_TWO_STEPS, false);

			PHDB::update(Person::COLLECTION, array("_id" => new MongoId($personId)), 
			                          array('$set' => $personToUpdate, '$unset' => array("pending" => "" ,"roles.tobeactivated"=>""
			                          	)));
			
			//Send Notification to Invitor
			Notification::actionOnPerson(
				ActStr::VERB_SIGNIN, ActStr::ICON_SHARE, 
				array("type"=>self::COLLECTION,"id"=> $account["_id"],"name"=>$account["name"]),
				array("type"=>self::COLLECTION, "id"=> $account["invitedBy"],"name"=>"", ));
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
	 * getPersonFollowsByUser
	 * @return Array<Person>
	 * @author Raphael RIVIERE
	 */
  

    public static function getPersonFollowsByUser($id) {
	  	$res = array();
	  	$person = Person::getById($id);
	  	
	  	if(empty($person)) {
            throw new CTKException(Yii::t("common", "Something went wrong : please contact your admin"));
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

		if (!empty($personImportData['badges'])){
			$newPerson["badges"] = $personImportData['badges'];
		}
			


		if(!empty($personImportData['image']))
			$newPerson["image"] = $personImportData["image"];

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
			$newOrganization['address'] = $details['address'];

			if(!empty($details['geo']))
				$newPerson['geo'] = $details['geo'] ;
			
			if(!empty($details['geoPosition']))
				$newPerson['geoPosition'] = $details['geoPosition'] ;

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
			if(! preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#',$person["email"])){
				throw new CTKException(Yii::t("import","205", null, Yii::app()->controller->module->id));
	        }

			//Check if the email of the person is already in the database
		  	$account = PHDB::findOne(Person::COLLECTION,array("email"=>$person["email"]));
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
			if ( !self::isUniqueUsername($person["username"]) ) {
				throw new CTKException(Yii::t("import","207", null, Yii::app()->controller->module->id));
		  	}
		  	$newPerson['username'] = $person['username'];
		}

		if(empty($invite)){

			if (empty($person['pwd'])) {
				if($warnings)
					$newPerson["warnings"][] = "204" ;
				else
					throw new CTKException(Yii::t("import","204", null, Yii::app()->controller->module->id));
			}else
				$newPerson['pwd'] = $person['pwd'];
		}
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
						
						
						$city = SIG::getCityByLatLngGeoShape($newPerson["geo"]["latitude"], $newPerson["geo"]["longitude"], (empty($resLocality["address"]["postcode"])?null:$resLocality["address"]["postcode"]));
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
		//}else{
			if (!empty($person['msgInvite']))
				$newPerson["msgInvite"] = $person['msgInvite'];
			if (!empty($person['nameInvitor']))
				$newPerson["nameInvitor"] = $person['nameInvitor'];
		//}
			
		
		
		
			
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

		if (!empty($person['badges']))
			$newPerson["badges"] = $person['badges'];

		return $newPerson;
	}

	/**
	 * Insert a new project, checking if the project is well formated
	 * @param array $params Array with all fields for a project
	 * @param string $userId UserId doing the insertion
	 * @return array as result type
	 */
	public static function insertPersonFromImportData($person, $warnings, $invite=null, $pathFolderImage = null, $moduleId = null, $paramsLink = null, $sendMail = null){
	    
	    try{
	    	$newPerson = self::getAndCheckPersonFromImportData($person, $invite, null, null, $warnings);
	    	//var_dump($newPerson);
		    if(!empty($newPerson["warnings"]) && $warnings == true)
		    	$newPerson["warnings"] = Import::getAndCheckWarnings($newPerson["warnings"]);
		    if(!empty($newPerson["badges"])){
		    	$newPerson["badges"] = Badge::conformeBadges($newPerson["badges"]);
		    }
		    
		    
		    $newPerson["@context"] = array("@vocab"=>"http://schema.org",
	            "ph"=>"http://pixelhumain.com/ph/ontology/");
		    $newPerson["roles"] = Role::getDefaultRoles();
		  	$newPerson["created"] = new mongoDate(time());
		  	$newPerson["preferences"] = array("seeExplanations"=> true);

		  	if(!empty($newPerson["image"])){
				$nameImage = $newPerson["image"];
				unset($newPerson["image"]);
			}
			
			if(!empty($invite) && $sendMail == true ){
				$msgMail = $person["msgInvite"];
				$nameInvitor = $person["nameInvitor"];
	        	unset($person["msgInvite"]);
	        	unset($person["nameInvitor"]);
			}

			PHDB::insert(Person::COLLECTION , $newPerson);

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

				if($paramsLink["typeLink"] == "Organization")
						$resLink = Link::addMember( $paramsLink["idLink"], Organization::COLLECTION, $newpersonId, Person::COLLECTION, Yii::app()->session['userId'], $paramsLink["isAdmin"]);
				
				if($paramsLink["typeLink"] == "Person")
					Link::addMember($paramsLink["idLink"], Person::COLLECTION, $newpersonId, Person::COLLECTION,  Yii::app()->session['userId'], $paramsLink["isAdmin"]);
			}

			if(!empty($invite) && $sendMail == true){
				//Mail::invitePerson($newPerson, $msgMail, $nameInvitor);
			}
			return array("result"=>true, "msg"=>"Cette personne est communecté.", "id" => $newPerson["_id"]);	

		}catch (CTKException $e){
			$res = self::updatePersonFromImportData($person, $e, $paramsLink);
	    	return array("result"=>false, "msg"=>$res["msg"]);
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
		
		$isLinked = Link::isLinked((string)$id,Person::COLLECTION, Yii::app()->session['userId']);
		$attNameConfidentiality = array("email", "locality", "phone");

		foreach ($attNameConfidentiality as $key => $fieldName) {
			if( Yii::app()->session['userId'] == (string)$id 
		  		||  ( isset($entity["preferences"]) && isset($entity["preferences"]["publicFields"]) && in_array( $fieldName, $entity["preferences"]["publicFields"]) )  
		  		|| ( $isLinked && isset($entity["preferences"]) && isset($entity["preferences"]["privateFields"]) && in_array( $fieldName, $entity["preferences"]["privateFields"]))  )
			{}
			else{
				if($fieldName == "locality")  { $entity["address"]["streetAddress"] = ""; }
				else if($fieldName == "phone"){ $entity["telephone"] = ""; }
				else{
					$entity[$fieldName] = "";
				}
			}
	  	}	
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

    /**
     * Check if the user with the email exists on db and is pending
     * @param string $email the email of the user
     * @return string : id of the user with this email and pending else empty string
     */
    public static function getPendingUserByEmail($email) {
		$res = "";
		if ($email){
		  	$account = PHDB::findOne(Person::COLLECTION,array("email"=>$email));
		  	if ($account && @$account["pending"]) {
		  		return (String) $account["_id"];
		  	}
		} else {
			throw new CTKException("Please fill the email of the user");
		}
		return $res;
    }

    public static function updatePersonFromImportData($person, $e, $paramsLink = null){
    	$res = array("result"=>false, "msg"=>$e->getMessage());

    	if(Yii::t("import","206", null, Yii::app()->controller->module->id) == $e->getMessage()){
    		$account = PHDB::findOne(Person::COLLECTION,array("email"=>$person["email"]));
    		/*if(!empty($person["source"]["key"]) ){
    			if(!empty($account["source"]["key"])){
    				if(is_array($account["source"]["key"]))
    					$account["source"]["key"] = array_merge($account["source"]["key"], $person["source"]["key"]);
    				else if(is_string($account["source"]["key"])){
    					$account["source"]["key"] = array_merge(array($account["source"]["key"]), $person["source"]["key"]);
    				}
    			}
    			else
    				$account["source"]["key"] = $person["source"]["key"];

    			PHDB::update(Person::COLLECTION,
							array("_id" => new MongoId((String)$account["_id"])),
            				array('$set' => array("source.key"	=> $account["source"]["key"])),
            				array('upsert' => true ));
    		}else
    			$account["source"]["key"] = array();*/
    		if(!empty($person["source"]["key"]) )
    			$resSourceKey = Import::addAndUpdateSourceKey($person["source"]["key"], (String)$account["_id"], self::COLLECTION);
    		if(!empty($person["badges"]) )
    			$resBadge = Badge::AddAndUpdateBadges($person["badges"], (String)$account["_id"], self::COLLECTION);


    		if(!empty($paramsLink) && $paramsLink["link"] == true){
				if($paramsLink["typeLink"] == "Organization")
					Link::addMember( $paramsLink["idLink"], Organization::COLLECTION, (String)$account["_id"], Person::COLLECTION, Yii::app()->session['userId'], $paramsLink["isAdmin"]);
			
				if($paramsLink["typeLink"] == "Person")
					Link::addMember($paramsLink["idLink"], Person::COLLECTION, (String)$account["_id"], Person::COLLECTION,  Yii::app()->session['userId'], $paramsLink["isAdmin"]);
			}
    		$res = array("result"=>true, "msg"=>Yii::t("import","250", null, Yii::app()->controller->module->id));

    	}
    		

	  	//var_dump($account);

	  	return $res;
    }

}
?>
