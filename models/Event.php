<?php 
class Event {
	const COLLECTION = "events";
	const CONTROLLER = "event";
	const ICON = "fa-calendar";
	const COLOR = "#F9B21A";

	const NO_ORGANISER = "dontKnow";


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
	    "shortDescription" => array("name" => "shortDescription"),
	    "allDay" => array("name" => "allDay"),
	    "modules" => array("name" => "modules"),
	    "startDate" => array("name" => "startDate", "rules" => array("eventStartDate")),
	    "endDate" => array("name" => "endDate", "rules" => array("eventEndDate")),
	    "parentId" => array("name" => "parentId"),
	    "source" => array("name" => "source")
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
			$event = array_merge($event, Document::retrieveAllImagesUrl($id, self::COLLECTION, @$event["type"], $event));
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
		$event = PHDB::findOneById( self::COLLECTION ,$id, array("id" => 1, "name" => 1, "type" => 1,  "shortDescription" => 1, "description" => 1, "address" => 1, "geo" => 1, "tags" => 1, "profilImageUrl" => 1, "profilThumbImageUrl" => 1, "profilMarkerImageUrl" => 1));

		if(!empty($event)){
			$simpleEvent["id"] = $id;
			$simpleEvent["name"] = @$event["name"];
			$simpleEvent["type"] = @$event["type"];
			$simpleEvent["geo"] = @$event["geo"];
			$simpleEvent["tags"] = @$event["tags"];
			$simpleEvent["shortDescription"] = @$event["shortDescription"];
			$simpleEvent["description"] = @$event["description"];
			
			$simpleEvent = array_merge($simpleEvent, Document::retrieveAllImagesUrl($id, self::COLLECTION, $simpleEvent["type"], $event));
			
			$simpleEvent["address"] = empty($event["address"]) ? array("addressLocality" => "Unknown") : $event["address"];
		}
		return @$simpleEvent;
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

		if(empty($event['startDate']) ) { //|| empty($event['endDate'])
			throw new CTKException("The start  date of an event are required.");
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
		date_default_timezone_set('UTC');
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
				$postalCode=$params['postalCode'];
				$cityName= $params['cityName'];
				$address = SIG::getAdressSchemaLikeByCodeInsee($insee,$postalCode,$cityName);
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
		
		
		if(!empty($params['geoPosLatitude']) && !empty($params["geoPosLongitude"]))
		{
			$newEvent["geo"] = 	array(	"@type"=>"GeoCoordinates",
						"latitude" => $params['geoPosLatitude'],
						"longitude" => $params['geoPosLongitude']);

			$newEvent["geoPosition"] = array(	"type"			=> "Point",
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

	    if(!empty($params['parentId']))
	        $newEvent["parentId"] = $params['parentId'];

	    return $newEvent;
	}

	public static function saveEvent($params, $import = false, $warnings = null) {
		if($import == false)
			$newEvent = self::getAndCheckEvent($params);
		else
			$newEvent = self::getAndCheckEventFromImportData($params, true, null, $warnings);

	    PHDB::insert(self::COLLECTION,$newEvent);
	    
	    /*
	    * except if organiser type is dontKnow
		*   Add the creator as the first attendee
		*	He is admin because he is admin of organizer
		*/
		$creator = true;
		$isAdmin = false;
		
		if($params["organizerType"] == Person::COLLECTION )
			$isAdmin=true;

	    if($params["organizerType"] != self::NO_ORGANISER ){
	    	Link::attendee($newEvent["_id"], Yii::app()->session['userId'], $isAdmin, $creator);
	    	Link::addOrganizer($params["organizerId"],$params["organizerType"], $newEvent["_id"], Yii::app()->session['userId']);
	    } else {
	    	$params["organizerType"] = Person::COLLECTION;
	    	$params["organizerId"] = Yii::app()->session['userId'];
	    }

	    //if it's a subevent, add the organiser to the parent user Organiser list 
    	//ajouter le nouveau sub user dans organiser ?
    	if( @$newEvent["parentId"] )
			Link::connect( $newEvent["parentId"], Event::COLLECTION,$newEvent["_id"], Event::COLLECTION, Yii::app()->session["userId"], "subEvents");	

		Notification::createdObjectAsParam( Person::COLLECTION, Yii::app()->session['userId'],Event::COLLECTION, (String)$newEvent["_id"], $params["organizerType"], $params["organizerId"], $newEvent["geo"], array($newEvent["type"]),$newEvent["address"]);

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
	 * Retrieve the list of events, for a given event 
	 * @param String event Id
	 * @return array list of the event and it's subevents
	 */
	public static function getListEventsById($id) {
		$event = self::getById($id);
		$listEvent = array($id => $event);
        $subEvents = PHDB::findAndSort(self::COLLECTION, array 	('parentId' => $id ), array('startDate' => 1));
        $listEvent = array_merge($listEvent,$subEvents);

        return Event::addInfoEvents($listEvent);
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
        	$value = array_merge($value, Document::retrieveAllImagesUrl($key, self::COLLECTION, $value));
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
				$postalCode = $eventFieldValue["postalCode"];
				$cityName = $eventFieldValue["addressLocality"];
				$address = SIG::getAdressSchemaLikeByCodeInsee($insee,$postalCode,$cityName);
				
				if(!empty( $eventFieldValue["streetAddress"] ))
					$address[ "streetAddress" ] = $eventFieldValue["streetAddress"];

				$set = array("address" => $address, 
							 "geo" => SIG::getGeoPositionByInseeCode($insee,$postalCode,$cityName));
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

	  		if (!empty($value["startDate"]) && !empty($value["endDate"])) 
	  		{
				if (gettype($value["startDate"]) == "object" && gettype($value["endDate"]) == "object") 
				{
					$events[$key]["startDate"] = date('Y-m-d H:i:s', $value["startDate"]->sec);
					$events[$key]["endDate"] = date('Y-m-d H:i:s', $value["endDate"]->sec);
				} 
				else 
				{
					//Manage old date with string on date value
					$now = time();
					$yesterday = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
					$yester2day = mktime(0, 0, 0, date("m")  , date("d")-2, date("Y"));
					$events[$key]["endDate"] = date('Y-m-d H:i:s', $yesterday);
					$events[$key]["startDate"] = date('Y-m-d H:i:s',$yester2day);
				}
			}

	  		$events[$key]["organizer"] = "";
	  		if( @$value["links"]["organizer"] )
	  		{
		  		foreach ( $value["links"]["organizer"] as $organizerId => $val ) 
		  		{
  					$organization = Organization::getById($organizerId);
  					$events[$key]["organizer"] = $organization["name"];
		  		}
		  	}
 	  		$events[$key] = array_merge($events[$key], Document::retrieveAllImagesUrl($key, self::COLLECTION));
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
		$where=array("source.id" => $eventsIdOpenAgenda);
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
				if(!empty($endDateOpenAgende[0]["dates"])){
					$arrayTimeEnd = explode(":", $endDateOpenAgende[0]["dates"][0]["timeEnd"]);
					$arrayDate = explode("-", $endDateOpenAgende[0]["dates"][0]["date"]);
					$end = mktime($arrayTimeEnd[0], $arrayTimeEnd[1], $arrayTimeEnd[2], $arrayDate[1]  , $arrayDate[2], $arrayDate[0]);
					$endDateEvents = date('Y-m-d H:i:s', $end);
					$today = mktime(date("H"), date("i"), date("s"), date("m")  , date("d")-1, date("Y"));
					
					if(!empty($value["modified"]->sec))
						$lastUpDate = $value["modified"]->sec ;
					else
						$lastUpDate = $value["created"]->sec ;
					
					if(strtotime($dateUpdate) > $lastUpDate ){
						$state = "Update";
					}
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
		$newEvents["shortDescription"] = empty($eventOpenAgenda["freeText"]["fr"]) ? "" : $eventOpenAgenda["freeText"]["fr"];
		$newEvents["image"] = empty($eventOpenAgenda["image"]) ? "" : $eventOpenAgenda["image"];
		$newEvents["organizerId"] = Yii::app()->params['idOpenAgenda'];
		$newEvents["organizerType"] = Person::COLLECTION ;	
			
		if(!empty($eventOpenAgenda["locations"][0]["dates"])){
			$nbDates = count($eventOpenAgenda["locations"][0]["dates"]);
			if(!empty($eventOpenAgenda["locations"][0]["dates"][0]["timeStart"]) && !empty($eventOpenAgenda["locations"][0]["dates"][$nbDates-1]["timeEnd"])){
				$arrayTimeStart = explode(":", $eventOpenAgenda["locations"][0]["dates"][0]["timeStart"]);
				$arrayTimeEnd = explode(":", $eventOpenAgenda["locations"][0]["dates"][$nbDates-1]["timeEnd"]);
				
				$arrayDateStart = explode("-", $eventOpenAgenda["locations"][0]["dates"][0]["date"]);
				$arrayDateEnd = explode("-", $eventOpenAgenda["locations"][0]["dates"][$nbDates-1]["date"]);

				$start = mktime($arrayTimeStart[0], $arrayTimeStart[1], $arrayTimeStart[2], $arrayDateStart[1]  , $arrayDateStart[2], $arrayDateStart[0]);
				$end = mktime($arrayTimeEnd[0], $arrayTimeEnd[1], $arrayTimeEnd[2], $arrayDateEnd[1]  , $arrayDateEnd[2], $arrayDateEnd[0]);

				$newEvents["startDate"] = date('Y-m-d H:i:s', $start);
				$newEvents["endDate"] = date('Y-m-d H:i:s', $end);
			}
			
			$newEvents["dates"] = $eventOpenAgenda["locations"][0]["dates"];
		}


		$geo["latitude"] = (empty($eventOpenAgenda["locations"][0]["latitude"]) ? "" : $eventOpenAgenda["locations"][0]["latitude"]);
		$geo["longitude"] = (empty($eventOpenAgenda["locations"][0]["longitude"]) ? "" : $eventOpenAgenda["locations"][0]["longitude"]);
		$address['postalCode'] =  (empty($eventOpenAgenda["locations"][0]["postalCode"])? "" : $eventOpenAgenda["locations"][0]["postalCode"]);
		$address['streetAddress'] = (empty($eventOpenAgenda["locations"][0]["address"]) ? "" : $eventOpenAgenda["locations"][0]["address"]);
		$address['addressLocality'] = (empty($eventOpenAgenda["locations"][0]["city"]) ? "" : $eventOpenAgenda["locations"][0]["city"]);

		/*$address = (empty($organization['address']) ? null : $organization['address']);
		$geo = (empty($newEvents["geo"]) ? null : $newEvents["geo"]);*/
		//var_dump($newOrganization['name']);
		$details = Import::getAndCheckAddressForEntity($address, $geo, null) ;
		$newEvents['address'] = $details['address'];

		if(!empty($details['geo']))
			$newEvents['geo'] = $details['geo'] ;

		if(!empty($details['geoPosition']))
			$newEvents['geoPosition'] = $details['geoPosition'] ;

		/*if(!empty($newEvents["geo"]["latitude"]) && !empty($newEvents["geo"]["longitude"]))
		{
			$newEvents['address']['@type'] = "PostalAddress" ;
			$newEvents['address']['postalCode'] = empty($eventOpenAgenda["locations"][0]["postalCode"]) ? "" : $eventOpenAgenda["locations"][0]["postalCode"];
			
			$where = array("cp"=>$newEvents['address']['postalCode']);
			$option = City::getWhere($where);
	        if(empty($option))
	        	throw new CTKException("Ce code postal n'existe pas.");	

	        $city = SIG::getCityByLatLngGeoShape($newEvents["geo"]["latitude"], $newEvents["geo"]["longitude"],  (empty($eventOpenAgenda["locations"][0]["postalCode"]) ? null : $eventOpenAgenda["locations"][0]["postalCode"]) );
			
			if(!empty($city)){
				foreach ($city as $key => $value) {
					if($eventOpenAgenda["locations"][0]["postalCode"] == $value["cp"])
					{
						$newEvents['address']['postalCode'] = $eventOpenAgenda["locations"][0]["postalCode"] ;
						$newEvents['address']['streetAddress'] = $eventOpenAgenda["locations"][0]["address"];
						$newEvents['address']['addressCountry'] =  $value["country"];
						$newEvents['address']['addressLocality'] = $value["alternateName"];
						$newEvents['address']['codeInsee'] = $value["insee"];
					}	
					else
						throw new CTKException("Erreur: le code postal ne correspond pas à la city retourné.");
				}
			}else{
				throw new CTKException("Erreur: On n'a pu récupérer la commune associé.");
			}		
	
		}*/

		$newEvents["tags"] = empty($eventOpenAgenda["tags"]["fr"]) ? "" : explode(",", $eventOpenAgenda["tags"]["fr"]);

		$newEvents["creator"] = Yii::app()->params['idOpenAgenda'];
		$newEvents["type"] = "other";
		$newEvents["public"] = true;
		$newEvents['allDay'] = 'true' ;

		$newEvents['source']["id"] = $eventOpenAgenda["uid"] ;
		$newEvents['source']["url"] = $eventOpenAgenda["link"] ;
		$newEvents['source']["key"] = "openagenda" ;

		return $newEvents;
	}

	public static function saveEventFromOpenAgenda($params, $moduleId) {
		$newEvent = self::getAndCheckEventOpenAgenda($params);
		

		if(!empty($newEvent["image"])){
			$arrrayNameImage = explode("/", $newEvent["image"]);
			$nameImage = $arrrayNameImage[count($arrrayNameImage)-1];
			$pathFolderImage = "https://cibul.s3.amazonaws.com/";
			unset($newEvent["image"]);
		}
	    


	    PHDB::insert(self::COLLECTION,$newEvent);
	    if (isset($newEvent["_id"]))
	    	$newEventId = (String) $newEvent["_id"];
	    else
	    	throw new CTKException("Problem inserting the new event");
	    
		$creator = true;
		$isAdmin = true;
		Link::attendee($newEvent["_id"], Yii::app()->params['idOpenAgenda'], $isAdmin, $creator);
	    Link::addOrganizer($params["organizerId"],$params["organizerType"], $newEvent["_id"], Yii::app()->params['idOpenAgenda']);

	    $msgErrorImage = "" ;
	    if(!empty($nameImage)){
			try{
				$res = Document::uploadDocument($moduleId, self::COLLECTION, $newEventId, "avatar", false, $pathFolderImage, $nameImage);
				if(!empty($res["result"]) && $res["result"] == true){
					$params = array();
					$params['id'] = $newEventId;
					$params['type'] = self::COLLECTION;
					$params['moduleId'] = $moduleId;
					$params['folder'] = self::COLLECTION."/".$newEventId;
					$params['name'] = $res['name'];
					$params['author'] = Yii::app()->session["userId"] ;
					$params['size'] = $res["size"];
					$params["contentKey"] = "profil";
					$res2 = Document::save($params);
					if($res2["result"] == false)
						throw new CTKException("Impossible de d'enregistrer le fichier.");

				}else{
					$msgErrorImage = "Impossible uploader le document." ; 
				}
			}catch (CTKException $e){
				throw new CTKException($e);
			}
		}

				
		return array("result"=>true, "msg"=>Yii::t("event","Your event has been connected.")." ".$msgErrorImage, "id"=>$newEvent["_id"], "event" => $newEvent );
	

	}

	
	public static function getAndCheckEventOpenAgenda($event) {
		$newEvent = array();
		
		if (empty($event['name'])) {
			throw new CTKException(Yii::t("import","001", null, Yii::app()->controller->module->id));
		}else
			$newEvent['name'] = $event['name'];



		$newEvent['created'] = new MongoDate(time()) ;
		
		if(!empty($event['email'])) {
			if (! preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#',$organization['email'])) { 
				throw new CTKException(Yii::t("import","205", null, Yii::app()->controller->module->id));
			}
			$newEvent["email"] = $event['email'];
		}

		if(empty($event['type'])) {
			throw new CTKException(Yii::t("import","208", null, Yii::app()->controller->module->id));
		}else{
			$newEvent["type"] = $event['type'];
		}
				  
		
		if(!empty($event['address'])) {
			if(empty($event['address']['postalCode'])){
				throw new CTKException(Yii::t("import","101", null, Yii::app()->controller->module->id));
			}
			if(empty($event['address']['codeInsee'])){
				throw new CTKException(Yii::t("import","102", null, Yii::app()->controller->module->id));
			}
			if(empty($event['address']['addressCountry'])){
				throw new CTKException(Yii::t("import","104", null, Yii::app()->controller->module->id));
			}
			if(empty($event['address']['addressLocality']))
				throw new CTKException(Yii::t("import","105", null, Yii::app()->controller->module->id));
			
			$newEvent['address'] = $event['address'] ;

		}else {
			throw new CTKException(Yii::t("import","100", null, Yii::app()->controller->module->id));
		}

		if(!empty($event['geo']) && !empty($event["geoPosition"])){
			$newEvent["geo"] = $event['geo'];
			$newEvent["geoPosition"] = $event['geoPosition'];

		}else if(!empty($event["geo"]['latitude']) && !empty($event["geo"]["longitude"])){
			$newEvent["geo"] = 	array(	"@type"=>"GeoCoordinates",
						"latitude" => $event["geo"]['latitude'],
						"longitude" => $event["geo"]["longitude"]);

			$newEvent["geoPosition"] = array("type"=>"Point",
													"coordinates" =>
														array(
															floatval($event["geo"]['latitude']),
															floatval($event["geo"]['longitude']))
												 	  	);
		}
		else
			throw new CTKException(Yii::t("import","150", null, Yii::app()->controller->module->id));
			
		
		if (isset($event['tags'])) {
			if ( gettype($event['tags']) == "array" ) {
				$tags = $event['tags'];
			} else if ( gettype($event['tags']) == "string" ) {
				$tags = explode(",", $event['tags']);
			}
			$newEvent["tags"] = $tags;
		}
		
		if (!empty($event['description']))
			$newEvent["description"] = $event['description'];

		if (!empty($event['shortDescription']))
			$newEvent["shortDescription"] = $event['shortDescription'];

		if(!empty($event['creator'])){
			$newEvent["creator"] = $event['creator'];
		}

		if(!empty($event['source'])){
			$newEvent["source"] = $event['source'];
		}

		//url by ImportData
		if(!empty($event['url'])){
			$newEvent["url"] = $event['url'];
		}

		if(!empty($event['allDay'])){
			$newEvent["allDay"] = $event['allDay'];
		}

		if(!empty($event['startDate'])){	
			$m = new MongoDate(strtotime($event['startDate']));
			$newEvent['startDate'] = $m;
		}	
		if(!empty($event['image'])){
			$newEvent["image"] = $event['image'];
		}

		if(!empty($event['endDate'])){
			$m = new MongoDate(strtotime($event['endDate']));
			$newEvent['endDate'] = $m;
		}		
		
		return $newEvent;
	}




	public static function newEventFromImportData($event, $emailCreator=null, $warnings=null) {
		
		$newEvent = array();
		/*if(!empty($event['email']))
			$newEvent["email"] = $event['email'];*/

		$newEvent["email"] = empty($event["email"]) ? $emailCreator : $event["email"];

		if(!empty($event['name']))
			$newEvent["name"] = $event['name'];
		else
			$newEvent["name"] = "Nuit Debout";

		if(!empty($event['source'])){
			if(!empty($event['source']['id']))
				$newEvent["source"]['id'] = $event["source"]['id'];
			if(!empty($event['source']['url']))
				$newEvent["source"]['url'] = $event["source"]['url'];
			if(!empty($event['source']['key']))
				$newEvent["source"]['key'] = $event["source"]['key'];
		}

		if(!empty($event['warnings']))
			$newEvent["warnings"] = $event["warnings"];

		if(!empty($event['type'])) {
			$newEvent["type"] = $event['type'];
		}else{
			$newEvent["type"] = "other";
		}
			
		
		
		$newEvent["description"] = empty($event['description']) ? "" : $event['description'];
		$newEvent["shortDescription"] = empty($event['shortDescription']) ? "" : $event['shortDescription'];
		$newEvent["role"] = empty($event['role']) ? "" : $event['role'];
		$newEvent["creator"] = empty($event['creator']) ? "" : $event['creator'];
		$newEvent["url"] = empty($event['url']) ? "" : $event['url'];


		if(!empty($event['tags']))
		{	
			$tags = array();
			foreach ($event['tags'] as $key => $value) {
				$trimValue=trim($value);
				if(!empty($trimValue))
					$tags[] = $trimValue;
			}
			$newEvent["tags"] = $tags;
		}

		if(!empty($event['telephone']))
		{
			$tel = array();
			$fixe = array();
			$mobile = array();
			$fax = array();
			if(!empty($event['telephone']["fixe"]))
			{
				foreach ($event['telephone']["fixe"] as $key => $value) {
					$trimValue=trim($value);
					if(!empty($trimValue))
						$fixe[] = $trimValue;
				}
			}
			if(!empty($event['telephone']["mobile"]))
			{
				foreach ($event['telephone']["mobile"] as $key => $value) {
					$trimValue=trim($value);
					if(!empty($trimValue))
						$mobile[] = $trimValue;
				}
			}

			if(!empty($event['telephone']["fax"]))
			{
				foreach ($event['telephone']["fax"] as $key => $value) {
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
				$newEvent['telephone'] = $tel;
		}

		if(!empty($event['source']))
			$newEvent["source"] = $event["source"];

		if(!empty($event['parentId']))
	        $newEvent["parentId"] = $event['parentId'];
		

		$address = (empty($event['address']) ? null : $event['address']);
		$geo = (empty($event['geo']) ? null : $event['geo']);
		$details = Import::getAndCheckAddressForEntity($address, $geo, $warnings) ;
		$newEvent['address'] = $details['address'];

		if(!empty($details['geo']))
			$newEvent['geo'] = $details['geo'] ;

		if(!empty($newEvent['warnings']))
			$newEvent['warnings'] = array_merge($newEvent['warnings'], $details['warnings']);
		else
			$newEvent['warnings'] = $details['warnings'];


		return $newEvent;
	}


	/**
	 * Apply event checks and business rules before inserting
	 * @param array $event : array with the data of the event to check
	 * @return array Organization well format : ready to be inserted
	 */
	public static function getAndCheckEventFromImportData($event, $insert=null, $update=null, $warnings = null) {
		$newEvent = array();
		
		
		if (empty($event['name'])) {
			if($warnings)
				$newEvent["warnings"][] = "001" ;
			else
				throw new CTKException(Yii::t("import","001"));
		}else
			$newEvent['name'] = $event['name'];
		
		$newEvent['created'] = new MongoDate(time()) ;
		$newEvent['creator'] = (empty($event["creator"]) ? Yii::app()->session['userId'] : $event["creator"] );
		$newEvent['organizerType'] = (empty($event["organizerType"]) ? self::NO_ORGANISER: $event["organizerType"] );
		$newEvent['organizerId'] = (empty($event["organizerId"]) ? Yii::app()->session['userId']: $event["organizerId"] );

		
		if(empty($event['type'])) {
			if($warnings)
			{
				$newEvent["warnings"][] = "208" ;
				//$newEvent["type"] = self::TYPE_GROUP ;
			}	
			else
				throw new CTKException(Yii::t("import","208", null, Yii::app()->controller->module->id));
		}else{
			$newEvent["type"] = $event['type'];
		}
			  
		
		if(!empty($event['address'])) {
			if(empty($event['address']['postalCode']) /*&& $insert*/){
				if($warnings)
					$newEvent["warnings"][] = "101" ;
				else
					throw new CTKException(Yii::t("import","101", null, Yii::app()->controller->module->id));
			}
			if(empty($event['address']['codeInsee'])/*&& $insert*/){
				if($warnings)
					$newEvent["warnings"][] = "102" ;
				else{
					throw new CTKException(Yii::t("import","102", null, Yii::app()->controller->module->id));
				}
					
			}
			if(empty($event['address']['addressCountry']) /*&& $insert*/){
				if($warnings)
					$newEvent["warnings"][] = "104" ;
				else
					throw new CTKException(Yii::t("import","104", null, Yii::app()->controller->module->id));
			}
			if(empty($event['address']['addressLocality']) /*&& $insert*/){
				if($warnings)
					$newEvent["warnings"][] = "105" ;
				else
					throw new CTKException(Yii::t("import","105", null, Yii::app()->controller->module->id));
			}
			$newEvent['address'] = $event['address'] ;

		}else {
			if($warnings)
				$newEvent["warnings"][] = "100" ;
			else
				throw new CTKException(Yii::t("import","100", null, Yii::app()->controller->module->id));
		}
		
		if(!empty($event['geo']) && !empty($event["geoPosition"])){
			$newEvent["geo"] = $event['geo'];
			$newEvent["geoPosition"] = $event['geoPosition'];

		}else if(!empty($event["geo"]['latitude']) && !empty($event["geo"]["longitude"])){
			$newEvent["geo"] = 	array(	"@type"=>"GeoCoordinates",
						"latitude" => $event["geo"]['latitude'],
						"longitude" => $event["geo"]["longitude"]);

			$newEvent["geoPosition"] = array("type"=>"Point",
													"coordinates" =>
														array(
															floatval($event["geo"]['latitude']),
															floatval($event["geo"]['longitude']))
												 	  	);
		}
		else if($insert){
			if($warnings)
				$newEvent["warnings"][] = "150" ;
			else
				throw new CTKException(Yii::t("import","150", null, Yii::app()->controller->module->id));
		}else if($warnings)
			$newEvent["warnings"][] = "150" ;
			
		
		if (isset($event['tags'])) {
			if ( gettype($event['tags']) == "array" ) {
				$tags = $event['tags'];
			} else if ( gettype($event['tags']) == "string" ) {
				$tags = explode(",", $event['tags']);
			}
			$newEvent["tags"] = $tags;
		}
		
		if (!empty($event['description']))
			$newEvent["description"] = $event['description'];

		if (!empty($event['shortDescription']))
			$newEvent["shortDescription"] = $event['shortDescription'];

		if(!empty($event['creator'])){
			$newEvent["creator"] = $event['creator'];
		}

		if(!empty($event['source'])){
			$newEvent["source"] = $event['source'];
		}

		//url by ImportData
		if(!empty($event['url'])){
			$newEvent["url"] = $event['url'];
		}

		if(!empty($event['allDay'])){
			$newEvent["allDay"] = $event['allDay'];
		}

		if(!empty($event['startDate'])){	
			$newEvent['startDate'] = new MongoDate(time());
		}else{
			$newEvent['startDate'] = new MongoDate(time());
		}	
		if(!empty($event['image'])){
			$newEvent["image"] = $event['image'];
		}

		if(!empty($event['endDate'])){
			$newEvent['endDate'] = new MongoDate(time() + (7 * 24 * 60 * 60));
		}else{
			$newEvent['endDate'] = new MongoDate(time() + (7 * 24 * 60 * 60));
		}

		if(!empty($event['parentId']))
	        $newEvent["parentId"] = $event['parentId'];
		
		return $newEvent;
	}


	public static function insertEventFromImportData($event, $warnings = null){
	    
	    $newEvent = self::getAndCheckEventFromImportData($event, true, null, $warnings);
		
		if (isset($newEvent["tags"]))
			$newEvent["tags"] = Tags::filterAndSaveNewTags($newEvent["tags"]);
		$newEvent["creator"] = Yii::app()->session['userId'];
		$newEvent["organizerId"] = Yii::app()->session['userId'];
		$newEvent["organizerType"] = Person::COLLECTION ;	
		//Insert the event
	    PHDB::insert( self::COLLECTION, $newEvent);

	    if (isset($newEvent["_id"])) {
	    	$newEventId = (String) $newEvent["_id"];
	    } else {
	    	throw new CTKException(Yii::t("event","Problem inserting the new event"));
	    }
	    
		$isAdmin = true;
		Link::attendee($newEvent["_id"], $newEvent["creator"], $isAdmin, $newEvent["creator"]);
	    Link::addOrganizer($newEvent["organizerId"],$newEvent["organizerType"], $newEvent["_id"], $newEvent["creator"]);
	    
	    

		$newEvent = self::getById($newEventId);
	    return array("result"=>true,
		    			"msg"=>"Votre event est communectée.", 
		    			"id"=>$newEventId, 
		    			"newOrganization"=> $newEvent);
	


	    $newEvent = self::getAndCheckEvent($params);

	    PHDB::insert(self::COLLECTION,$newEvent);
	    $creator = true;
		$isAdmin = false;
		
		if($params["organizerType"] == Person::COLLECTION )
			$isAdmin=true;

	    if($params["organizerType"] != self::NO_ORGANISER ){
	    	Link::attendee($newEvent["_id"], Yii::app()->session['userId'], $isAdmin, $creator);
	    	Link::addOrganizer($params["organizerId"],$params["organizerType"], $newEvent["_id"], Yii::app()->session['userId']);
	    } else {
	    	$params["organizerType"] = Person::COLLECTION;
	    	$params["organizerId"] = Yii::app()->session['userId'];
	    }

	    //if it's a subevent, add the organiser to the parent user Organiser list 
    	//ajouter le nouveau sub user dans organiser ?
    	if( @$newEvent["parentId"] )
			Link::connect( $newEvent["parentId"], Event::COLLECTION,$newEvent["_id"], Event::COLLECTION, Yii::app()->session["userId"], "subEvents");	

		Notification::createdObjectAsParam( Person::COLLECTION, Yii::app()->session['userId'],Event::COLLECTION, (String)$newEvent["_id"], $params["organizerType"], $params["organizerId"], $newEvent["geo"], array($newEvent["type"]),$newEvent["address"]);

	    $creator = Person::getById(Yii::app()->session['userId']);
	    Mail::newEvent($creator,$newEvent);
	    
	    //TODO : add an admin notification
	    //Notification::saveNotification(array("type"=>NotificationType::ASSOCIATION_SAVED,"user"=>$new["_id"]));
	    
	    return array("result"=>true, "msg"=>Yii::t("event","Your event has been connected."), "id"=>$newEvent["_id"], "event" => $newEvent );
	











	}



	
}
?>