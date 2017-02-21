<?php 
class Organization {

	const COLLECTION = "organizations";
	const CONTROLLER = "organization";
	const ICON = "fa-users";
	const ICON_BIZ = "fa-industry";
	const ICON_GROUP = "fa-circle-o";
	const ICON_GOV = "fa-circle-o";
	const COLOR = "#93C020";

	const TYPE_NGO = "NGO";
	const TYPE_BUSINESS = "LocalBusiness";
	const TYPE_GROUP = "Group";
	const TYPE_GOV = "GovernmentOrganization";

	public static $types = array(
        "NGO" => "Association",
        "LocalBusiness" => "Entreprise",
        "Group" => "Groupe",
        "GovernmentOrganization" => "Organisation Gouvernementale"
	); 

	//From Post/Form name to database field name
	//TODO : remove name   
	public static $dataBinding = array(
	    "name" => array("name" => "name", "rules" => array("required", "organizationSameName")),
	    "email" => array("name" => "email", "rules" => array("email")),
	    "type" => array("name" => "type", "rules" => array("typeOrganization")),
	    "shortDescription" => array("name" => "shortDescription"),
	    "description" => array("name" => "description"),
	    "category" => array("name" => "category"),
	    "address" => array("name" => "address", "rules" => array("addressValid")),
	    "addresses" => array("name" => "addresses"),
	    "streetAddress" => array("name" => "address.streetAddress"),
	    "postalCode" => array("name" => "address.postalCode"),
	    "city" => array("name" => "address.codeInsee"),
	    "addressLocality" => array("name" => "address.addressLocality"),
	    "addressCountry" => array("name" => "address.addressCountry"),
	    "geo" => array("name" => "geo", "rules" => array("geoValid")),
	    "geoPosition" => array("name" => "geoPosition", "rules" => array("geoPositionValid")),
	    "tags" => array("name" => "tags"),
	    "typeIntervention" => array("name" => "typeIntervention"),
	    "typeOfPublic" => array("name" => "typeOfPublic"),
	    "url"=>array("name" => "url"),
	    "telephone" => array("name" => "telephone"),
	    "mobile" => array("name" => "telephone.mobile"),
	    "fixe" => array("name" => "telephone.fixe"),
	    "fax" => array("name" => "telephone.fax"),
	    "modules" => array("name" => "modules"),
	    "preferences" => array("name" => "preferences"),
	    "video" => array("name" => "video"),
	    "state" => array("name" => "state"),
	    "warnings" => array("name" => "warnings"),
	    "urlFacebook" => array("name" => "urlFacebook"),
	    "urlTwitter" => array("name" => "urlTwitter"),
	    "isOpenData" => array("name" => "isOpenData"),
	    "badges" => array("name" => "badges"),
		"source" => array("name" => "source", "rules" => array("source")),
	    "role" => array("name" => "role"),
	    "medias" => array("name" => "medias"),
	    "urls" => array("name" => "urls"),
	    "modified" => array("name" => "modified"),
	    "updated" => array("name" => "updated"),
	    "creator" => array("name" => "creator"),
	    "created" => array("name" => "created"),
	    "locality" => array("name" => "address"),
	    "contacts" => array("name" => "contacts"),
	    "urls" => array("name" => "urls"),
	    "descriptionHTML" => array("name" => "descriptionHTML"),
	);
	
	//See findOrganizationByCriterias...
	public static function getWhere($params) {
	  	return PHDB::find( self::COLLECTION,$params);
	}

	//TODO SBAR - First test to validate data. Move it to DataValidator
  	private static function getCollectionFieldNameAndValidate($organizationFieldName, $organizationFieldValue, $organizationId) {
		return DataValidator::getCollectionFieldNameAndValidate(self::$dataBinding, $organizationFieldName, $organizationFieldValue, $organizationId);
	}

	/** TODO : REOMVE DEPRECATED with ELement refactor
	 * insert a new organization in database
	 * @param array A well format organization 
	 * @param String $creatorId : an existing user id representing the creator of the organization
	 * @param String $adminId : can be ommited. user id representing the administrator of the organization
	 * @return array result as an array. 
	 */
	public static function insert($organization, $creatorId, $adminId = null) {
	    
	    $newOrganization = Organization::getAndCheckOrganization($organization);
		

		//Manage tags : save any inexistant tag to DB 
		if (!empty($newOrganization["tags"]))
			$newOrganization["tags"] = Tags::filterAndSaveNewTags($newOrganization["tags"]);

		//Add the user creator of the organization in the system
		if (empty($creatorId)) {
			throw new CTKException("The creator of the organization is required.");
		} else {
			$newOrganization["creator"] = $creatorId;	
		}

		if(empty($newOrganization["preferences"])){
			$newOrganization["preferences"] = array("publicFields" => array(), "privateFields" => array(), "isOpenData"=>true,"isOpenEdition"=>true);
		}
		$newOrganization["modified"] = new MongoDate(time());
		$newOrganization["updated"] = time();	
		//Insert the organization
	    PHDB::insert( Organization::COLLECTION, $newOrganization);
		
	    if (isset($newOrganization["_id"])) {
	    	$newOrganizationId = (String) $newOrganization["_id"];
	    } else {
	    	throw new CTKException(Yii::t("organization","Problem inserting the new organization"));
	    }

	    Badge::addAndUpdateBadges("opendata", $newOrganizationId, Organization::COLLECTION);
		
		//Manage link with the creator depending of the role selected
		if (@$organization["role"] == "admin") {
			$isToLink = true;
			$memberId = $creatorId;
			$isAdmin = true;
		} else if (@$organization["role"] == "member") {
			$isToLink = true;
			$memberId = $creatorId;
			$isAdmin = false;
		} else if (@$organization["role"] == "creator") {
			$isToLink = false;
		}
		unset($organization["role"]);

		//If the adminId is set then add him as admin
		if ($adminId) {
			$isToLink = true;
			$memberId = $adminId;
			$isAdmin = true;
		}
		
		if ($isToLink) {
			//Create link in both entity person and organization 
			Link::connect($newOrganizationId, Organization::COLLECTION, $memberId, Person::COLLECTION, $creatorId,"members",$isAdmin);
			Link::connect($memberId, Person::COLLECTION, $newOrganizationId, Organization::COLLECTION, $creatorId,"memberOf",$isAdmin);
		   // Link::addMember($newOrganizationId, Organization::COLLECTION, $memberId, Person::COLLECTION, $creatorId, $isAdmin);
		}

	    //send Notification Email
	    $creator = Person::getById($creatorId);
	    //Mail::newOrganization($creator,$newOrganization);
	    if(isset($newOrganization["geo"]) && !empty($newOrganization["geo"])){
		    $orgaGeo=$newOrganization["geo"];
	    }
	    else
	    	$orgaGeo="";
	    
	    $orgaTags= ((@$newOrganization["tags"] && !empty($newOrganization["tags"]))?$newOrganization["tags"]:null);
	    
	    if (@$newOrganization["address"]["codeInsee"] && !empty($newOrganization["address"]["codeInsee"]))
	    	$orgaCodeInsee=$newOrganization["address"];
	    else
	    	$orgaCodeInsee="";
	    
		Notification::createdObjectAsParam(Person::COLLECTION,$creatorId,Organization::COLLECTION, $newOrganizationId, Person::COLLECTION,$creatorId, $orgaGeo,$orgaTags,$orgaCodeInsee);
		ActivityStream::saveActivityHistory(ActStr::VERB_CREATE, $newOrganizationId, Organization::COLLECTION, "organization", $newOrganization["name"]);
	    $newOrganization = Organization::getById($newOrganizationId);
	    return array("result"=>true,
		    			"msg"=>"Votre organisation est communectée.", 
		    			"id"=>$newOrganizationId, 
		    			"newOrganization"=> $newOrganization);
	}
	
	/**
	 * insert a new organization in database
	 * @param array A well format organization 
	 * @param String $creatorId : an existing user id representing the creator of the organization
	 * @param String $adminId : can be ommited. user id representing the administrator of the organization
	 * @return array result as an array. 
	 */

	public static function afterSave($organization, $creatorId,$paramsImport=null) {

	    $newOrganizationId = (string)$organization['_id'];
		Badge::addAndUpdateBadges("opendata", $newOrganizationId, Organization::COLLECTION);
		//Manage link with the creator depending of the role selected
		if (@$organization["role"] == "admin") {
			$isToLink = true;
			$memberId = $creatorId;
			$isAdmin = true;
		} else if (@$organization["role"] == "member") {
			$isToLink = true;
			$memberId = $creatorId;
			$isAdmin = false;
		} else if (@$organization["role"] == "creator") {
			$isToLink = false;
		}
		unset($organization["role"]);
		
		
		if ($isToLink) {
			//Create link in both entity person and organization 
			Link::connect($newOrganizationId, Organization::COLLECTION, $memberId, Person::COLLECTION, $creatorId,"members",$isAdmin);
			Link::connect($memberId, Person::COLLECTION, $newOrganizationId, Organization::COLLECTION, $creatorId,"memberOf",$isAdmin);
		}

		if (@$paramsImport) {
			if(!empty($paramsImport["link"])){
				$idLink = $paramsImport["link"]["idLink"];
				$typeLink = $paramsImport["link"]["typeLink"];
				if (@$paramsImport["link"]["role"] == "admin"){
					$isAdmin = true;
				}else{
					$isAdmin = false;
				}

				if($typeLink == Organization::COLLECTION){
					Link::connect($idLink, $typeLink, $newOrganizationId, self::COLLECTION, $creatorId,"members", false);
					Link::connect($newOrganizationId, self::COLLECTION, $idLink, $typeLink, $creatorId,"memberOf",false);
				}
				else if($typeLink == Person::COLLECTION){
					Link::connect($newOrganizationId, self::COLLECTION, $idLink, Person::COLLECTION, $creatorId,"members",$isAdmin);
					Link::connect($idLink, $typeLink, $newOrganizationId, self::COLLECTION, $creatorId,"memberOf",$isAdmin);
				}
			}

			if(!empty($paramsImport["img"])){
		    	try{
		    		$paramsImg = $paramsImport["img"] ;
					$resUpload = Document::uploadDocumentFromURL(	$paramsImg["module"], self::COLLECTION, 
																	$newOrganizationId, "avatar", false, 
																	$paramsImg["url"], $paramsImg["name"]);
					if(!empty($resUpload["result"]) && $resUpload["result"] == true){
						$params = array();
						$params['id'] = $newOrganizationId;
						$params['type'] = self::COLLECTION;
						$params['moduleId'] = $paramsImg["module"];
						$params['folder'] = self::COLLECTION."/".$newOrganizationId;
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

	    //send Notification Email
	    $creator = Person::getById($creatorId);
	    //Mail::organization($creator,$organization);
	    if(isset($organization["geo"]) && !empty($organization["geo"])){
		    $orgaGeo=$organization["geo"];
	    }
	    else
	    	$orgaGeo="";
	    
	    $orgaTags= ((@$organization["tags"] && !empty($organization["tags"]))?$organization["tags"]:null);
	    
	    if (@$organization["address"]["codeInsee"] && !empty($organization["address"]["codeInsee"]))
	    	$orgaCodeInsee=$organization["address"];
	    else
	    	$orgaCodeInsee="";

		Notification::createdObjectAsParam(Person::COLLECTION,$creatorId,Organization::COLLECTION, $newOrganizationId, Person::COLLECTION,$creatorId, $orgaGeo,$orgaTags,$orgaCodeInsee);
		ActivityStream::saveActivityHistory(ActStr::VERB_CREATE, $newOrganizationId, Organization::COLLECTION, "organization", $organization["name"]);
	    $organization = Organization::getById($newOrganizationId);
	    return array("result"=>true,
		    			"msg"=>"Votre organisation est communectée.", 
		    			"id"=>$newOrganizationId, 
		    			"organization"=> $organization);
	}

	public static function newOrganizationFromPost($organization) {
		$newOrganization = array();
		if (@$organization['organizationEmail']) $newOrganization["email"] = trim($organization['organizationEmail']);
		if (@$organization['organizationName']) $newOrganization["name"] = rtrim($organization['organizationName']);
		if (@$organization['type']) $newOrganization["type"] = $organization['type'];
		
		//Location
		if (@$organization['streetAddress']) $newOrganization["streetAddress"] = rtrim($organization['streetAddress']);
		if (@$organization['postalCode']) $newOrganization["postalCode"] = $organization['postalCode'];
		if (@$organization['city']) $newOrganization["city"] = $organization['city'];
		if (@$organization['cityName']) $newOrganization["cityName"] = $organization['cityName'];
		if (@$organization['organizationCountry']) $newOrganization["addressCountry"] = $organization['organizationCountry'];

		if (@$organization['description']) $newOrganization["description"] = rtrim($organization['description']);
		if (@$organization['tagsOrganization']) $newOrganization["tags"] = $organization['tagsOrganization'];
		if (@$organization['typeIntervention']) $newOrganization["typeIntervention"] = $organization['typeIntervention'];
		if (@$organization['typeOfPublic']) $newOrganization["typeOfPublic"] = $organization['typeOfPublic'];
		if (@$organization['category']) $newOrganization["category"] = $organization['category'];
		if (@$organization['role']) $newOrganization["role"] = $organization['role'];

		//error_log("latitude : ".$organization['geoPosLatitude']);
		if(!empty($organization['geoPosLatitude']) && !empty($organization["geoPosLongitude"])){
			$newOrganization["geo"] = array("@type"=>"GeoCoordinates",
											"latitude" => $organization['geoPosLatitude'],
											"longitude" => $organization['geoPosLongitude']);

			$newOrganization["geoPosition"] = array("type"=>"Point",
															"coordinates" =>
																array(
																	floatval($organization['geoPosLongitude']),
																	floatval($organization['geoPosLatitude']))
														 	  	);
		}
		
		return $newOrganization;
	}

	public static function getAndCheckAdressOrganization($organization) {
		$trimPostalCode=trim($organization['address']['postalCode']);
		if(!empty($trimPostalCode))
		{
			$where = array("cp"=>$trimPostalCode);
			$fields = array("name", "alternateName");
	        $option = City::getWhere($where, $fields);
	        if(!empty($option))
	        {
	        	/*$findCity = false ;
		        //var_dump($organization['address']['postalCode']);
		        foreach ($option as $key => $value) {
		        	//var_dump($value['name']);
		        	//var_dump($value['alternateName']);
		        	if($organization['address']['addressLocality'] == $value['name'])
		        	{
		        		$findCity = true;
		        		$organization['address']['addressLocality'] = $value['alternateName'] ;
		        	}
		        }
		        if($findCity != true)
					throw new CTKException("Le nom de la ville n'est pas conforme.");*/
		    }
		    else
		    	throw new CTKException("Ce code postal n'existe pas.");	
	        	
	    }else{
	    	throw new CTKException("Cette organisation n'a pas de code postal.");	
		}

		return $organization ;
	}

	/** TODO : REOMVE DEPRECATED with ELement refactor
	 * Apply organization checks and business rules before inserting
	 * @param array $organization : array with the data of the organization to check
	 * @return array Organization well format : ready to be inserted
	 */
	public static function getAndCheckOrganization($organization) {
		if (empty($organization['name'])) {
			throw new CTKException(Yii::t("organization","You have to fill a name for your organization"));
		}
		
		// Is There a association with the same name ?
	    $organizationSameName = PHDB::findOne( Organization::COLLECTION,array( "name" => $organization["name"]));      
	    if($organizationSameName) { 
	      throw new CTKException(Yii::t("organization","An organization with the same name already exist in the plateform"));
	    }

		$newOrganization = array(
			"name" => $organization['name'],
			'created' => time()
		);
		
		//email : mandotory 
		if(!empty($organization['email'])) {
			//validate Email
			if (! preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#',$organization['email'])) { 
				throw new CTKException("Vous devez remplir un email valide.");
			}
			$newOrganization["email"] = $organization['email'];
		}

		if (empty($organization['type'])) {
			throw new CTKException(Yii::t("organization", "You have to fill the type of your organization"));
		}
		$newOrganization["type"] = $organization['type'];
		
		if (!@$organization["invitedBy"] && empty($organization['city'])) {
			throw new CTKException(Yii::t("organization", "You have to fill the city of your organization"));
		}


		if(!empty($organization['postalCode'])) {
			if (!empty($organization['city'])) {
				$insee = $organization['city'];
				$postalCode=$organization['postalCode'];
				$cityName= $organization['cityName'];
				$address = SIG::getAdressSchemaLikeByCodeInsee($insee,$postalCode,$cityName);
				$address["streetAddress"] = @$organization["streetAddress"];
				$address["addressCountry"] = @$organization["addressCountry"];
				$newOrganization["address"] = $address;

				if(empty($organization["geo"]))
					$newOrganization["geo"] = SIG::getGeoPositionByInseeCode($insee,$postalCode,$cityName);
				else
					$newOrganization["geo"] = $organization["geo"];
			}
		}else if(!@$organization["invitedBy"]){
			throw new CTKException(Yii::t("organization", "You have to fill the postal codes of your organization"));
		}


		//méthode pour récupérer le code insee à partir d'une position geographique :
		//$geo = SIG::getPositionByCp($organization['postalCode']);
		//$insee = SIG::getInseeByLatLngCp($geo["latitude"], $geo["longitude"], $organization['postalCode']);
				  
		

				  
		//Tags
		if (!empty($organization['tags'])) {
			if ( gettype($organization['tags']) == "array" ) {
				$tags = $organization['tags'];
			} else if ( gettype($organization['tags']) == "string" ) {
				$tags = explode(",", $organization['tags']);
			}
			$newOrganization["tags"] = $tags;
		}
		
		//category
		if (isset($organization['category'])) {
			if ( gettype($organization['category']) == "array" ) {
				$category = $organization['category'];
			} else if ( gettype($organization['category']) == "string" ) {
				$category = explode(",", $organization['category']);
			}
			$newOrganization["category"] = $category;
		}

		//************************ Import Data specific ********************/
		//ConctactPoint
		if (!empty($organization['description']))
			$newOrganization["description"] = $organization['description'];

		/*if (!empty($organization['telephone']))
			$newOrganization["telephone"] = $organization['telephone'];

		if(!empty($organization['contact'])){
			foreach ($organization['contact'] as $key => $valueContactPoint) {
				if(!empty($valueContactPoint['email'])){
					//validate Email
					if (! preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#',$valueContactPoint['email'])) { 
						throw new CTKException("Vous devez remplir un email valide pour le contactPoint ".$valueContactPoint['email'].".");
					}
				}
			}
			$newOrganization["contact"] = $organization['contact'];
		}

		//address by ImportData
		if(!empty($organization['address'])){
			$newOrganization["address"] = $organization['address'];
		}

		if(!empty($organization['creator'])){
			$newOrganization["creator"] = $organization['creator'];
		}

		if(!empty($organization['role'])){
			$newOrganization["role"] = $organization['role'];
		}

		//details by ImportData
		if(!empty($organization['details'])){
			$newOrganization["details"] = $organization['details'];
		}

		//url by ImportData
		if(!empty($organization['url'])){
			$newOrganization["url"] = $organization['url'];
		}*/


		//************************ Spécifique Granddir ********************/
		//TODO SBAR : A sortir du CTK. Prévoir une méthode populateSpecific() à appeler ici
		//Cette méthode sera implémenté dans le Modèle Organization spécifique de Granddir
		//Type of Intervention
		if (!empty($organization["typeIntervention"])) {
			$newOrganization["typeIntervention"] = $organization["typeIntervention"];
		}
	
		//Type of Intervention
		if (!empty($organization["typeOfPublic"])) {
			$newOrganization["typeOfPublic"] = $organization["typeOfPublic"];
		}


		return $newOrganization;
	}

	/**
	 * get an Organisation By Id
	 * @param type $id : is the mongoId of the organisation
	 * @return type
	 */
	public static function getById($id) {

	  	$organization = PHDB::findOne(Organization::COLLECTION,array("_id"=>new MongoId($id)));
	  	
	  	if (empty($organization)) {
            //TODO Sylvain - Find a way to manage inconsistent data
            //throw new CommunecterException("The organization id ".$id." is unkown : contact your admin");
        } else {
			$organization = array_merge($organization, Document::retrieveAllImagesUrl($id, self::COLLECTION, null, $organization));
			$organization["typeSig"] = "organizations";
        }
	  	return $organization;
	}

	/**
	 * Retrieve a simple organization (id, name, profilImageUrl) by id from DB
	 * @param String $id of the organization
	 * @return array with data id, name, profilImageUrl, logoImageUrl
	 */
	public static function getSimpleOrganizationById($id,$orga=null) {

		$simpleOrganization = array();
		if(!$orga)
			$orga = PHDB::findOneById( self::COLLECTION ,$id, array("id" => 1, "name" => 1, "type" => 1, "email" => 1, "url" => 1, "shortDescription" => 1, "description" => 1, "address" => 1, "pending" => 1, "tags" => 1, "geo" => 1, "updated" => 1, "profilImageUrl" => 1, "profilThumbImageUrl" => 1, "profilMarkerImageUrl" => 1,"profilMediumImageUrl" => 1, "addresses"=>1, "telephone"=>1) );
		if(!empty($orga)){
			$simpleOrganization["id"] = $id;
			$simpleOrganization["name"] = @$orga["name"];
			$simpleOrganization["type"] = @$orga["type"];
			$simpleOrganization["email"] = @$orga["email"];
			$simpleOrganization["url"] = @$orga["url"];
			$simpleOrganization["telephone"] = @$orga["telephone"];
			$simpleOrganization["pending"] = @$orga["pending"];
			$simpleOrganization["tags"] = @$orga["tags"];
			$simpleOrganization["geo"] = @$orga["geo"];
			$simpleOrganization["shortDescription"] = @$orga["shortDescription"];
			$simpleOrganization["description"] = @$orga["description"];
			$simpleOrganization["updated"] = @$orga["updated"];
			$simpleOrganization["addresses"] = @$orga["addresses"];
			$simpleOrganization["typeSig"] = "organizations";
			$simpleOrganization = array_merge($simpleOrganization, Document::retrieveAllImagesUrl($id, self::COLLECTION, @$orga["type"], $orga));
			
			$logo = Document::getLastImageByKey($id, self::COLLECTION, Document::IMG_LOGO);
			$simpleOrganization["logoImageUrl"] = $logo;
			
			$simpleOrganization["address"] = empty($orga["address"]) ? array("addressLocality" => Yii::t("common","Unknown Locality")) : $orga["address"];
		}
		return $simpleOrganization;
	}

	/**
	 * get members an Organization By an organization Id
	 * @param String $id : is the mongoId (String) of the organization
	 * @param String $type : can be use to filter the member by type (all (default), person, organization)
	 * @param String $role : can be use to filter the member by role (isAdmin:true)
	 * @return arrays of members (links.members)
	 */
	public static function getMembersByOrganizationId($id, $type="all",$role=null) {
	  	$res = array();
	  	$organization = Organization::getById($id);
	  	
	  	if (empty($organization)) {
            throw new CTKException(Yii::t("organization", "The organization id is unkown : contact your admin"));
        }
	  	if (isset($organization) && isset($organization["links"]) && isset($organization["links"]["members"])) {
	  		$members=array();
	  		foreach($organization["links"]["members"] as $key => $member){
	  		 	if(!@$member["toBeValidated"] && !@$member["isInviting"])
	  		 		$members[$key]= $member;
	  		}
	  		//No filter needed
	  		if ($type == "all") {
	  			return $members;
	  		} else {
	  			foreach ($organization["links"]["members"] as $key => $member) {
		            if ($member['type'] == $type ) {
		            	if(!@$member["toBeValidated"] && !@$member["isInviting"])
		            		$res[$key] = $member;	
		            }
		            if ( $role && @$member[$role] == true ) {
		            	if($role=="isAdmin"){
		            		if(!@$member["isAdminPending"])
		            			$res[$key] = $member;	
		            	} else 
		                	$res[$key] = $member;
		            }
	        	}
	  		}
	  	}
	  	return $res;
	}
	public static function getFollowersByOrganizationId($id,$organization=null) {
	  	$res = array();
	  	if(!$organization)
	  		$organization = Organization::getById($id);
	  	
	  	if (empty($organization)) {
            throw new CTKException(Yii::t("organization", "The organization id is unkown : contact your admin"));
        }
	  	if (isset($organization) && isset($organization["links"]) && isset($organization["links"]["followers"])) {
	  		$followers = $organization["links"]["followers"];
	  		//No filter needed
	  		foreach ($organization["links"]["followers"] as $key => $follower) {
		                $res[$key] = $follower;
	  		}
	  	}
	  	return $res;
	}
	/** TODO BOUBOULE - CDA : PLUS UTILISER A SUPPRIMER 
	 * update an organization in database
	 * @param String $organizationId : 
	 * @param array $organization organization fields to update
	 * @param String $userId : the userId making the update
	 * @return array of result (result => boolean, msg => string)
	 */
	public static function update($organizationId, $newOrganization, $userId) 
	{
		//Check if user is authorized to update
		$organization = self::getById($organizationId);
		if(@$organization["preferences"]["isOpenEdition"] && $organization["preferences"]["isOpenEdition"]){
			$authorization="openEdition";
		}
		else{
			if (! Authorisation::isOrganizationAdmin($userId, $organizationId)) 
				return Rest::json(array("result"=>false, "msg"=>Yii::t("organization", "Unauthorized Access.")));
			else 
				$authorization=true;
		}
		$countUpdated = 0;
		foreach ($newOrganization as $fieldName => $fieldValue) 
		{
			//TKA : optim, ne marche pas quand les fieldnames sont en profondeur ex : postalCode
			//if( @$organization[$fieldName] && $organization[$fieldName] != $fieldValue){
				self::updateField($organizationId, $fieldName, $fieldValue,$authorization);
				$countUpdated++;
			//}
		}

	    return array("result" => true, 
	    			 "msg"    => Yii::t("organization", "The organization has been updated"), 
	    			 "id"     => $organizationId,
	    			 "updatedFileds" => $countUpdated);
	}
	
	/**
	 * Happens when an Organisation is invited or linked as a member and doesn't exist in the system
	 * It is created in a temporary state
	 * This creates and invites the email to fill extra information 
	 * into the Organisation profile 
	 * @param array $param minimal information in order to create the organization
	 * @return type
	 */
	public static function createAndInvite($param) {
	  	try {
	  		$res = self::insert($param, $param["invitedBy"], $param["invitedBy"]);
	  	} catch (CTKException $e) {
	  		$res = array("result"=>false, "msg"=> $e->getMessage());
	  	}
        //TODO TIB : mail Notification 
        //for the organisation owner to subscribe to the network 
        //and complete the Organisation Profile
        
        return $res;
	}

	/**
	 * Get an organization from an id and return filter data in order to return only public data
	 * @param type $id 
	 * @return organization structure
	 */
	public static function getPublicData($id) {
		//Public datas 
		$publicData = array (
			"imagePath",
			"name",
			"city",
			"socialAccounts",
			"url",
			"coi"
		);

		//TODO SBAR = filter data to retrieve only public data	
		$organization = Organization::getById($id);
		if (empty($organization)) {
			//throw new CTKException("The organization id is unknown ! Check your URL");
		}

		return $organization;
	}

	/** TODO CDA -- FUNCTION NOT EXIST -- TAKE IN CHARGE IN LINK::CONNECTPARENTTOCHILD
	 * When an initation to join an organization network is sent :
	 * this method will :
	 * 1. Create a new person and organization.
	 * 2. Make the new person a member and admin of the organization
	 * 3. Join the network of the organization inviting
	 * @param type $person the minimal data to create a person
	 * @param type $organization the minimal data to create an organization
	 * @param type $parentOrganizationId the organization Id to join the network of
	 * @return newPersonId ans newOrganizationId
	 */
	/*public static function createPersonOrganizationAndAddMember($person, $organization, $parentOrganizationId) {
		//The data check is normaly done before inserting but the both data (organization and person)  
		//must be ok before inserting
		//Check person datas 
		Person::getAndcheckPersonData($person, false);
		//Check organization datas 
		Organization::getAndCheckOrganization($organization);
		
		//Create a new person + send email validation
		$res = Person::insert($person);
		Mail::validatePerson($res["person"]);

		//Create a new organization
		$newOrganization = Organization::insert($organization, $newPerson["id"], $newPerson["id"]);

		//Link the person as an admin
		Link::addMember($newOrganization["id"], Organization::COLLECTION, $newPerson["id"], Person::COLLECTION, $newPerson["id"], true);

		//Link the organization as a member of the invitor
		//Is the parent oragnization can manage the organizations bellow ?
		$isParentOrganizationAdmin = @Yii::app()->params['isParentOrganizationAdmin'];
		Link::addMember($parentOrganizationId, Organization::COLLECTION, $newOrganization["id"], Organization::COLLECTION, 
						$newPerson["id"], $isParentOrganizationAdmin);
		
		return array("result"=>true, "msg"=>Yii::t("organization", "The invitation process completed with success"), "id"=>$newOrganization["id"]);;
	}*/


	/**
	 * List all the event of an organization and his members (if can edit member)
	 * @param String $organisationId : is the mongoId of the organisation
	 * @return all the event link with the organization
	 */
	//TODO SBAR : Refactor using a startDate in order to not retrieve all the database
	public static function listEventsPublicAgenda($organizationId){
		$events = array();
		$organization = Organization::getById($organizationId);
		
		if(isset($organization["links"]["events"])){
			foreach ($organization["links"]["events"] as $keyEv => $valueEv) {
				 $event = Event::getPublicData($keyEv);
           		 $events[$keyEv] = $event;
			}
		}
		//Specific case : if canEditMember
		if(Authorisation::canEditMembersData($organizationId)){
			$subOrganization = Organization::getMembersByOrganizationId($organizationId, Organization::COLLECTION);
			foreach ($subOrganization as $key => $value) {
				 $newOrganization = Organization::getById($key);
				 if(!empty($newOrganization)&& isset($newOrganization["links"]["events"])){
				 	foreach ($newOrganization["links"]["events"] as $keyEv => $valueEv) {
				 		$event = Event::getPublicData($keyEv);
           		 		$events[$keyEv] = $event;
				 	}
				 }	 
			}
		}
		return $events;
	}
	/**
	 * List all the project of an organization and his members (if can edit member)
	 * @param String $organisationId : is the mongoId of the organisation
	 * @return all the project link with the organization
	 */

	public static function listProjects($organizationId){
		$projects = array();
		$organization = Organization::getById($organizationId);
		
		if(isset($organization["links"]["projects"])){
			foreach ($organization["links"]["projects"] as $keyProj => $valueProj) {
				 $project = Project::getPublicData($keyProj);
           		 $projects[$keyProj] = $project;
			}
		}
		//Specific case : if canEditMember
		if(Authorisation::canEditMembersData($organizationId)){
			$subOrganization = Organization::getMembersByOrganizationId($organizationId, Organization::COLLECTION);
			foreach ($subOrganization as $key => $value) {
				 $newOrganization = Organization::getById($key);
				 if(!empty($newOrganization)&& isset($newOrganization["links"]["projects"])){
				 	foreach ($newOrganization["links"]["projects"] as $keyProj => $valueProj) {
				 		$project = Project::getPublicData($keyProj);
           		 		$projects[$keyProj] = $project;
				 	}
				 }	 
			}
		}
		return $projects;
	}

	/**
	 * Update the roles' list of an organization
	 * @param $roleTab is an array with all the roles
	 * @param type $organisationId : is the mongoId of the organisation
	 */
	public static function setRoles($roleTab, $organizationId){
		PHDB::update( Organization::COLLECTION,
						array("_id" => new MongoId($organizationId)), 
                        array('$set' => array( 'roles' => $roleTab))
                    );
	}

	 /**
	 * Update an organization field value
	 * @param String $organisationId The organization Id to update
	 * @param String $organizationFieldName The name of the field to update
	 * @param String $organizationFieldValue the value of the field
	 * @param String $userId the user Id must be admin of the organization to update it
	 * @return boolean True if the update has been done correctly. Can throw CTKException on error.
	 */
	 public static function updateOrganizationField($organizationId, $organizationFieldName, $organizationFieldValue, $userId){
	 	$pref = Preference::getPreferencesByTypeId($organizationId, self::COLLECTION);
	 	$authorization = Preference::isOpenEdition($pref);
	 	if($authorization == false){
		 	$authorization=Authorisation::canEditItem($userId, self::COLLECTION, $organizationId);
		 	if (!$authorization) {
				return Rest::json(array("result"=>false, "msg"=>Yii::t("organization", "Unauthorized Access.")));
			}
		}
		$res = self::updateField($organizationId, $organizationFieldName, $organizationFieldValue, $authorization);
	                  
	    return $res;
	}

	private static function updateField($organizationId, $organizationFieldName, $organizationFieldValue, $authorization=false) {
		$dataFieldName = Organization::getCollectionFieldNameAndValidate($organizationFieldName, $organizationFieldValue, $organizationId);
		$set = array($dataFieldName => $organizationFieldValue);

		//Specific case : 
		//Tags
		

		if ($dataFieldName == "tags") {
			$organizationFieldValue = Tags::filterAndSaveNewTags($organizationFieldValue);
			$set = array($dataFieldName => $organizationFieldValue);
		}
		else if ( ($organizationFieldName == "mobile"|| $organizationFieldName == "fixe" || $organizationFieldName == "fax")){
			if($organizationFieldValue ==null)
				$organizationFieldValue = array();
			else
				$organizationFieldValue = explode(",", $organizationFieldValue);

			$set = array($dataFieldName => $organizationFieldValue);
		}
		else if ($dataFieldName == "address") {
		//address
			if(!empty($organizationFieldValue["postalCode"]) && !empty($organizationFieldValue["codeInsee"])) {
				$insee = $organizationFieldValue["codeInsee"];
				$postalCode = $organizationFieldValue["postalCode"];
				$cityName = $organizationFieldValue["addressLocality"];
				$address = SIG::getAdressSchemaLikeByCodeInsee($insee, $postalCode,$cityName);
				$set = array("address" => $address);
				if (!empty($organizationFieldValue["streetAddress"]))
					$set["address"]["streetAddress"] = $organizationFieldValue["streetAddress"];
				if(empty($organizationFieldValue["geo"]))
					$set["geo"] = SIG::getGeoPositionByInseeCode($insee, $postalCode,$cityName);
			} else {
				throw new CTKException("Error updating the Organization : address is not well formated !");			
			}
		}
		//update the organization
		$set["modified"] = new MongoDate(time());
		$set["updated"] = time();
		PHDB::update( Organization::COLLECTION, array("_id" => new MongoId($organizationId)), 
		                          array('$set' => $set));

		if($authorization == "openEdition" && $dataFieldName != "badges"){
			// Add in activity to show each modification added to this entity
			//echo $dataFieldName;
			ActivityStream::saveActivityHistory(ActStr::VERB_UPDATE, $organizationId, Organization::COLLECTION, $dataFieldName, $organizationFieldValue);
		}
			

		return true;
	}

	/** TODO CDA -- TO DELETE Link::ConnectPArentToChild do it
	 * Add someone as admin of an organization.
	 * If there are already admins of the organization, they will receive a notification and email to 
	 * accept or not the new admin
	 * @param String $idOrganization The id of the organization
	 * @param String $idPerson The id of the person asking to become an admin
	 * @param String $userId The userId doing the action
	 * @return array of result (result => bool, msg => string)
	 */
	/*public static function addPersonAsAdmin($idOrganization, $idPerson, $userId) {
		$res = array("result" => true, "msg" => "You are now admin of the organization");

		$organization = self::getById($idOrganization);
		$pendingAdmin = Person::getById($idPerson);
		
		if (!$organization || !$pendingAdmin) {
			return array("result" => false, "msg" => "Unknown organization or person. Please check your parameters !");
		}
		//First case : The organization doesn't have an admin yet : the person is automatically added as admin
		$usersAdmin = Authorisation::listOrganizationAdmins($idOrganization, false);
		if (in_array($idPerson, $usersAdmin)) 
			return array("result" => false, "msg" => "Your are already admin of this organization !");

		if (count($usersAdmin) == 0) {
			Link::addMember($idOrganization, self::COLLECTION, $idPerson, Person::COLLECTION, $userId, true, "", false);
			Notification::actionOnPerson ( ActStr::VERB_JOIN, ActStr::ICON_SHARE, $pendingAdmin , array("type"=>Organization::COLLECTION,"id"=> $idOrganization,"name"=>$organization["name"]) ) ;
		} else {
			//Second case : there is already an admin (or few) 
			// 1. Admin link will be added but pending
			Link::addMember($idOrganization, self::COLLECTION, $idPerson, Person::COLLECTION, $userId, true, "", true);
			Notification::actionOnPerson ( ActStr::VERB_JOIN, ActStr::ICON_SHARE, $pendingAdmin , array("type"=>Organization::COLLECTION,"id"=> $idOrganization,"name"=>$organization["name"]) ) ;
			// 2. Notification and email are sent to the admin(s)
			$listofAdminsEmail = array();
			foreach ($usersAdmin as $adminId) {
				$currentAdmin = Person::getSimpleUserById($adminId);
				array_push($listofAdminsEmail, $currentAdmin["email"]);
			}
			Mail::someoneDemandToBecomeAdmin($organization, $pendingAdmin, $listofAdminsEmail);
			//TODO - Notification
			$res = array("result" => true, "msg" => "Your request has been sent to other admins.");
			// After : the 1rst existing Admin to take the decision will remove the "pending" to make a real admin
		}

		return $res;
	}*/




public static function newOrganizationFromImportData($organization, $emailCreator=null, $warnings=null) {
		//var_dump($organization);
		$newOrganization = array();
		/*if(!empty($organization['email']))
			$newOrganization["email"] = $organization['email'];*/

		$newOrganization["email"] = empty($organization["email"]) ? $emailCreator : $organization["email"];

		if(!empty($organization['name']))
			$newOrganization["name"] = $organization['name'];

		if(!empty($organizationorganization['source'])){
			if(!empty($organization['source']['id']))
				$newOrganization["source"]['id'] = $organization["source"]['id'];
			if(!empty($organization['source']['url']))
				$newOrganization["source"]['url'] = $organization["source"]['url'];
			$organization["source"]['key'] = "patapouf";
			if(!empty($organization['source']['key']))
				$newOrganization["source"]['key'] = $organization["source"]['key'];
			if(!empty($organization['source']['update']))
				$newOrganization["source"]['update'] = $organization["source"]['update'];
		}

		if(!empty($organization['warnings']))
			$newOrganization["warnings"] = $organization["warnings"];

		if(empty($organization['type'])){
			$newOrganization["type"] = Organization::TYPE_GROUP ;
			//$newOrganization["warnings"][] = "212" ;
			
		}else{
			//$newOrganization["type"] = $organization['type'];
			if(trim($organization['type']) == "Association")
				$newOrganization["type"] = Organization::TYPE_NGO ;
			else if(trim($organization['type']) == "Groupe gouvernemental")
				$newOrganization["type"] = Organization::TYPE_GOV ;
			else if(trim($organization['type']) == "Entreprise")
				$newOrganization["type"] = Organization::TYPE_BUSINESS ;
			else
				$newOrganization["type"] = Organization::TYPE_GROUP ;

		}
			
		
		if(!empty($organization['description']))
			$newOrganization["description"] = $organization['description'];

		$newOrganization["role"] = empty($organization['role']) ? "" : $organization['role'];
		$newOrganization["creator"] = empty($organization['creator']) ? "" : $organization['creator'];
		
		

		if(!empty($organization['url']))
			$newOrganization["url"] = empty($organization['url']) ? "" : $organization['url'];


		if(!empty($organization['tags']))
		{	
			$tags = array();
			foreach ($organization['tags'] as $key => $value) {
				$trimValue=trim($value);
				if(!empty($trimValue))
					$tags[] = $trimValue;
			}
			$newOrganization["tags"] = $tags;
		}

		if(!empty($organization['telephone']))
		{
			$tel = array();
			$fixe = array();
			$mobile = array();
			$fax = array();
			if(!empty($organization['telephone']["fixe"]))
			{
				foreach ($organization['telephone']["fixe"] as $key => $value) {
					$trimValue=trim($value);
					if(!empty($trimValue))
						$fixe[] = $trimValue;
				}
			}
			if(!empty($organization['telephone']["mobile"]))
			{
				foreach ($organization['telephone']["mobile"] as $key => $value) {
					$trimValue=trim($value);
					if(!empty($trimValue))
						$mobile[] = $trimValue;
				}
			}

			if(!empty($organization['telephone']["fax"]))
			{
				foreach ($organization['telephone']["fax"] as $key => $value) {
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
				$newOrganization['telephone'] = $tel;
		}
		
		if(!empty($organization['contacts'])){
			$contact = array();
			foreach ($organization['contacts'] as $keyContact => $valueContact) {
				$unContact = array();
				foreach ($valueContact as $key => $value) {
					if(is_array($value)){
						$arrayName = array();
						foreach ($value as $keyArray => $valueArray) {
							$trimValue=trim($valueArray);
							if(!empty($trimValue))
								$arrayName[] = $trimValue ;
						}
						if(count($arrayName) != 0)
							$unContact[$key] = $arrayName;
					}
					else{
						$trimValue=trim($value);
						if(!empty($trimValue))
							$unContact[$key] = $trimValue ;	
					}
				}
				if(count($unContact) != 0)
					$contact[] = $unContact;
			}
			if(count($contact) != 0)	
				$newOrganization['contacts'] = $contact;
		}

		

		if(!empty($organization['source']))
			$newOrganization["source"] = $organization["source"];
		
		
		$address = (empty($organization['address']) ? null : $organization['address']);
		$geo = (empty($organization['geo']) ? null : $organization['geo']);
		//var_dump($newOrganization['name']);
		$details = Import::getAndCheckAddressForEntity($address, $geo, $warnings) ;
		$newOrganization['address'] = $details['address'];

		if(!empty($details['geo']))
			$newOrganization['geo'] = $details['geo'] ;

		if(!empty($details['geoPosition']))
			$newOrganization['geoPosition'] = $details['geoPosition'] ;

		if(!empty($organization['urlFacebook']))
			$newOrganization['urlFacebook'] = $organization['urlFacebook'] ;

		if(!empty($organization['urlTwitter']))
			$newOrganization['urlTwitter'] = $organization['urlTwitter'] ;

		if(!empty($newOrganization['warnings']))
			$newOrganization['warnings'] = array_merge($newOrganization['warnings'], $details['warnings']);
		else
			$newOrganization['warnings'] = $details['warnings'];

		if(!empty($organization['citizenType']))
			$newOrganization["citizenType"] = $organization['citizenType'];

		

		return $newOrganization;
	}


	/**
	 * Apply organization checks and business rules before inserting
	 * @param array $organization : array with the data of the organization to check
	 * @return array Organization well format : ready to be inserted
	 */					   
	public static function getAndCheckOrganizationFromImportData($organization, $insert=null, $update=null, $warnings = null) {
		$newOrganization = array();
		
		$newOrganization = array();
		if (empty($organization['name'])) {
			if($warnings)
				$newOrganization["warnings"][] = "001" ;
			else
				throw new CTKException(Yii::t("import","001"));
		}else
			$newOrganization['name'] = $organization['name'];
		
		$newEvent['created'] = new MongoDate(time()) ;
		
		if(!empty($organization['email'])) {
			if (! preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#',$organization['email'])) { 
				if($warnings)
					$newOrganization["warnings"][] = "205" ;
				else
					throw new CTKException(Yii::t("import","205", null, Yii::app()->controller->module->id));
			}
			$newOrganization["email"] = $organization['email'];
		}

		if(empty($organization['type'])) {
			if($warnings){
				//$newOrganization["warnings"][] = "208" ;
				$newOrganization["type"] = self::TYPE_GROUP ;
			}	
			else
				throw new CTKException("208");
		}else{
			$newOrganization["type"] = $organization['type'];
		}
			  
		
		if(!empty($organization['address'])) {
			if(empty($organization['address']['postalCode']) /*&& $insert*/){
				if($warnings)
					$newOrganization["warnings"][] = "101" ;
				else
					throw new CTKException(Yii::t("import","101", null, Yii::app()->controller->module->id));
			}
			if(empty($organization['address']['codeInsee'])/*&& $insert*/){
				if($warnings)
					$newOrganization["warnings"][] = "102" ;
				else{
					throw new CTKException(Yii::t("import","102", null, Yii::app()->controller->module->id));
				}
					
			}
			if(empty($organization['address']['addressCountry']) /*&& $insert*/){
				if($warnings)
					$newOrganization["warnings"][] = "104" ;
				else
					throw new CTKException(Yii::t("import","104", null, Yii::app()->controller->module->id));
			}
			if(empty($organization['address']['addressLocality']) /*&& $insert*/){
				if($warnings)
					$newOrganization["warnings"][] = "105" ;
				else
					throw new CTKException(Yii::t("import","105", null, Yii::app()->controller->module->id));
			}
			$newOrganization['address'] = $organization['address'] ;

		}else {
			if($warnings)
				$newOrganization["warnings"][] = "100" ;
			else
				throw new CTKException(Yii::t("import","100", null, Yii::app()->controller->module->id));
		}
		
		if(!empty($organization['geo']) && !empty($organization["geoPosition"])){
			$newOrganization["geo"] = $organization['geo'];
			$newOrganization["geoPosition"] = $organization['geoPosition'];

		}else if(!empty($organization["geo"]['latitude']) && !empty($organization["geo"]["longitude"])){
			$newOrganization["geo"] = 	array(	"@type"=>"GeoCoordinates",
						"latitude" => $organization["geo"]['latitude'],
						"longitude" => $organization["geo"]["longitude"]);

			$newOrganization["geoPosition"] = array("type"=>"Point",
													"coordinates" =>
														array(
															floatval($organization["geo"]['longitude']),
															floatval($organization["geo"]['latitude']))
												 	  	);
		}
		else if($insert){
			if($warnings)
				$newOrganization["warnings"][] = "150" ;
			else
				throw new CTKException(Yii::t("import","150", null, Yii::app()->controller->module->id));
		}else if($warnings)
			$newOrganization["warnings"][] = "150" ;
			
		
		if (isset($organization['tags'])) {
			if ( gettype($organization['tags']) == "array" ) {
				//$tags = TextHelper::createHashTag($organization['tags']);
				foreach ($organization['tags'] as $key => $tag) {
					$tags[] = TextHelper::createHashTag($tag);
				}
			} else if ( gettype($organization['tags']) == "string" ) {
				$arrayTags = explode(",", $organization['tags']);
				foreach ($arrayTags as $key => $tag) {
					$tags[] = TextHelper::createHashTag($tag);
				}
			}
			$newOrganization["tags"] = $tags;
		}
		
		//category
		if (isset($organization['category'])) {
			if ( gettype($organization['category']) == "array" ) {
				$category = $organization['category'];
			} else if ( gettype($organization['category']) == "string" ) {
				$category = explode(",", $organization['category']);
			}
			$newOrganization["category"] = $category;
		}

		if (!empty($organization['description']))
			$newOrganization["description"] = $organization['description'];

		if (!empty($organization['telephone']))
			$newOrganization["telephone"] = $organization['telephone'];

		if(!empty($organization['contacts'])){
			foreach ($organization['contacts'] as $key => $valueContactPoint) {
				if(!empty($valueContactPoint['email'])){
					//validate Email
					if (! preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#',$valueContactPoint['email'])) { 
						if($warnings)
							$newOrganization["warnings"][] = "209" ;
						else
							throw new CTKException(Yii::t("import","209", null, Yii::app()->controller->module->id));
					}
				}
			}
			$newOrganization["contacts"] = $organization['contacts'];
		}

		if(!empty($organization['creator'])){
			$newOrganization["creator"] = $organization['creator'];
		}

		if(!empty($organization['role'])){
			$newOrganization["role"] = $organization['role'];
		}

		if(!empty($organization['source'])){
			$newOrganization["source"] = $organization['source'];
		}

		//details by ImportData
		if(!empty($organization['details'])){
			$newOrganization["details"] = $organization['details'];
		}
		if(!empty($organization['citizenType'])){
			$newOrganization["citizenType"] = $organization['citizenType'];
		}

		//url by ImportData
		if(!empty($organization['url'])){
			$newOrganization["url"] = $organization['url'];
		}
		
		if(!empty($organization['urlFacebook']))
			$newOrganization['urlFacebook'] = $organization['urlFacebook'] ;

		if(!empty($organization['urlTwitter']))
			$newOrganization['urlTwitter'] = $organization['urlTwitter'] ;

		if (!empty($organization['warnings'])){
			if (!empty($newOrganization['warnings'])){
				$newOrganization['warnings'] = array_merge($newOrganization['warnings'], $organization['warnings']);
			}else{
				$newOrganization["warnings"] = $organization['warnings'];
			}

			if(count($newOrganization["warnings"]) == 1 && in_array("212", $newOrganization["warnings"]))
				$newOrganization["state"] = "";
			else
				$newOrganization["state"] = "uncomplete";
		}

		if (!empty($organization['image']))
			$newOrganization["image"] = $organization['image'];

		if (!empty($organization['properties']))
			$newOrganization["properties"] = $organization['properties'];

		/*if(!empty($organization['source']['id']) ){
			$id = $organization['source']['id'] ;
			if($id >= "8025" &&  $id <= "8152"){
				throw new CTKException(Yii::t("organization","Projet Amaury"));
			}
			if($id >= "8169" &&  $id <= "11686"){
				throw new CTKException(Yii::t("organization","Projet ImaginationForPeople"));
			}
		}*/

		// Is There a association with the same name ?
		if(!empty($newOrganization["address"]["codeInsee"]) && $update == null){
			$organizationSameName = PHDB::findOne( Organization::COLLECTION,array( "name" => trim($organization["name"]), "address.codeInsee" => $newOrganization["address"]["codeInsee"]));      
		    if($organizationSameName) { 
		      throw new CTKException(Yii::t("organization","An organization with the same name already exist in the plateform"));
		    }
		}
	    
		return $newOrganization;
	}


	/**
	 * insert a new organization in database From ImportDATA
	 * @param array A well format organization 
	 * @param String $creatorId : an existing user id representing the creator of the organization
	 * @param String $adminId : can be ommited. user id representing the administrator of the organization
	 * @return array result as an array. 
	 */
	public static function insertOrganizationFromImportData($organization, $creatorId, $warnings = null, $pathFolderImage = null, $moduleId = null, $paramsLink = null){
	    
	    $newOrganization = Organization::getAndCheckOrganizationFromImportData($organization, true, null, $warnings);
		
		if (isset($newOrganization["tags"]))
			$newOrganization["tags"] = Tags::filterAndSaveNewTags($newOrganization["tags"]);

		//Add the user creator of the organization in the system
		if (empty($creatorId)) {
			throw new CTKException("The creator of the organization is required.");
		} else {
			$newOrganization["creator"] = $creatorId;	
		}
		
		if(empty($newOrganization["preferences"])){
			$newOrganization["preferences"] = array("publicFields" => array(), "privateFields" => array(), "isOpenData"=>true, "isOpenEdition"=>true);
		}

		if(!empty($newOrganization["image"])){
			$nameImage = $newOrganization["image"];
			unset($newOrganization["image"]);
		}
	
		//Insert the organization
	    PHDB::insert( Organization::COLLECTION, $newOrganization);
		
	    if (isset($newOrganization["_id"])) {
	    	$newOrganizationId = (String) $newOrganization["_id"];

	    	if(!empty($nameImage)){
		    	try{
					$res = Document::uploadDocumentFromURL($moduleId, self::COLLECTION, $newOrganizationId, "avatar", false, $pathFolderImage, $nameImage);
					if(!empty($res["result"]) && $res["result"] == true){
						$params = array();
						$params['id'] = $newOrganizationId;
						$params['type'] = self::COLLECTION;
						$params['moduleId'] = $moduleId;
						$params['folder'] = self::COLLECTION."/".$newOrganizationId;
						$params['name'] = $res['name'];
						$params['author'] = Yii::app()->session["userId"] ;
						$params['size'] = $res["size"];
						$params["contentKey"] = "profil";
						$res2 = Document::save($params);
						if($res2["result"] == false)
							throw new CTKException("Impossible de sauvegarder l'image.");

					}else{
						throw new CTKException("Impossible uploader l'image.");
					}
				}catch (CTKException $e){
					throw new CTKException($e);
				}	
			}

			if(!empty($paramsLink) && $paramsLink["link"] == true){
				if($paramsLink["typeLink"] == "Organization"){
					Link::connect($paramsLink["idLink"], Organization::COLLECTION, $newOrganizationId, self::COLLECTION, $creatorId,"members", false);
					Link::connect($newOrganizationId, self::COLLECTION, $paramsLink["idLink"], Organization::COLLECTION, $creatorId,"memberOf",false);
				}
				if($paramsLink["typeLink"] == "Person"){
					Link::connect($newOrganizationId, Organization::COLLECTION, $paramsLink["idLink"], Person::COLLECTION, $creatorId,"members",$paramsLink["isAdmin"]);
					Link::connect($paramsLink["idLink"], Person::COLLECTION, $newOrganizationId, Organization::COLLECTION, $creatorId,"memberOf",$paramsLink["isAdmin"]);
				}
			}


			$newOrganization = Organization::getById($newOrganizationId);





	    } else {
	    	throw new CTKException(Yii::t("organization","Problem inserting the new organization"));
	    }


	    
	    return array("result"=>true,
		    			"msg"=>"Votre organisation est communectée.", 
		    			"id"=>$newOrganizationId, 
		    			"newOrganization"=> $newOrganization);
	}

    public static function getFollowsByOrganizationId($id) {
	  	$res = array();
	  	$organization = Organization::getById($id);
	  	
	  	if (empty($organization)) {
            throw new CTKException(Yii::t("organization", "The organization id is unkown : contact your admin"));
        }
	  	if (isset($organization) && isset($organization["links"]) && isset($organization["links"]["follows"])) {
	  		$followers = $organization["links"]["follows"];
	  		//No filter needed
	  		foreach ($organization["links"]["follows"] as $key => $follower) {
		                $res[$key] = $follower;
	  		}
	  	}
	  	return $res;
	}

	public static function getQuestionAnwser($organization){
		if(!empty($organization["tags"])){
			if(in_array("commun", $organization['tags']) || in_array("fabmob", $organization['tags'])){
				$url = "http://data.patapouf.org".$organization["source"]["url"];
				$res = Import::getDataByUrl($url);
				$json = json_decode($res, true);
				if(!empty($json["question_answers"])){
					foreach ($json["question_answers"] as $key => $value) {
						$qt["key"] = $value["question"]["slug"] ;
						$qt["description"] = $value["answer"] ;
						$qt["value"] = -1 ;
						$organization["properties"]["chart"][] = $qt;
					}

				}
			}
		}


		return $organization ;
	}


	public static function getDataBinding() {
	  	return self::$dataBinding;
	}

	public static function checkType($type) {
		$type = self::translateType($type);
		$types = array(self::TYPE_NGO, self::TYPE_BUSINESS, self::TYPE_GROUP, self::TYPE_GOV);
		$result = (in_array($type, $types)?true:false);
	  	return $result;
	}

	public static function translateType($type) {
		if(trim($type) == "Association")
			$type = self::TYPE_NGO ;
		else if(trim($type) == "Groupe Gouvernemental" || trim($type) == "Groupe gouvernemental")
			$type = self::TYPE_GOV ;
		else if(trim($type) == "Entreprise")
			$type = self::TYPE_BUSINESS ;
		else if(trim($type) == "Groupe" || trim($type) == "Groupe informel")
			$type = self::TYPE_GROUP ;
		return $type;
	}

	public static function getKeyTypeByValue($type) {
		foreach (self::$types as $key => $value) {
			if($type == $value)
				return $key;
		}
	  	return false;
	}
	

}
?>