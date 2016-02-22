<?php 
class Event {
	const COLLECTION = "events";
	const CONTROLLER = "event";
	const ICON = "fa-calendar";
	const COLOR = "#F9B21A";


	//From Post/Form name to database field name
	private static $dataBinding = array(
	    "name" => array("name" => "name", "rules" => array("required")),
	    "type" => array("name" => "type"),
	    "address" => array("name" => "address"),
	    "streetAddress" => array("name" => "address.streetAddress"),
	    "postalCode" => array("name" => "address.postalCode"),
	    "city" => array("name" => "address.codeInsee"),
	    "addressLocality" => array("name" => "address.addressLocality"),
	    "addressCountry" => array("name" => "address.addressCountry"),
	    "geo" => array("name" => "geo"),
	    "geoPosition" => array("name" => "geoPosition"),
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
		$event = PHDB::findOne(self::COLLECTION,array("_id"=>new MongoId($id)));
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
		if(!empty($event)){
		$event = array_merge($event, Document::retrieveAllImagesUrl($id, self::COLLECTION));
		$event["typeSig"] = "events";
	  	}
	  	return $event;
	}

	/**
	 * Retrieve a simple event (id, name, type profilImageUrl) by id from DB
	 * @param String $id of the event
	 * @return array with data id, name, type profilImageUrl
	 */
	public static function getSimpleEventById($id) {
		
		$simpleEvent = array();
		$event = PHDB::findOneById( self::COLLECTION ,$id, array("id" => 1, "name" => 1, "type" => 1,  "shortDescription" => 1, "description" => 1,
																 "address" => 1, "geo" => 1, "tags" => 1) );

		$simpleEvent["id"] = $id;
		$simpleEvent["name"] = @$event["name"];
		$simpleEvent["type"] = @$event["type"];
		$simpleEvent["geo"] = @$event["geo"];
		$simpleEvent["tags"] = @$event["tags"];
		$simpleEvent["shortDescription"] = @$event["shortDescription"];
		$simpleEvent["description"] = @$event["description"];
		
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
			//throw new CTKException("The event id is unknown ! Check your URL");
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
	public static function getAndCheckEvent($params,$update=false) {
		
		self::checkEventData($params);
		$allDay = $params['allDay'] == 'true' ? true : false;
	    $newEvent = array(
			"name" => $params['name'],
			'type' => $params['type'],
			"allDay" => $allDay
		);
		if(!$update)
			$newEvent = array_merge( $newEvent , array( 'public' => true,
														'created' => new MongoDate(time()),
														"startDate" => new MongoDate(strtotime($params['startDate'])),
														"endDate" => new MongoDate(strtotime($params['endDate'])),
												        'creator' => (empty($params["creator"]) ? Yii::app()->session['userId'] : $params["creator"] ) ) );

	    
	    //Postal code & geo
	    if(!empty($params['postalCode'])) {
			if (!empty($params['city'])) {
				$insee = $params['city'];
				$address = SIG::getAdressSchemaLikeByCodeInsee($insee);
				$newEvent["address"] = $address;
			}

			// For module "OpenAgenda"
		} else if(!empty($params['address'])) {
			$newEvent["address"] = $params['address'];
			$newEvent['address']['@type'] = "PostalAddress" ;
		}
		
		
	    //Postal code & geo
	    if(!empty($params['country'])) {
			$newEvent["address"]["addressCountry"] = $params['country'];
		}
		if(!empty($params['streetAddress'])) {
			$newEvent["address"]["streetAddress"] = $params['streetAddress'];
		}
		
		
		if(!empty($params['geoPosLatitude']) && !empty($params["geoPosLongitude"])){
			

			$newEvent["geo"] = 	array(	"@type"=>"GeoCoordinates",
						"latitude" => $params['geoPosLatitude'],
						"longitude" => $params['geoPosLongitude']);

			$newEvent["geoPosition"] = array(	"type"			=> "point",
												"coordinates" 	=> array(
																		floatval($params['geoPosLongitude']),
																		floatval($params['geoPosLatitude']))
															 	  	);
		}
		else
		{
			$newEvent["geo"] = SIG::getGeoPositionByInseeCode($insee);
		}
	    //sameAs      
	    if(!empty($params['description']))
	         $newEvent["description"] = $params['description'];


	    if(!empty($params['sourceId']))
	    	$newEvent["sourceId"] = $params['sourceId'];
	    if(!empty($params['sourceUrl']))
	    	$newEvent["sourceUrl"] = $params['sourceUrl'];
	    if(!empty($params['dates']))
	    	$newEvent["dates"] = $params['dates'];

	    return $newEvent;
	}

	public static function saveEvent($params) {
		$newEvent = self::getAndCheckEvent($params);

	    PHDB::insert(self::COLLECTION,$newEvent);
	    
	    /*
		*   Add the creator as the first attendee
		*	He is admin because he is admin of organizer
		*/
		$creator = true;
		$isAdmin = false;
		
		if($params["organizerType"] == Person::COLLECTION )
			$isAdmin=true;

	    Link::attendee($newEvent["_id"], Yii::app()->session['userId'], $isAdmin, $creator);
	    Link::addOrganizer($params["organizerId"],$params["organizerType"], $newEvent["_id"], Yii::app()->session['userId']);
				
		Notification::createdObjectAsParam( Person::COLLECTION, Yii::app()->session['userId'],Event::COLLECTION, (String)$newEvent["_id"], $params["organizerType"], $params["organizerId"], $newEvent["geo"], array($newEvent["type"]),$newEvent["address"]);

	    //send validation mail
	    //TODO : make emails as cron events
	    /*$message = new YiiMailMessage; 
	    $message->view = 'validation';
	    $message->setSubject('Confirmer votre compte Pixel Humain');
	    $message->setBody(array("user"=>$new["_id"]), 'text/html');
	    $message->addTo("oceatoon@gmail.com");//$params['registerEmail']
	    $message->from = Yii::app()->params['adminEmail'];
	    Yii::app()->mail->send($message);*/
	    $creator = Person::getById(Yii::app()->session['userId']);
	    Mail::newEvent($creator,$newEvent);
	    
	    //TODO : add an admin notification
	    //Notification::saveNotification(array("type"=>NotificationType::ASSOCIATION_SAVED,"user"=>$new["_id"]));
	    
	    return array("result"=>true, "msg"=>Yii::t("event","Your event has been connected."), "id"=>$newEvent["_id"], "event" => $newEvent );
	}

	/**
	 * update an event in database
	 * @param String $eventId : 
	 * @param array $event event fields to update
	 * @param String $userId : the userId making the update
	 * @return array of result (result => boolean, msg => string)
	 */
	public static function update($eventId, $eventChangedFields, $userId) 
	{
		//Check if user is authorized to update
		if (! Authorisation::iseventAdmin($eventId,$userId)) {
			return array("result"=>false, "msg"=>Yii::t("event", "Unauthorized Access."));
		}
		//$event = self::getById($eventId);
		foreach ($eventChangedFields as $fieldName => $fieldValue) {
			//if( $event[$fieldName] != $fieldValue)
				self::updateEventField($eventId, $fieldName, $fieldValue, $userId);
		}

	    return array("result"=>true, "msg"=>Yii::t("event", "The event has been updated"), "id"=>$eventId);
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
					if(isset($value["links"]["attendees"])){
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
		$res = ActivityStream::removeObject($eventId,event::COLLECTION);
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
		if ($dataFieldName == "address") {
			if(!empty($eventFieldValue["postalCode"]) && !empty($eventFieldValue["codeInsee"])) {
				$insee = $eventFieldValue["codeInsee"];
				$address = SIG::getAdressSchemaLikeByCodeInsee($insee);
				
				if(!empty( $eventFieldValue["streetAddress"] ))
					$address[ "streetAddress" ] = $eventFieldValue["streetAddress"];

				$set = array("address" => $address, 
							 "geo" => SIG::getGeoPositionByInseeCode($insee));
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
		
		$event["modified"] = new MongoDate(time());
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

	/* 	get event list where organizer is one of my organization that I administrate
	*	@param string itemId is to find organizerId in event
	*   @param string itemType is to find organizerType in event
	*
	*/
	public static function listEventByOrganizerId($itemId,$itemType){
		$where=array("links.organizer.".$itemId.".type" => $itemType);
		$events=PHDB::find(self::COLLECTION, $where);
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

	/* 	Get an event from an OpenAgenda ID 
	*	@param string OpenAgenda Id is to find event
	*   return Event
	*/
	public static function getEventsOpenAgenda($eventsIdOpenAgenda){
		$where=array("sourceId" => $eventsIdOpenAgenda);
		$event=PHDB::find(self::COLLECTION, $where);
		return $event;
	}



	/* 	Get state an event from an OpenAgenda ID 
	*	@param string OpenAgenda ID
	*	@param string Date Update openAgenda
	*   return String ("Add", "Update" or "Delete")
	*/
	public static function getStateEventsOpenAgenda($eventsIdOpenAgenda, $dateUpdate, $endDateOpenAgende){
		$state = "";
		$event=Event::getEventsOpenAgenda($eventsIdOpenAgenda);
		if(empty($event)){
			$state = "Add";
		}else{
			foreach ($event as $key => $value) {
				$arrayTimeEnd = explode(":", $endDateOpenAgende[0]["dates"][0]["timeEnd"]);
				$arrayDate = explode("-", $endDateOpenAgende[0]["dates"][0]["date"]);
				$end = mktime($arrayTimeEnd[0], $arrayTimeEnd[1], $arrayTimeEnd[2], $arrayDate[1]  , $arrayDate[2], $arrayDate[0]);
				$endDateEvents = date('Y-m-d H:i:s', $end);
				$today = mktime(date("H"), date("i"), date("s"), date("m")  , date("d")-1, date("Y"));
				
				if(!empty($value["modified"]->sec))
					$lastUpDate = $value["modified"]->sec ;
				else
					$lastUpDate = $value["created"]->sec ;
				
				if($today > strtotime($endDateEvents)){
					$state = "Delete";
				}else if(strtotime($dateUpdate) > $lastUpDate ){
					
					//var_dump("HERE");
					$state = "Update";
				}
				break;
			}
			
		}

		return $state;
	}

	/* 	Get state an event from an OpenAgenda ID 
	*	@param string OpenAgenda ID
	*	@param string Date Update openAgenda
	*   return String ("Add", "Update" or "Delete")
	*/
	public static function createEventsFromOpenAgenda($eventOpenAgenda) {
		$newEvents["name"] = empty($eventOpenAgenda["title"]["fr"]) ? "" : $eventOpenAgenda["title"]["fr"];
		$newEvents["description"] = empty($eventOpenAgenda["description"]["fr"]) ? "" : $eventOpenAgenda["description"]["fr"];
		$newEvents["organizerId"] = "5694ea2a94ef47ad1c8b456d";
		$newEvents["organizerType"] = Person::COLLECTION ;
		if(!empty($eventOpenAgenda["locations"][0]["dates"][0]["timeStart"]) && !empty($eventOpenAgenda["locations"][0]["dates"][0]["timeStart"]) && !empty($eventOpenAgenda["locations"][0]["dates"][0]["date"]))
		{
			$arrayTimeStart = explode(":", $eventOpenAgenda["locations"][0]["dates"][0]["timeStart"]);

			$nbDates = count($eventOpenAgenda["locations"][0]["dates"]);
			$arrayTimeEnd = explode(":", $eventOpenAgenda["locations"][0]["dates"][$nbDates-1]["timeEnd"]);
			$arrayDateStart = explode("-", $eventOpenAgenda["locations"][0]["dates"][0]["date"]);
			$arrayDateEnd = explode("-", $eventOpenAgenda["locations"][0]["dates"][$nbDates-1]["date"]);

			$start = mktime($arrayTimeStart[0], $arrayTimeStart[1], $arrayTimeStart[2], $arrayDateStart[1]  , $arrayDateStart[2], $arrayDateStart[0]);
			$end = mktime($arrayTimeEnd[0], $arrayTimeEnd[1], $arrayTimeEnd[2], $arrayDateEnd[1]  , $arrayDateEnd[2], $arrayDateEnd[0]);

			$newEvents["startDate"] = date('Y-m-d H:i:s', $start);
			$newEvents["endDate"] = date('Y-m-d H:i:s', $end);
		}
		
		if(!empty($eventOpenAgenda["locations"][0]["dates"]))
			$newEvents["dates"] = $eventOpenAgenda["locations"][0]["dates"];


		$newEvents["geoPosLatitude"] = empty($eventOpenAgenda["locations"][0]["latitude"]) ? "" : $eventOpenAgenda["locations"][0]["latitude"];
		$newEvents["geoPosLongitude"] = empty($eventOpenAgenda["locations"][0]["longitude"]) ? "" : $eventOpenAgenda["locations"][0]["longitude"];


		if(!empty($newEvents["geoPosLongitude"]) && !empty($newEvents["geoPosLongitude"]))
		{
			$newEvents['address']['@type'] = "PostalAddress" ;
			$newEvents['address']['postalCode'] = empty($eventOpenAgenda["locations"][0]["postalCode"]) ? "" : $eventOpenAgenda["locations"][0]["postalCode"];
			
			$where = array("cp"=>$newEvents['address']['postalCode']);
			$option = City::getWhere($where);
	        if(empty($option))
	        	throw new CTKException("Ce code postal n'existe pas.");	

			$city = SIG::getInseeByLatLngCp($newEvents["geoPosLatitude"], $newEvents["geoPosLongitude"],  (empty($eventOpenAgenda["locations"][0]["postalCode"]) ? null : $eventOpenAgenda["locations"][0]["postalCode"]) );

			if($eventOpenAgenda["locations"][0]["postalCode"] == $city["cp"])
				$newEvents['address']['postalCode'] = $eventOpenAgenda["locations"][0]["postalCode"] ;
			else
				throw new CTKException("Erreur: le code postal ne correspond pas à la city retourné.");

			$newEvents['address']['streetAddress'] = "";
			$newEvents['address']['addressCountry'] =  $city["country"];
			$newEvents['address']['addressLocality'] = $city["alternateName"];
			$newEvents['address']['codeInsee'] = $city["insee"];	
	
		}
		$newEvents["creator"] = "5694ea2a94ef47ad1c8b456d";
		$newEvents["type"] = "other";
		$newEvents["public"] = true;
		$newEvents['allDay'] = 'true' ;

		$newEvents['sourceId'] = $eventOpenAgenda["uid"] ;
		$newEvents['sourceUrl'] = $eventOpenAgenda["link"] ;

		return $newEvents;
	}


	public static function saveEventFromOpenAgenda($params) {
		$newEvent = self::getAndCheckEvent($params);

	    PHDB::insert(self::COLLECTION,$newEvent);
	    
		$creator = true;
		$isAdmin = true;
		Link::attendee($newEvent["_id"], "5694ea2a94ef47ad1c8b456d", $isAdmin, $creator);
	    Link::addOrganizer($params["organizerId"],$params["organizerType"], $newEvent["_id"], "5694ea2a94ef47ad1c8b456d");
				
		return array("result"=>true, "msg"=>Yii::t("event","Your event has been connected."), "id"=>$newEvent["_id"], "event" => $newEvent );
	

	}

}
?>