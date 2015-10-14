<?php 
class Event {
	const COLLECTION = "events";
	const CONTROLLER = "event";
	const ICON = "fa-calendar";


	//From Post/Form name to database field name
	private static $dataBinding = array(
	    "name" => array("name" => "name", "rules" => array("required")),
	    "type" => array("name" => "type"),
	    "streetAddress" => array("name" => "address.streetAddress"),
	    "postalCode" => array("name" => "address.postalCode"),
	    "city" => array("name" => "address.codeInsee"),
	    "addressLocality" => array("name" => "address.addressLocality"),
	    "addressCountry" => array("name" => "address.addressCountry"),
	    "description" => array("name" => "description"),
	    "allDay" => array("name" => "allDay"),
	    "startDate" => array("name" => "startDate", "rules" => array("eventStartDate")),
	    "endDate" => array("name" => "endDate", "rules" => array("eventEndDate"))
	);

	//TODO SBAR - First test to validate data. Move it to DataValidator
  	private static function getCollectionFieldNameAndValidate($eventFieldName, $eventFieldValue, $eventId) {
		$res = "";
		if (isset(self::$dataBinding["$eventFieldName"])) 
		{
			$data = self::$dataBinding["$eventFieldName"];
			$name = $data["name"];
			//Validate field
			if (isset($data["rules"])) 
			{
				$rules = $data["rules"];
				foreach ($rules as $rule) {
					$isDataValidated = DataValidator::$rule($eventFieldValue, $eventId);
					if ($isDataValidated != "") {
						throw new CTKException($isDataValidated);
					}
				}	
			}
		} else {
			throw new CTKException("Unknown field :".$eventFieldName);
		}
		return $name;
	}

	/**
	 * get an event By Id
	 * @param type $id : is the mongoId of the event
	 * @return type
	 */
	public static function getById($id) {
		$event = PHDB::findOne( PHType::TYPE_EVENTS,array("_id"=>new MongoId($id)));
		if (!empty($event["startDate"]) && !empty($event["endDate"])) {
			if (gettype($event["startDate"]) == "object" && gettype($event["endDate"]) == "object") {
				//Set TZ to UTC in order to be the same than Mongo
				date_default_timezone_set('UTC');
				$event["startDate"] = date('Y-m-d H:i:s', $event["startDate"]->sec);
				$event["endDate"] = date('Y-m-d H:i:s', $event["endDate"]->sec);	
			} else {
				//Manage old date with string on date event
				$now = time();
				$yesterday = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
				$yester2day = mktime(0, 0, 0, date("m")  , date("d")-2, date("Y"));
				$event["endDate"] = date('Y-m-d H:i:s', $yesterday);
				$event["startDate"] = date('Y-m-d H:i:s',$yester2day);;
			}
		}
		$event = array_merge($event, Document::retrieveAllImagesUrl($id, self::COLLECTION));

	  	return $event;
	}

	/**
	 * Retrieve a simple event (id, name, type profilImageUrl) by id from DB
	 * @param String $id of the event
	 * @return array with data id, name, type profilImageUrl
	 */
	public static function getSimpleEventById($id) {
		
		$simpleEvent = array();
		$event = PHDB::findOneById( self::COLLECTION ,$id, array("id" => 1, "name" => 1, "type" => 1, "address" => 1) );

		$simpleEvent["id"] = $id;
		$simpleEvent["name"] = @$event["name"];
		$simpleEvent["type"] = @$event["type"];
		$simpleEvent = array_merge($simpleEvent, Document::retrieveAllImagesUrl($id, self::COLLECTION));
		
		$simpleEvent["address"] = empty($event["address"]) ? array("addressLocality" => "Unknown") : $event["address"];
		
		return $simpleEvent;
	}

	public static function getWhere($params) 
	{
	  	$events =PHDB::findAndSort( self::COLLECTION,$params,array("created"),null);
	  	
	  	return Event::addInfoEvents($events);
	}

	/**
	 * Get an event from an id and return filter data in order to return only public data
	 * @param type $id 
	 * @return event structure
	 */
	public static function getPublicData($id) {
		//Public datas 
		$publicData = array (
		);

		//TODO SBAR = filter data to retrieve only publi data	
		$event = Event::getById($id);
		if (empty($event)) {
			throw new CTKException("The event id is unknown ! Check your URL");
		}

		return $event;
	}

	/**
	 * Check the data of an event
	 * @param array $event array of event data
	 * @return true if the event is well format else throw exception if not
	 */
	public static function checkEventData($event) {
		//Title is mandatory
		if (empty($event["name"])) {
			throw new CTKException("The event Title is required.");
		}

		//The organizer is required and should exist
		if (empty($event["organizerId"])) {
			throw new CTKException("You must select an organizer");
		} 
		/*$organizer = Organization::getById($event["organization"]);
		if (empty($organizer)) {
			throw new CTKException("The organizer does not exist. Please check the organizer.");
		}*/

		if(empty($event['startDate']) || empty($event['endDate'])) {
			throw new CTKException("The start and end date of an event are required.");
		}
		
		if (! empty($event['allDay'])) {
			$allDay = $event['allDay'] == 'true' ? true : false;
 		} else {
			throw new CTKException("You must specify if the event is during all day or not.");
		}

		//The end datetime must be after start datetime
		$startDate = strtotime($event['startDate']);
		$endDate = strtotime($event['endDate']);
		if ($startDate >= $endDate) {
			//Special case when it's an allday event the startDate and endDate could be equals
			if (!($startDate == $endDate && $allDay)) {
				throw new CTKException("The start date must be before the end date.");
			}
		}
	}

	/**
	 * Save an event from Post. Check if it is well format.
	 * @param type POST
	 * @return save the event
	*/
	public static function saveEvent($params) {
		
		self::checkEventData($params);
		$allDay = $params['allDay'] == 'true' ? true : false;
	    $newEvent = array(
			"name" => $params['name'],
			'type' => $params['type'],
			'public' => true,
			'created' => time(),
			"startDate" => new MongoDate(strtotime($params['startDate'])),
			"endDate" => new MongoDate(strtotime($params['endDate'])),
	        "allDay" => $allDay,
	        'creator' => $params['userId'],
	    );
	    
	    //Postal code & geo
	    if(!empty($params['postalCode'])) {
			if (!empty($params['city'])) {
				$insee = $params['city'];
				$address = SIG::getAdressSchemaLikeByCodeInsee($insee);
				$newEvent["address"] = $address;
			}
		}
		
		
		if(!empty($params['geoPosLatitude']) && !empty($params["geoPosLongitude"])){
			

			$newEvent["geo"] = 	array(	"@type"=>"GeoCoordinates",
						"latitude" => $params['geoPosLatitude'],
						"longitude" => $params['geoPosLongitude']);

			$newEvent["geoPosition"] = 
				array(	"type"=>"point",
						"coordinates" =>
							array($params['geoPosLatitude'],
					 	  		  $params['geoPosLongitude']));
		}else
		{
			$newEvent["geo"] = SIG::getGeoPositionByInseeCode($insee);
		}
	    //sameAs      
	    if(!empty($params['description']))
	         $newEvent["description"] = $params['description'];
	    
	    PHDB::insert(self::COLLECTION,$newEvent);
	    
	    //add the creator as the admin and the first attendee
	    Link::attendee($newEvent["_id"], $params['userId'], true);

	    Link::addOrganizer($params["organizerId"],$params["organizerType"], $newEvent["_id"], $params['userId']);
		
		Notification::createdEvent($params["organizerType"], $params["organizerId"], $newEvent["_id"], $newEvent["name"],$newEvent["geo"],$newEvent["type"], $params['userId']);
	    //send validation mail
	    //TODO : make emails as cron events
	    /*$message = new YiiMailMessage; 
	    $message->view = 'validation';
	    $message->setSubject('Confirmer votre compte Pixel Humain');
	    $message->setBody(array("user"=>$new["_id"]), 'text/html');
	    $message->addTo("oceatoon@gmail.com");//$params['registerEmail']
	    $message->from = Yii::app()->params['adminEmail'];
	    Yii::app()->mail->send($message);*/
	    $creator = Person::getById($params['userId']);
	    Mail::newEvent($creator,$newEvent);
	    
	    //TODO : add an admin notification
	    //Notification::saveNotification(array("type"=>NotificationType::ASSOCIATION_SAVED,"user"=>$new["_id"]));
	    
	    return array("result"=>true, "msg"=>"Votre evenement est communecté.", "id"=>$newEvent["_id"], "event" => $newEvent );
	}

	/**
	 * Retrieve the list of events, the organization is organizer
	 * Special case : when the organization can edit member data : retireve the events of the members
	 * The event should not be over
	 * The event of the organization $organizationId will be selected first
	 * @param String $organizationId The organization Id
	 * @param int $limit limit of the result
	 * @return array list of the events the organization is part of the organization sorted on endDate
	 */
	public static function getListCurrentEventsByOrganizationId($organizationId, $limit = 20) {
		$listEvent = array();
		$where = array 	('$and' => array (
							array("links.organizer.".$organizationId => array('$exists' => true)),
							array("endDate" => array('$gte' => new MongoDate(time())))
						));
        $eventOrganization = PHDB::findAndSort(self::COLLECTION, $where, array('endDate' => 1), $limit);

        //If not enougth events, lets see for canEditMember
        if (count($eventOrganization) < $limit) {
        	if(Authorisation::canEditMembersData($organizationId)) {
				$subOrganization = Organization::getMembersByOrganizationId($organizationId, Organization::COLLECTION);
				foreach ($subOrganization as $key => $value) {
					//Recursive call : yes papa !!!
					$subOrgaEvents = self::getListCurrentEventsByOrganizationId($key, $limit - count($eventOrganization));
					foreach ($subOrgaEvents as $keyEvent => $valueEvent) {
						$eventOrganization[$keyEvent] = $valueEvent;
					}
					if (count($eventOrganization) >= $limit) break;
				}
			}
        }

        return Event::addInfoEvents($eventOrganization);
	}

	/**
	* @param List of field, of an event (name, organisation, dates ....)
	* @return true is event existing, false else
	*/
	public static function checkExistingEvents($params){
		$res = false;
		$events = PHDB::find(PHType::TYPE_EVENTS,array( "name" => $params['name']));
		if(!$events){
			$res = false;
		}else{
			foreach ($events as $key => $value) {
				if(isset($params["organization"])){
					if(isset($value["links"]["organizer"])){
						foreach ($value["links"]["organizer"] as $keyEv => $valueEv) {
							if($keyEv==$params["organization"]){
								$startDate = explode(" ", $value["startDate"]);
								$start = explode(" ", $params["start"]);
								if( $startDate[0] == $start[0]){
									$res = true;
								}
							}
						}
					}
				}
				else if(isset($params["userId"])){
					foreach ($value["links"]["attendees"] as $keyEv => $valueEv) {
						if($keyEv==$params["userId"] && isset($params["start"])){
							$startDate = explode(" ", $value["startDate"]);
							$start = explode(" ", $params["start"]);
							if( $startDate[0] == $start[0]){
								$res = true;
							}
						}
					}
				}
			}
		}
		return $res;
	}


	/**
	 * Retrieve the list of events that an user is attending of
	 * @param String $userId is the id of a citoyen
	 * @return array list of the events the person
	 */
	public static function listEventAttending($userId){
		$where = array("links.attendees.".$userId => array('$exists' => true));
		$listEventAttending= array();
		$eventsAttending = PHDB::find(PHType::TYPE_EVENTS, $where);
		foreach ($eventsAttending as $key => $value) {
        	$profil = Document::getLastImageByKey($key, PHType::TYPE_EVENTS, Document::IMG_PROFIL);
        	if(strcmp($profil, "")!= 0){
        		$value['imagePath']=$profil;
        	}
        	$listEventAttending[$key] = $value;
        }
        return $listEventAttending;
	}


	public static function delete($eventId, $userId){
		if (! Authorisation::isEventAdmin($eventId, $userId)) {
			throw new CTKException("Can not delete the event : you are not authorized to delete that event!");	
		}

		$res = Link::removeEventLinks($eventId);
		
		if($res["ok"]==1){
    		$res = PHDB::remove(self::COLLECTION, array("_id"=>new MongoId($eventId)));
    	}
		
		return array("result"=>true, "msg"=>"Votre evenement est supprimé.", "id"=>$eventId);
	}


	public static function updateEventField($eventId, $eventFieldName, $eventFieldValue, $userId){

		if (! Authorisation::isEventAdmin($eventId, $userId)) {
			throw new CTKException("Can not update the event : you are not authorized to update that event!");	
		}

		$dataFieldName = self::getCollectionFieldNameAndValidate($eventFieldName, $eventFieldValue, $eventId);

		//address
		if ($eventFieldName == "address") {
			if(!empty($eventFieldValue["postalCode"]) && !empty($eventFieldValue["codeInsee"])) {
				$insee = $eventFieldValue["codeInsee"];
				$address = SIG::getAdressSchemaLikeByCodeInsee($insee);
				$set = array("address" => $address, "geo" => SIG::getGeoPositionByInseeCode($insee));
			} else {
				throw new CTKException("Error updating the Event : address is not well formated !");			
			}
		//Date format
		} else if ($dataFieldName == "startDate" || $dataFieldName == "endDate") {
			date_default_timezone_set('UTC');
			$dt = DateTime::createFromFormat('Y-m-d H:i', $eventFieldValue);
			if (empty($dt)) {
				$dt = DateTime::createFromFormat('Y-m-d', $eventFieldValue);
			}
			$newMongoDate = new MongoDate($dt->getTimestamp());
			$set = array($dataFieldName => $newMongoDate);	
		} else {
			$set = array($dataFieldName => $eventFieldValue);
		}

		$res = Event::updateEvent($eventId, $set, $userId);
		return $res;
	}


	public static function updateEvent($eventId, $event, $userId) {  
		
		if (! Authorisation::isEventAdmin($eventId, $userId)) {
			throw new CTKException("Can not update the event : you are not authorized to update that event!");	
		}

		if (isset($event["tags"]))
			$event["tags"] = Tags::filterAndSaveNewTags($event["tags"]);
		

		PHDB::update( Event::COLLECTION, array("_id" => new MongoId($eventId)), 
		                          array('$set' => $event));
	                  
	    return array("result"=>true, "msg"=>"Votre evenement a été modifié avec succes", "id"=>$eventId);

	}

	/**
	* @param itemId is the id of  a citizen
	* @param limit is the number of events we want to get
	* @return an array with the next event since the current day
	*/
	public static function getListCurrentEventsByPeopleId($userId, $limit = 20) {
		$listEvent = array();
		$where = array 	('$and' => array (
							array("links.attendees.".$userId => array('$exists' => true)),
							array("endDate" => array('$gte' => new MongoDate(time())))
						));
        $eventPeople = PHDB::findAndSort(self::COLLECTION, $where, array('endDate' => 1), $limit);

        return Event::addInfoEvents($eventPeople);
	}


	public static function addInfoEvents($events){
		foreach ($events as $key => $value) {

	  		if (!empty($value["startDate"]) && !empty($value["endDate"])) {
				if (gettype($value["startDate"]) == "object" && gettype($value["endDate"]) == "object") {
					$events[$key]["startDate"] = date('Y-m-d H:i:s', $value["startDate"]->sec);
					$events[$key]["endDate"] = date('Y-m-d H:i:s', $value["endDate"]->sec);
				} else {
					//Manage old date with string on date value
					$now = time();
					$yesterday = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
					$yester2day = mktime(0, 0, 0, date("m")  , date("d")-2, date("Y"));
					$events[$key]["endDate"] = date('Y-m-d H:i:s', $yesterday);
					$events[$key]["startDate"] = date('Y-m-d H:i:s',$yester2day);;
				}
			}

	  		$events[$key]["organizer"] = "";
	  		if(isset( $value["links"] )){
		  		foreach ( $value["links"] as $k => $v ) {
		  			if($k == "organizer"){
		  				foreach ($v as $organizerId => $val) {
		  					$organization = Organization::getById($organizerId);
		  					$events[$key]["organizer"] = $organization["name"];
		  				}
		  			}
		  		}
		  	}
 	  		$imageUrl= Document::getLastImageByKey($key, self::COLLECTION, '');
 	  		$events[$key]["imageUrl"] = $imageUrl;
	  	}
	  	return $events;
	}

	/**
	 * get attendees of an Event By an event Id
	 * @param String $id : is the mongoId (String) of the event
	 * @param String $type : can be use to filter the member by type (all (default), person, event)
	 * @param String $role : can be use to filter the member by role (isAdmin:true)
	 * @return arrays of members (links.members)
	 */
	public static function getAttendeesByEventId($id, $type="all",$role=null) {
	  	$res = array();
	  	$event = Event::getById($id);
	  	
	  	if (empty($event)) {
            throw new CTKException(Yii::t("event", "The event id is unkown : contact your admin"));
        }
	  	if (isset($event) && isset($event["links"]) && isset($event["links"]["attendees"])) {
	  		//No filter needed
	  		if ($type == "all") {
	  			return $event["links"]["attendees"];
	  		} else {
	  			foreach ($event["links"]["attendees"] as $key => $member) {
		            if ($member['type'] == $type ) {
		                $res[$key] = $member;
		            }
		            if ( $role && @$member[$role] == true ) {
		                $res[$key] = $member;
		            }
	        	}
	  		}
	  	}
	  	return $res;
	}

	
}
?>