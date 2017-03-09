<?php
class Link {
    
    const person2person = "follows";
    const person2organization = "memberOf";
    const organization2person = "members";
    const person2events = "events";
    const person2projects = "projects";
    const event2person = "attendees";
    const project2person = "contributors";
    const need2Item = "needs";

    //Link options
    const TO_BE_VALIDATED = "toBeValidated";
    const IS_ADMIN = "isAdmin";
    const IS_ADMIN_PENDING = "isAdminPending";
    const INVITED_BY_ID = "invitorId";
    const INVITED_BY_NAME = "invitorName";
    const IS_INVITING = "isInviting";

	/** TODO BOUBOULE  ----- TO DELETE ConnectParentToChild do it
	 * Add a member to an organization
	 * Create a link between the 2 actors. The link will be typed members and memberOf
	 * The memberOf should be an organization
	 * The member can be an organization or a person
	 * 2 entry will be added :
	 * - $memberOf.links.members["$memberId"]
	 * - $member.links.memberOf["$memberOfId"]
	 * @param String $memberOfId The Id memberOf (organization) where a member will be linked. 
	 * @param String $memberOfType The Type (should be organization) memberOf where a member will be linked. 
	 * @param String $memberId The member Id to add. It will be the member added to the memberOf
	 * @param String $memberType MemberType to add : could be an organization or a person
	 * @param String $userId The userId doing the action
     * @param Boolean $userAdmin if true the member will be added as an admin 
     * @param Boolean $pendingAdmin if true the member will be added as a pending admin 
	 * @return result array with the result of the operation
	 */ 
   /* public static function addMember($memberOfId, $memberOfType, $memberId, $memberType, 
                        $userId, $userAdmin = false, $userRole = "", $pendingAdmin=false) {
        
        $organization=Organization::getById($memberOfId);
        $listAdmin=Authorisation::listOrganizationAdmins($memberOfId);
       // print_r($listAdmin);
        //TODO SBAR => Change the boolean userAdmin to a role (admin, contributor, moderator...)        
        //1. Check if the $userId can manage the $memberOf
        $toBeValidated = false;
        $notification="";
        if (!Authorisation::isOrganizationAdmin($userId, $memberOfId)) {
            // Specific case when the user is added as an admin
            if (!$userAdmin) {
                // Add a toBeValidated tag on the link
                if(@$organization["links"]["members"] && count($organization["links"]["members"])!=0 && !empty($listAdmin)){
                	$toBeValidated = true;
					$notification="toBeValidated";
                }
            }
        }

        // Create link between both entity
		$res=self::connect($memberOfId, $memberOfType, $memberId, $memberType, $userId,"members",$userAdmin,$pendingAdmin, $toBeValidated, $userRole);
        $res=self::connect($memberId, $memberType, $memberOfId, $memberOfType, $userId,"memberOf",$userAdmin,$pendingAdmin, $toBeValidated, $userRole);
        //3. Send Notifications
	    //TODO - Send email to the member
        return array("result"=>true, "msg"=>"The member has been added with success", "memberOfId"=>$memberOfId, "memberId"=>$memberId,"notification" => $notification);
    }*/
    /**
	 * Add a contributor when a project is created
	 * Create a link between the creator person and project. The link will be typed contributors and projects
	 * The parent could be an organization then creation of link between organization and project
	 * Only creator is declare as admin of the project 
	 * @param String $creatorId - The creator Id to add. It will be the contributor added to the contributors as admin
	 * @param String $creatorType - The creator is a person (session)
	 * @param String $parentId - The id of the context. Could be the session user or an organization
	 * @param String $parentType - The type of the context. Could be the session user or an organization
     * @param String $projectId - Id of project linked to creator and parent if organization type
	 * @return result array with the result of the operation
	 */ 
	public static function addContributor($creatorId, $creatorType, $parentId, $parentType, $projectId){
		if($parentType==Organization::COLLECTION){
			$res=self::connect($parentId, $parentType, $projectId, Project::COLLECTION, $creatorId, "projects");
			$res=self::connect($projectId, Project::COLLECTION, $parentId, $parentType, $creatorId, "contributors");
		}
		$res=self::connect($creatorId, $creatorType, $projectId, Project::COLLECTION, $creatorId, "projects", true );
		$res=self::connect($projectId, Project::COLLECTION, $creatorId, $creatorType, $creatorId, "contributors", true );
		return array("result"=>true);
	}
    /** TODO BOUBOULE  - PLUS UTILISER ?? A SUPPRIMER
     * Remove a member of an organization
     * Delete a link between the 2 actors.
     * The memberOf should be an organization
     * The member can be an organization or a person
     * 2 entry will be deleted :
     * - $memberOf.links.members["$memberId"]
     * - $member.links.memberOf["$memberOfId"]
     * @param type $memberOfId The Id memberOf (organization) where a member will be deleted. 
     * @param type $memberOfType The Type (should be organization) memberOf where a member will be deleted. 
     * @param type $memberId The member Id to remove. It will be the member removed from the memberOf
     * @param type $memberType MemberType to remove : could be an organization or a person
     * @param type $userId $userId The userId doing the action
     * @return result array with the result of the operation
     */
    /*public static function removeMember($memberOfId, $memberOfType, $memberId, $memberType, $userId) {
        
        //0. Check if the $memberOfId and the $memberId exists
        $memberOf = Element::checkIdAndType($memberOfId, $memberOfType);
        $member = Element::checkIdAndType($memberId, $memberType);
        
        //1.1 the $userId can manage the $memberOf (admin)
        // Or the user can remove himself from a member list of an organization
        if (!Authorisation::isOrganizationAdmin($userId, $memberOfId)) {
            if ($memberId != $userId) {
                throw new CTKException("You are not admin of the Organization : ".$memberOfId);
            }
        }

        //2. Remove the links
        PHDB::update( $memberOfType, 
                   array("_id" => $memberOf["_id"]) , 
                   array('$unset' => array( "links.members.".$memberId => "") ));
 
        PHDB::update( $memberType, 
                       array("_id" => $member["_id"]) , 
                       array('$unset' => array( "links.memberOf.".$memberOfId => "") ));

        //3. Send Notifications
        //TODO - Send email to the member

        return array("result"=>true, "msg"=>"The member has been removed with success", "memberOfid"=>$memberOfId, "memberid"=>$memberId);
    }*/
    
    private static function checkIdAndType($id, $type, $actionType=null) {
		if ($type == Organization::COLLECTION) {
        	$res = Organization::getById($id); 
            if (@$res["disabled"] && $actionType != "disconnect") {
                throw new CTKException("Impossible to link something on a disabled organization");    
            }
        } else if ($type == Person::COLLECTION) {
        	$res = Person::getById($id);
        } else if ($type== Event::COLLECTION){
        	$res = Event::getById($id);
        } else if ($type== Project::COLLECTION){
        	$res = Project::getById($id);
        } else if ($type== Need::COLLECTION){
            $res = Need::getById($id);
        }else if ( $type == Poi::COLLECTION){
            $res = Poi::getById($id);
        } else if ( $type == ActionRoom::COLLECTION_ACTIONS){
            $res = ActionRoom::getActionById($id);
        } else if ( $type == Survey::COLLECTION) {
            $res = Survey::getById($id);
        }
        else {
        	throw new CTKException("Cannot manage this type : ".$type.Survey::COLLECTION);
        }
        if (empty($res)) throw new CTKException("The actor (".$id." / ".$type.") is unknown");

        return $res;
    }
    
    
    
    /**
     * Connection between 2 class : organization, person, event, projects, places
	 * Create a link between the 2 actors. The link will be typed as knows
	 * 1 entry will be added:
	 * - $origin.links.knows["$target"]
     * @param type $originId The Id of actor who wants to create a link with the $target
     * @param type $originType The Type (Organization or Person or Project or Places) of actor who wants to create a link with the $target
     * @param type $targetId The itemId that will be linked (Organization or Person or Project or Places)
     * @param type $targetType The Type (Organization or Person or Project or Places) that will be linked
     * @param type $userId The userId doing the action
     * @param type $connectType the name of connection in the target about user 
     * @param type $isAdmin boolean if new connection has admin role in the parent
     * @param type $pendingAdmin boolean if new connection has to be validated by an admin
     * @param type $isPending boolean if new connection add itself and need admin validation
     * @param type $role array if user added has a role in parent item
     * @return result array with the result of the operation
     */
    public static function connect($originId, $originType, $targetId, $targetType, $userId, $connectType,$isAdmin=false,$pendingAdmin=false,$isPending=false, $isInviting=false, $role="") {
	    //0. Check if the $originId and the $targetId exists
        $origin = Element::checkIdAndType($originId, $originType);
		$target = Element::checkIdAndType($targetId, $targetType);
        $links=array("links.".$connectType.".".$targetId.".type" => $targetType,"updated"=>time(),"modified" => new MongoDate(time()));
	    if($isPending){
		    //If event, refers has been invited by and user as to confirm its attendee to the event
		  /*  if($targetType==Event::COLLECTION || $originType==Event::COLLECTION){
		    	$links["links.".$connectType.".".$targetId.".".Link::INVITED_BY_ID] = $userId;
                $links["links.".$connectType.".".$targetId.".".Link::INVITED_BY_NAME] = Yii::app()->session["user"]["name"];
		    }*///else
		    	$links["links.".$connectType.".".$targetId.".".Link::TO_BE_VALIDATED] = $isPending;
	    }
        if($isInviting){
            $links["links.".$connectType.".".$targetId.".".Link::INVITED_BY_ID] = $userId;
            $links["links.".$connectType.".".$targetId.".".Link::INVITED_BY_NAME] = Yii::app()->session["user"]["name"];
            $links["links.".$connectType.".".$targetId.".".Link::IS_INVITING] = $isInviting;
        }/*else if($targetType==Event::COLLECTION || $originType==Event::COLLECTION){
		    PHDB::update($originType, 
                       array("_id" => $origin["_id"]) , 
                       array(
                        '$unset' => array("links.".$connectType.".".$targetId => ""),
                        '$set' => array( "updated"=>time(),"modified" => new MongoDate(time()) )
                        ));
	    }*/
        if($isAdmin){
        	$links["links.".$connectType.".".$targetId.".".Link::IS_ADMIN]=$isAdmin;
            if ($pendingAdmin) {
                $links["links.".$connectType.".".$targetId.".".Link::IS_ADMIN_PENDING] = true;
            }
        }
        if ($role != ""){
        	$links["links.".$connectType.".".$targetId.".roles"] = $role;
        }

	    //2. Create the links
        PHDB::update($originType, 
                       array("_id" => $origin["_id"]) , 
                       array('$set' => $links));
        
        return array("result"=>true, "msg"=>"The link knows has been added with success", "originId"=>$originId, "targetId"=>$targetId);
    }

    /**
     * Disconnect 2 actors : organization or Person
     * Delete a link knows between the 2 actors.
     * 1 entry will be deleted :
     * - $origin.links.knows["$target"]
     * @param type $originId The Id of actor where a link with the $target will be deleted
     * @param type $originType The Type (Organization or Person) of actor where a link with the $target will be deleted
     * @param type $targetId The actor that will be unlinked
     * @param type $targetType The Type (Organization or Person) that will be unlinked
     * @param type $userId The userId doing the action
     * @return result array with the result of the operation
     */
    public static function disconnect($originId, $originType, $targetId, $targetType, $userId, $connectType, $linkOption=null) {
        
        //0. Check if the $originId and the $targetId exists
        $origin = Element::checkIdAndType($originId, $originType, "disconnect");
        $target = Element::checkIdAndType($targetId, $targetType, "disconnect");
        $unset=array("links.".$connectType.".".$targetId => "");
        if ($linkOption != null && $linkOption==self::IS_ADMIN_PENDING){
            if(!@$origin["links"][$connectType][$targetId][self::TO_BE_VALIDATED]){
                $unset=array(
                        "links.".$connectType.".".$targetId.".".self::IS_ADMIN_PENDING => "",
                        "links.".$connectType.".".$targetId.".".self::IS_ADMIN => ""
                    );
            }
        }
        //2. Remove the links
        PHDB::update( $originType, 
                       array("_id" => $origin["_id"]) , 
                       array('$unset' => $unset ));

        //3. Send Notifications
        //TODO - Send email to the member

        return array("result"=>true, "msg"=>"The link follows has been removed with success", "originId"=>$originId, "targetId"=>$targetId, "parentEntity"=>$target);
    }

    /**
     * Check if two actors are connected with a links knows
     * @param type $originId The Id of actor to check the link with the $target
     * @param type $originType The Type (Organization or Person) of actor to check the link with the $target
     * @param type $targetId The actor to check that is linked
     * @param type $targetType The Type (Organization or Person) to check that is linked
     * @return boolean : true if the actors are connected, false else
     */
    public static function isConnected($originId, $originType, $targetId, $targetType) {
        $res = false;
        $where = array(
                    "_id"=>new MongoId($originId),
                    "links.follows.".$targetId =>  array('$exists' => 1));

        $originLinksKnows = PHDB::findOne($originType, $where);
        
        $res = isset($originLinksKnows);     

        return $res;
    }

    /** TODO BOUBOULE - OK TO DELETE ????
	 * 1 invitor invite a guest. The guest is not yet in the application
	 * Create a link between the invitor and the guest with the status toBeValidated
	 * The guest will receive a mail inviting him to create a ph account
	 * 1 entry will be added :
	 * - $invitor.links.knows["$guest"] = "status = toBeValidated"
	 * One Person or Organization will be created with basic information
	 * @param type $invitorId The actor Id who invite a guest
	 * @param type $invitorType The type (organization or person) who invite the guest
	 * @param type $guestId The actor Id that will invited
	 * @param type $guestType The type (organization or person) that will invited
	 * @param type $userId The userId doing the action
	 * @return result array with the result of the operation
	 */
    public static function invite($invitorId, $invitorType, $guestId, $guestType, $userId) {
 
        $result = array();
       
        return $result;
    }
    /**
	 * Add a helper to a need
	 * Create a link between need and helper. The link will be typed need and helpers
	 * @param type $organizerId The Id (organization) where an event will be linked. 
	 * @param type $eventId The Id (event) where an organization will be linked. 
	 * @param type $userId The user Id who try to link the organization to the event
	 * @return result array with the result of the operation
	 */
	public static function addHelper($needId, $helperId, $helperType, $booleanState) {
		if($booleanState==0){
			$isValidated=false;
		}
		else{
			$isValidated=true;
		}
		$linksPerson="links.needs.".$needId;
		$linksNeed="links.helpers.".$helperId;
		PHDB::update(Person::COLLECTION,
	   						array("_id" => new MongoId($helperId)),
	   						array('$set' => array($linksPerson.".type" => Need::COLLECTION, $linksPerson.".isValidated"=>$isValidated))
	   	);
	   	PHDB::update(Need::COLLECTION,
	   		array("_id"=>new MongoId($needId)),
	   		array('$set'=> array($linksNeed.".type"=>Person::COLLECTION, $linksNeed.".isValidated"=> $isValidated))
	   	);
	   	$res = array("result"=>true, "msg"=>"The event has been added with success");
		return $res;
	}
    /**
	 * Add a organizer to an event
	 * Create a link between the 2 actors. The link will be typed event and organizer
	 * @param type $organizerId The Id (organization) where an event will be linked. 
	 * @param type $eventId The Id (event) where an organization will be linked. 
	 * @param type $userId The user Id who try to link the organization to the event
	 * @return result array with the result of the operation
	 */
    public static function addOrganizer($organizerId, $organizerType, $eventId, $userId) {
		error_log("Try to add organizer ".$organizerId."/".$organizerType." from event ".$eventId);
        
        if ($organizerType == Organization::COLLECTION) {
            $isUserAdmin = Authorisation::isOrganizationAdmin($userId, $organizerId) || Authorisation::isUserSuperAdmin($userId); 
        } else if ($organizerType == Project::COLLECTION) {
            $isUserAdmin = Authorisation::isProjectAdmin($organizerId,$userId) || Authorisation::isUserSuperAdmin($userId);
        } else if ($organizerType == Person::COLLECTION) {
            $isUserAdmin = ($userId == $organizerId) || Authorisation::isUserSuperAdmin($userId);
        } else if ($organizerType == Event::NO_ORGANISER) { 
            $isUserAdmin = true;
        } else{
            throw new CTKException("Unknown organizer type = ".$organizerType);
        }
        
        if($isUserAdmin != true)
            $isUserAdmin = Authorisation::isOpenEdition($organizerId, $organizerType);

        if($isUserAdmin != true)
            return array("result"=>false, "msg"=>"You can't remove the organizer of this event !");

		if ($organizerType != Event::NO_ORGANISER) {
            PHDB::update($organizerType,
    					array("_id" => new MongoId($organizerId)),
    					array('$set' => array("links.events.".$eventId.".type" => Event::COLLECTION))
    		);
            PHDB::update(Event::COLLECTION,
                    array("_id"=>new MongoId($eventId)),
                    array('$set'=> array("links.organizer.".$organizerId.".type"=>$organizerType))
            );
        }
		//TODO SBAR : add notification for new organizer
		$res = array("result"=>true, "msg"=>"The event organizer has been added with success");
	   	
   		return $res;
   	}

    public static function removeOrganizer($organizerId, $organizerType, $eventId, $userId) {
        error_log("Try to remove organizer ".$organizerId."/".$organizerType." from event ".$eventId);
        
        if ($organizerType==Organization::COLLECTION) {
            $isUserAdmin = Authorisation::isOrganizationAdmin($userId, $organizerId) || Authorisation::isUserSuperAdmin($userId); 
        } else if ($organizerType==Project::COLLECTION) {
            $isUserAdmin = Authorisation::isProjectAdmin($organizerId,$userId) || Authorisation::isUserSuperAdmin($userId);
        } else if ($organizerType==Person::COLLECTION) {
            $isUserAdmin = ($userId == $organizerId) || Authorisation::isUserSuperAdmin($userId);
        } else if ($organizerType == Event::NO_ORGANISER) { 
            return array("result"=>true, "msg"=>"Nothing to remove for an unknown organizer");
        } else {
            throw new CTKException("Unknown organizer type = ".$organizerType);
        }

        if($isUserAdmin != true)
            $isUserAdmin = Authorisation::isOpenEdition($organizerId, $organizerType);

        if($isUserAdmin != true)
            return array("result"=>false, "msg"=>"You can't remove the organizer of this event !");
        

        if ($organizerType != Event::NO_ORGANISER) {
            PHDB::update(Event::COLLECTION,
                        array("_id"=>new MongoId($eventId)),
                        array('$unset'=> array("links.organizer.".$organizerId => ""))
            );
            //TODO SBAR : add notification for old organizer
            $res = array("result"=>true, "msg"=>"The organizer has been removed with success");
        }

        return $res;
    }

    /**
	* Link a person to an event when an event is create
	* Create a link between the 2 actors. The link will be typed event and organizer
	* @param type $eventId The Id (event) where a person will be linked. 
	* @param type $userId The user (person) Id who want to be link to the event
	* @param type $userAdmin (Boolean) to set if the member is admin or not
	* @param type $creator (Boolean) to set if attendee is creator of the event
	* @return result array with the result of the operation
	* When organization or project create an event, this parent is admin
	* Creator is automatically attendee but stays admin if he is parent admin 
	*/
    public static function attendee($eventId, $userId, $isAdmin = false, $creator = true){
   		//Link::addLink($userId, Person::COLLECTION, $eventId, PHType::TYPE_EVENTS, $userId, "events");
   		//Link::addLink($eventId, PHType::TYPE_EVENTS, $userId, Person::COLLECTION, $userId, "attendees");
   		$userType=Person::COLLECTION;
   		$link2person="links.events.".$eventId;
   		$link2event="links.attendees.".$userId;
   		$where2person=array($link2person.".type"=>Event::COLLECTION, $link2person.".isAdmin" => $isAdmin);
   		$where2event=array($link2event.".type" => $userType, $link2event.".isAdmin" => $isAdmin);
   		// TODO BOUBOULE - REMOVE THIS LINKS 
   		/*if($creator) {
	   		$where2person[$link2person.".isCreator"] = true;
	   		$where2event[$link2event.".isCreator"] = true;
   		}*/
		PHDB::update(Person::COLLECTION, 
          		array("_id" => new MongoId($userId)), 
                array('$set' => $where2person)
        );

        PHDB::update( PHType::TYPE_EVENTS, 
          		array("_id" => new MongoId($eventId)), 
                array('$set' => $where2event)
        );
    }

    /** TODO BOUBOULE - TO DELETE (NOT USED ANYMORE)
     * Connect 2 actors : Event, Person, Organization or Project
	 * Create a link between the 2 actors. The link will be typed as knows, attendee, event, project or contributor
	 * 1 entry will be added for example :
	 * - $origin.links.knows["$target"]
     * @param type $originId The Id of actor who wants to create a link with the $target
     * @param type $originType The Type (Organization, Person, Project or Event) of actor who wants to create a link with the $target
     * @param type $targetId The actor that will be linked
     * @param type $targetType The Type (Organization, Person, Project or Event) that will be linked
     * @param type $userId The userId doing the action (Optional)
     * @param type $connectType The link between the two actors
     * @return result array with the result of the operation
     */
   /* private static function addLink($originId, $originType, $targetId, $targetType, $userId= null, $connectType){

    	//0. Check if the $originId and the $targetId exists
        $origin = Element::checkIdAndType($originId, $originType);
        $target = Element::checkIdAndType($targetId, $targetType);

        //2. Create the links
        PHDB::update( $originType, 
                       array("_id" => $originId) , 
                       array('$unset' => array("links.".$connectType.".".$targetId => "") ));

        //3. Send Notifications
        //TODO - Send email to the member

        return array("result"=>true, "msg"=>"The link ".$connectType." has been added with success", "originId"=>$originId, "targetId"=>$targetId);
    }*/
    
	/*
	* function isLinked is generally called on Communecter and two times in models/Person.php
	* it permits to return true if a link between an entity(Person/Orga/Project/Event) and a person exists
	* @param type string $itemId is the id of the entity checked
	* @param type string $itemType is the type of the entity checked
	* @param type string $userId is the id of the user logged
	*/
    public static function isLinked($itemId, $itemType, $userId, $links=null) {
    	$res = false;
        if ($itemType == Person::COLLECTION) $linkType = self::person2person;
        elseif ($itemType == Organization::COLLECTION) $linkType = self::organization2person;
        elseif ($itemType == Event::COLLECTION) $linkType = self::event2person;
        elseif ($itemType == Project::COLLECTION) $linkType = self::project2person;
        else $linkType = "unknown";

        if(empty($links)){
           $item = PHDB::findOne( $itemType ,array("_id"=>new MongoId($itemId)));
           $links = @$item["links"] ;
        }
    	if(isset($links) && isset($links[$linkType])){
            foreach ($links[$linkType] as $key => $value) {
                if( $key == $userId) {
	                //exception for event when attendee is invited
	                if(!@$value["invitorId"])
    					$res = true;
    			}
    		}
    	}
    	return $res;
    }
	
	///// TODO BOUBOULE - AN ORGANIZER COULD BE A PROJECT OR AN ORGA OR A PERSON
	//// DOCUMENT THIS FUNCTION 
    public static function removeEventLinks($eventId){
    	$events = Event::getById($eventId);
    	foreach ($events["links"] as $type => $item) {
			foreach ($item as $id => $itemInfo) {
				if($type == "organizer"){
					$res = PHDB::update( Organization::COLLECTION, 
                  			array("_id" => new MongoId($id)) , 
                  			array('$unset' => array( "links.events.".$eventId => "") ) );
				}else{
					$res = PHDB::update( Person::COLLECTION, 
                  			array("_id" => new MongoId($id)) , 
                  			array('$unset' => array( "links.events.".$eventId => "") ) );
				}
			}
    	}
    	return $res;
    }
	
	// TODO BOUBOULE - COULD BE DELETED FOR A BETTER INTERPRETATION OF ROLE
    public static function removeRole($memberOfId, $memberOfType, $memberId, $memberType, $role, $userId) {
        
        //0. Check if the $memberOfId and the $memberId exists
        $memberOf = Element::checkIdAndType($memberOfId, $memberOfType);
        $member = Element::checkIdAndType($memberId, $memberType);
        
        //1.1 the $userId can manage the $memberOf (admin)
        // Or the user can remove himself from a member list of an organization
        if (!Authorisation::isOrganizationAdmin($userId, $memberOfId)) {
            if ($memberId != $userId) {
                throw new CTKException("You are not admin of the Organization : ".$memberOfId);
            }
        }

        //2. Remove the role
        PHDB::update( $memberOfType, 
                   array("_id" => $memberOf["_id"]) , 
                   array('$pull' => array( "links.members.".$memberId.".roles" => $role) ));
 
        //3. Remove the role
        PHDB::update($memberType,
        			array("_id"=> $member["_id"]),
        			array('$pull' => array("links.memberOf.".$memberOfId.".roles" => $role)) );
        
        return array("result"=>true, "msg"=>Yii::t("link","The member's role has been removed with success",null,Yii::app()->controller->module->id), "memberOfid"=>$memberOfId, "memberid"=>$memberId);
    }

    /** TODO BOUBOULE - TO DELETE WITH CTK/CONTROLLERS/PERSON/DISCONNECTACTION.PHP
     * Delete a link between the 2 actors.
     * @param $ownerId is the person who want to remowe a link
     * @param $targetId is the id of item we want to be unlink with
     * @param $ownerLink is the type of link between the owner and the target
     * @param $targetLink is the type of link between the target and the owner
     * @return result array with the result of the operation
     */
    public static function disconnectPerson($ownerId, $ownerType, $targetId, $targetType, $ownerLink, $targetLink = null) {
        
        //0. Check if the $owner and the $target exists
        $owner = Element::checkIdAndType($ownerId, $ownerType);
        $target = Element::checkIdAndType($targetId, $targetType);
       
        //1. Remove the links
        PHDB::update( $ownerType, 
                   array("_id" => new MongoId($ownerId)) , 
                   array('$unset' => array( "links.".$ownerLink.".".$targetId => "") ));
 
 		if(isset($targetLink) && $targetLink != null){
	        PHDB::update( $targetType, 
	                       array("_id" => new MongoId($targetId)) , 
	                       array('$unset' => array( "links.".$targetLink.".".$ownerId => "") ));
	    }

        //3. Send Notifications

        return array("result"=>true, "msg"=>"The link has been removed with success");
    }


     /** TODO BOUBOULE - NOT USE ANYMORE === TO DELETE 
     * Add a link between the 2 actors.
     * @param $ownerId is the person who want to add a link
     * @param $targetId is the id of item we want to be link with
     * @param $ownerLink is the type of link between the owner and the target
     * @param $targetLink is the type of link between the target and the owner
     * @return result array with the result of the operation
     */
    /*public static function connectPerson($ownerId, $ownerType, $targetId, $targetType, $ownerLink, $targetLink = null){
    	 //0. Check if the $owner and the $target exists
        $owner = Element::checkIdAndType($ownerId, $ownerType);
        $target = Element::checkIdAndType($targetId, $targetType);

        PHDB::update( $ownerType, 
           array("_id" => new MongoId($ownerId)) , 
           array('$set' => array( "links.".$ownerLink.".".$targetId.".type" => $targetType) ));

        //Mail::newConnection();

        if(isset($targetLink) && $targetLink != null){
         	$newObject = array('type' => $ownerType );
	        PHDB::update( $targetType, 
			               array("_id" => new MongoId($targetId)) , 
			               array('$set' => array( "links.".$targetLink.".".$ownerId.".type" => $ownerType) ));
	    }

        return array("result"=>true, "msg"=>"The link has been added with success");
    }*/
    
     /**
     * Add a link between the 2 entity (person to an entity).
     * @param $parentId is the id of parent where we add a link followers
     * @param $parentType is the type of parent wher user wants to follows
     * @param $child is an array having id, type of user who wants to follows
     * @return result array with the result of the operation
     */
	public static function follow($parentId, $parentType, $child){
		$childId = @$child["childId"];
        $childType = $child["childType"];
        $levelNotif=null;
		if($parentType == Organization::COLLECTION){
			$parentData = Organization::getById($parentId);
			$parentController = Organization::CONTROLLER;
		}
		else if ($parentType == Project::COLLECTION){
			$parentData = Project::getById($parentId);			
			$parentController=Project::CONTROLLER;
		} 
		else if ($parentType == Person::COLLECTION){
			$parentData = Person::getById($parentId);			
			$parentController=Person::CONTROLLER;
            $levelNotif="user";
		} else {
            throw new CTKException(Yii::t("common","Can not manage the type ").$parentType);
        }
        //Retrieve the child info
        $pendingChild = Person::getById($childId);
        if (!$pendingChild) {
            return array("result" => true, "msg" => "Something went wrong ! Impossible to find the children ".$childId);
        }
		$parentConnectAs = "followers";
		$childConnectAs = "follows";
		$verb = ActStr::VERB_FOLLOW;
		$msg=Yii::t("common","You are following")." ".$parentData["name"];
		Link::connect($parentId, $parentType, $childId, $childType,Yii::app()->session["userId"], $parentConnectAs);
		Link::connect($childId, $childType, $parentId, $parentType, Yii::app()->session["userId"], $childConnectAs);
        if($parentType==Person::COLLECTION)
            Mail::follow($parentData, $parentType);
        //else
          //  Mail::follow($element, $elementType, $listOfMail);
        Notification::constructNotification($verb, $pendingChild , array("type"=>$parentType,"id"=> $parentId,"name"=>$parentData["name"]), null, $levelNotif);
		//Notification::actionOnPerson($verb, ActStr::ICON_SHARE, $pendingChild , array("type"=>$parentType,"id"=> $parentId,"name"=>$parentData["name"]));
		return array( "result" => true , "msg" => $msg, "parentEntity" => $parentData );
	}
	 /**
     * Check and remove the link "follows" if a user already follow an entity and will become a member or contributor or knows
     * @param $parentId is the id of parent where we add a link followers
     * @param $parentType is the type of parent wher user wants to follows
     * @param $child is an array having id, type of user who wants to follows
     * @return result array with the result of the operation
     */
	public static function checkAndRemoveFollowLink($parentId,$parentType,$childId,$childType){
		if($childType==Person::COLLECTION){
			$person=Person::getById($childId);
			if(isset($person["links"]["follows"][$parentId]) && $person["links"]["follows"][$parentId]["type"] == $parentType){
				Link::disconnect($childId, $childType, $parentId, $parentType,Yii::app()->session['userId'], "follows");
				Link::disconnect($parentId, $parentType, $childId, $childType,Yii::app()->session['userId'], "followers");
			}
		}
	}
    /**
	 * Author @clement.damiens@gmail.com && @sylvain.barbot@gmail.com
     * Connect A Child to parent with a link. 
     * Child can be a person or an organization.
     * Parent can be an Organization or a Project
     * The child can be set as admin of the parent
     * 
     * @param String $parentId Id of the parent to link to
     * @param String $parentType type of the parent to link to
     * @param Array $child array discribing a child to link to the parent. 
     *                         childId => the id of the child. Could be empty if it's an invitation
     *                         childType => the type of the child. Mandatory
     *                         childName => Used if it's an invitation
     *                         childEmail => Used if it's an invitation
     * @param boolean $isConnectingAdmin Is the child admin ? 
     * @param String $userId The userId doing the action
     * @param string $userRole The role the child inside the parent. Not use yet.
     * @return array of result ()
     */
	public static function connectParentToChild($parentId, $parentType, $child, $isConnectingAdmin, $userId, $userRole="") {
        $typeOfDemand="admin";
        $childId = @$child["childId"];
        $childType = $child["childType"];
        $isInviting = false;
        $levelNotif = null;

		if($parentType == Organization::COLLECTION){
			$parentData = Organization::getById($parentId);
			$usersAdmin = Authorisation::listAdmins($parentId,  $parentType, false);
			$parentUsersList = Organization::getMembersByOrganizationId($parentId,  "all", null);
			$parentController = Organization::CONTROLLER;
			$parentConnectAs = "members";
			$childConnectAs = "memberOf";
			if(!$isConnectingAdmin)
				$typeOfDemand = "member";
		}
		else if ($parentType == Project::COLLECTION){
			$parentData = Project::getById($parentId);			
			$usersAdmin = Authorisation::listAdmins($parentId,  $parentType, false);
			$parentUsersList = Project::getContributorsByProjectId( $parentId ,"all", null ) ;
			$parentController=Project::CONTROLLER;
			$parentConnectAs="contributors";
			$childConnectAs="projects";
			if(!$isConnectingAdmin)
				$typeOfDemand = "contributor";
		} 
		else if ($parentType == Event::COLLECTION){
			$parentData = Event::getById($parentId);	
			$usersAdmin = Authorisation::listAdmins($parentId,  $parentType, false);
			//print_r($usersAdmin);
			$parentUsersList = Event::getAttendeesByEventId( $parentId ,"all", null);
			$parentController = Event::CONTROLLER;
			$parentConnectAs="attendees";
			$childConnectAs="events";
			if(!$isConnectingAdmin)
				$typeOfDemand = "attendee";
		} else {
            throw new CTKException(Yii::t("common","Can not manage the type ").$parentType);
        }
        
        if (!$parentData) {
            return array("result" => false, "msg" => "Unknown ".$parentController.". Please check your parameters !");
        }
        
        //Check if the user is admin
		$actionFromAdmin=in_array($userId,$usersAdmin);
		
        if ($childType == Organization::COLLECTION) {
            $class = "Organization";
        //ou Child type Person
        } else if ($childType == Person::COLLECTION) {
            $class = "Person";
        } else {
            return array("result" => false, "msg" => "Unknown ".$childType.". Please check your parameters !");
        }
		
        //if the childId is empty => it's an invitation
        //Let's create the child
        if (empty($childId)) {
            $invitation = ActStr::VERB_INVITE;
            $child = array(
                'name' => @$child["childName"],
                'email' => @$child["childEmail"],
                'invitedBy'=>Yii::app()->session["userId"]
            );     

            //Child Type d'organization
            if ($childType == Organization::COLLECTION) {
                $child["type"] = (isset($_POST["organizationType"])) ? $_POST["organizationType"] : "";
            }

            //create an entry in the right collection type
            $result = $class::createAndInvite($child);
            if ($result["result"]) {
                $childId = $result["id"];
            } else 
                return $result;
        }

        //Retrieve the child info
        $pendingChild = $class::getById($childId);
		$pendingChild["id"] = $childId;
        if (!$pendingChild) {
            return array("result" => false, "msg" => "Something went wrong ! Impossible to find the children ".$childId);
        }
		//Check if the child is already link to the parent with the connectType
		$alreadyLink=false;
		if($typeOfDemand != "admin"){
			if(@$parentUsersList[$childId] && $userId != $childId)
				$alreadyLink=true;
		}else{
			if(@$parentUsersList[$childId] && @$parentUsersList[$childId]["isAdmin"])
				$alreadyLink=true;
		}
		if($alreadyLink)
			return array("result" => false, "type"=>"info", "msg" => $pendingChild["name"]." ".Yii::t("common", "is already ".$typeOfDemand." of")." ".Yii::t("common","this ".$parentController)." !");
			
        //Check : You are already member or admin
		if ($actionFromAdmin && $userId == $childId) 
			return array("result" => false, "type"=>"info", "msg" => Yii::t("common", "You are already admin of")." ".Yii::t("common","this ".$parentController)." !");
		
        if($isConnectingAdmin==true)
            $levelNotif="asAdmin";
        else
            $levelNotif="asMember";
        //First case : The parent doesn't have an admin yet or it is an action from an admin or it is an event: 
		if (count($usersAdmin) == 0 || $actionFromAdmin || $parentType == Event::COLLECTION) {
            //the person is automatically added as member (admin or not) of the parent
            //var_dump("here");
            if ($actionFromAdmin || ($parentType == Event::COLLECTION && $childId != Yii::app()->session["userId"])) {
	            //If admin add as admin or member
                $verb = ActStr::VERB_INVITE; 
	            if($isConnectingAdmin==true){
					//$verb = ActStr::VERB_ACCEPT;
					$msg=$pendingChild["name"]." ".Yii::t("common","is well invited to administrate of")." ".$parentData["name"];
					$pendingChild["isAdmin"]=true;
				} else 
					//$verb = ActStr::VERB_ACCEPT;
					$msg=$pendingChild["name"]." ".Yii::t("common","is well invited to join")." ".$parentData["name"];
                $pendingChild["isInviting"]=true;
				$toBeValidated=false;
                $isInviting=true;
                Mail::someoneInviteYouToBecome($parentData, $parentType, $pendingChild, $typeOfDemand);
			} else{
                // Verb Confirm in ValidateLink
				$verb = ActStr::VERB_JOIN;
				$toBeValidated=false;
				//if($childId==Yii::app()->session["userId"]){
				$msg= Yii::t("common", "You are now ".$typeOfDemand." of")." ".Yii::t("common","this ".$parentController);
                /*}else{
					$invitation = ActStr::VERB_INVITE;
					if($typeOfDemand != "admin"){
						$toBeValidated=true;
						$pendingChild["toBeValidated"]=true;
					}else 
						$verb = ActStr::VERB_CONFIRM;
					$msg= $pendingChild["name"]." ".Yii::t("common","is now ".$typeOfDemand." of")." ".$parentData["name"];
				}*/
			}
			// Check if links follows exists than if true, remove of follows and followers links
			self::checkAndRemoveFollowLink($parentId,$parentType,$childId,$childType);
			$toBeValidatedAdmin=false;

            /*if ($isConnectingAdmin && $parentType == Event::COLLECTION) {
                $verb = ActStr::VERB_AUTHORIZE;
                $toBeValidatedAdmin=true;
                $toBeValidated=false;
            }*/
           
		//Second case : Not an admin doing the action.
        } else {      
            //Someone ask to become an admin
            $verb = ActStr::VERB_ASK;
            if ($isConnectingAdmin) {
    			//Admin validation process
    			$toBeValidatedAdmin=true;
    			$toBeValidated=false;
    			$pendingChild["isAdminPending"]=true;
                if(!@$parentUsersList[$childId]){
                    $toBeValidated=true;
                    $pendingChild["toBeValidated"]=true;
                }
            } else {
                $toBeValidatedAdmin=false;
                $toBeValidated=true;
                $pendingChild["toBeValidated"]=true;
            }
            //Notification and email are sent to the admin(s)
             //CREATE VARIABLE OF EMAIL AND GENERALIZE EMAIL someoneDemandToBecome || someoneInvitingYouTo
            $listofAdminsEmail = array();
            foreach ($usersAdmin as $adminId) {
                $currentAdmin = Person::getEmailById($adminId);
                array_push($listofAdminsEmail, $currentAdmin["email"]);
            }
            //CREATE VARIABLE OF EMAIL AND GENERALIZE EMAIL someoneDemandToBecome || someoneInvitingYouTo
            if (count($listofAdminsEmail))
                Mail::someoneDemandToBecome($parentData, $parentType, $pendingChild, $listofAdminsEmail, $typeOfDemand);
            //TODO - Notification
            $msg = Yii::t("common","Your request has been sent to other admins.");
            // After : the 1rst existing Admin to take the decision will remove the "pending" to make a real admin
        } 
        
		Link::connect($parentId, $parentType, $childId, $childType,Yii::app()->session["userId"], $parentConnectAs, $isConnectingAdmin, $toBeValidatedAdmin, $toBeValidated, $isInviting, $userRole);
		Link::connect($childId, $childType, $parentId, $parentType, Yii::app()->session["userId"], $childConnectAs, $isConnectingAdmin, $toBeValidatedAdmin, $toBeValidated, $isInviting, $userRole);
        Notification::constructNotification($verb, $pendingChild , array("type"=>$parentType,"id"=> $parentId,"name"=>$parentData["name"]), null, $levelNotif);
        //Notification::actionOnPerson($verb, ActStr::ICON_SHARE, $pendingChild , array("type"=>$parentType,"id"=> $parentId,"name"=>$parentData["name"]), $invitation);
		$res = array("result" => true, "msg" => $msg, "parent" => $parentData,"parentType"=>$parentType,"newElement"=>$pendingChild, "newElementType"=> $childType );
		return $res;
	}
	
    /**
     * Description
     * @param type $parentId 
     * @param type $parentType 
     * @param type $childId 
     * @param type $childType 
     * @param type $linkOption 
     * @param type $userId 
     * @return type
     */
    public static function validateLink($parentId, $parentType, $childId, $childType, $linkOption, $userId) {
        
        $res = array( "result" => false , "msg" => Yii::t("common","Something went wrong!" ));

        if ($childType == Organization::COLLECTION) {
            $class = "Organization";
            if ($linkOption == Link::IS_ADMIN_PENDING) {
                return array("result" => false, "msg" => "Impossible to validate an organization as admin !");
            }
        //ou Child type Person
        } else if ($childType == Person::COLLECTION) {
            $class = "Person";
        } else {
            return array("result" => false, "msg" => "Unknown ".$childType.". Please check your parameters !");
        }
        //Retrieve the child info
        $pendingChild = $class::getById($childId);
        if (!$pendingChild) {
            return array("result" => false, "msg" => "Something went wrong ! Impossible to find the children ".$childId);
        }

        //Retrieve parent and connection
        if ($parentType==Organization::COLLECTION) {
            $parent = Organization::getById( $parentId );
            $connectTypeOf="memberOf";
            $connectType="members";
            $usersAdmin = Authorisation::listAdmins($parentId,  $parentType, false);
            $typeOfDemand="member";
        } else if ($parentType==Project::COLLECTION) {
            $parent = Project::getById( $parentId );            
            $connectTypeOf = "projects";
            $connectType = "contributors";
            $typeOfDemand="contributor";
            $usersAdmin = Authorisation::listAdmins($parentId,  $parentType, false);
        } else if ($parentType==Event::COLLECTION) {
            $parent = Event::getById( $parentId );            
            $connectTypeOf = "events";
            $connectType = "attendees";
            $typeOfDemand="attendee";
            $usersAdmin = Authorisation::listAdmins($parentId,  $parentType, false);
        } else {
            throw new CTKException(Yii::t("common","Can not manage the type ").$parentType);
        }
        if($linkOption==Link::IS_ADMIN_PENDING)
            $typeOfDemand="admin";
        //Check if the user is admin
        $actionFromAdmin=in_array($userId,$usersAdmin);
        //Check the link exists in order to update it
        if (@$parent["links"][$connectType][$childId][$linkOption] && 
            @$pendingChild["links"][$connectTypeOf][$parentId][$linkOption]) {
	        self::checkAndRemoveFollowLink($parentId,$parentType,$childId,$childType);
            self::updateLink($parentType, $parentId, $childId, $childType, $connectType, $connectTypeOf, $linkOption);
            if($linkOption == Link::IS_ADMIN_PENDING && 
                (@$parent["links"][$connectType][$childId][Link::TO_BE_VALIDATED] && 
            @$pendingChild["links"][$connectTypeOf][$parentId][Link::TO_BE_VALIDATED]))
                self::updateLink($parentType, $parentId, $childId, $childType, $connectType, $connectTypeOf, Link::TO_BE_VALIDATED);
        } else {
            return array( "result" => false , 
                "msg" => "The link ".$linkOption." does not exist between ".@$parent["name"]." and ".@$pendingChild["name"]);
        }

        $user = array(
            "id"=>$childId,
            "type"=>$childType,
            "name" => $pendingChild["name"],
        );
        
        //Notifications
        if ($linkOption == Link::IS_ADMIN_PENDING) {
            //Notification::actionOnPerson ( ActStr::VERB_CONFIRM, ActStr::ICON_SHARE, $user, array("type"=>$parentType,"id"=> $parentId,"name"=>$parent["name"]));
            $verb=ActStr::VERB_ACCEPT;
            $levelNotif = "asAdmin";
            $msg = $pendingChild["name"]." has been validated as admin of ".$parent["name"];
            //MAIL TO CHILDREN 
            //REMOVE ASK NOTIF FOR COMMUNITY
        } else if ($linkOption == Link::TO_BE_VALIDATED) {
            //Notification::actionOnPerson ( ActStr::VERB_ACCEPT, ActStr::ICON_SHARE, $user, array("type"=>$parentType,"id"=> $parentId,"name"=>$parent["name"]));
            $verb=ActStr::VERB_ACCEPT;
            $levelNotif="asMember";
            $msg = $pendingChild["name"]." has been validated as member of ".$parent["name"];
            //MAIL TO CHILDREN
            //REMOVE ASK NOTIF FOR COMMUNITY
        } else if ($linkOption == Link::IS_INVITING){
            $verb=ActStr::VERB_CONFIRM;
            $msg = "Your answer has been succesfully register";
            if(@$pendingChild["links"][$connectType][$parentId]["isAdmin"] && @$parent["links"][$connectType][$childId]["isAdmin"])
                $levelNotif="asAdmin";
            else
                $levelNotif="asMember";
            //MAIL TO INVITOR
        }
        if($verb==ActStr::VERB_ACCEPT)
            Mail::someoneConfirmYouTo($parent, $parentType, $pendingChild, $typeOfDemand);
        Notification::constructNotification($verb, $user , array("type"=>$parentType,"id"=> $parentId,"name"=>$parent["name"]), null, $levelNotif);
        return array( "result" => true , "msg" => Yii::t("common",$msg) );
    }

	/*
	* This function is similar to disconnect but he just remove a value as pending, isAdminPending, isPending
	* @valueUpdate is string to know which line disconnect
	* Using for acceptAsAdmin, AcceptAsMember, etc...
	*/
	private static function updateLink($parentType,$parentId,$userId,$userType,$connectType,$connectTypeOf,$valueUpdate){
		PHDB::update($parentType, 
                 array("_id" => new MongoId($parentId)) , 
                array('$unset' => array("links.".$connectType.".".$userId.".".$valueUpdate => ""))
                );
        PHDB::update($userType, 
                array("_id" => new MongoId($userId)) , 
                array('$unset' => array("links.".$connectTypeOf.".".$parentId.".".$valueUpdate => ""))
                );
		return array("result"=>true, "msg"=>"The link has been added with success");
	}
} 
?>