<?php 
class Event {
	const COLLECTION = "events";

	/**
	 * get an event By Id
	 * @param type $id : is the mongoId of the event
	 * @return type
	 */
	public static function getById($id) {
		$event = PHDB::findOne( PHType::TYPE_EVENTS,array("_id"=>new MongoId($id)));
		if (!empty($event["startDate"]) && !empty($event["endDate"])) {
			if (gettype($event["startDate"]) == "object" && gettype($event["endDate"]) == "object") {
				$event["startDate"] = date('Y-m-d h:i:s', $event["startDate"]->sec);
				$event["endDate"] = date('Y-m-d h:i:s', $event["endDate"]->sec);
			} else {
				//Manage old date with string on date event
				$now = time();
				$yesterday = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
				$yester2day = mktime(0, 0, 0, date("m")  , date("d")-2, date("Y"));
				$event["endDate"] = date('Y-m-d h:i:s', $yesterday);
				$event["startDate"] = date('Y-m-d h:i:s',$yester2day);;
			}
		}

	  	return $event;
	}
	
	public static function getWhere($params) 
	{
	  	$events =PHDB::findAndSort( self::COLLECTION,$params,array("created"),null);
	  	foreach ($events as $key => $value) {

	  		if (!empty($value["startDate"]) && !empty($value["endDate"])) {
				if (gettype($value["startDate"]) == "object" && gettype($value["endDate"]) == "object") {
					$events[$key]["startDate"] = date('Y-m-d h:i:s', $value["startDate"]->sec);
					$events[$key]["endDate"] = date('Y-m-d h:i:s', $value["endDate"]->sec);
				} else {
					//Manage old date with string on date value
					$now = time();
					$yesterday = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
					$yester2day = mktime(0, 0, 0, date("m")  , date("d")-2, date("Y"));
					$events[$key]["endDate"] = date('Y-m-d h:i:s', $yesterday);
					$events[$key]["startDate"] = date('Y-m-d h:i:s',$yester2day);;
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
		if (empty($event["organization"])) {
			throw new CTKException("You must select an organization");
		} 
		$organizer = Organization::getById($event["organization"]);
		if (empty($organizer)) {
			throw new CTKException("The organization does not exist. Please check the organizer.");
		}

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
				$newEvent["geo"] = SIG::getGeoPositionByInseeCode($insee);
			}
		}

	    //sameAs      
	    if(!empty($params['description']))
	         $newEvent["description"] = $params['description'];
	    
	    PHDB::insert(self::COLLECTION,$newEvent);
	    
	    //add the creator as the admin and the first attendee
	    Link::attendee($newEvent["_id"], $params['userId'], true);
	    
	    Link::addOrganizer($params["organization"], $newEvent["_id"], $params['userId']);

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
	    Mail::validatePerson($creator,$newEvent);
	    
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

        return $eventOrganization;
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
						if($keyEv==$params["userId"]){
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
		$eventsAttending = PHDB::find(PHType::TYPE_EVENTS, $where);
		foreach ($eventsAttending as $key => $value) {
        	$profil = Document::getLastImageByKey($key, PHType::TYPE_EVENTS, Document::IMG_PROFIL);
        	if($profil!="")
        		$value['imagePath']=$profil;
        }
        return $eventsAttending;
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

		//address
		if ($eventFieldName == "address") {
			if(!empty($eventFieldValue["postalCode"]) && !empty($eventFieldValue["codeInsee"])) {
				$insee = $eventFieldValue["codeInsee"];
				$address = SIG::getAdressSchemaLikeByCodeInsee($insee);
				$event = array("address" => $address, "geo" => SIG::getGeoPositionByInseeCode($insee));
			} else {
				throw new CTKException("Error updating the Organization : address is not well formated !");			
			}
		} else {
			$event = array($eventFieldName => $eventFieldValue);	
		}

		$res = Event::updateEvent($eventId, $event, $userId);
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

        return $eventPeople;
	}
}
?>