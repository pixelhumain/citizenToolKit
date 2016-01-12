<?php
class Link {
    
    const person2person = "knows";
    const person2organization = "memberOf";
    const organization2person = "members";
    const person2events = "events";
    const person2projects = "projects";
    const event2person = "attendees";
    const project2person = "contributors";
    const need2Item = "needs";

	/**
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
    public static function addMember($memberOfId, $memberOfType, $memberId, $memberType, 
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
		$res=self::connect($memberOfId, $memberOfType, $memberId, $memberType, $userId,"members",$userAdmin,$pendingAdmin,$toBeValidated, $userRole);
		$res=self::connect($memberId, $memberType, $memberOfId, $memberOfType, $userId,"memberOf",$userAdmin,$pendingAdmin,$toBeValidated, $userRole);
        //3. Send Notifications
	    //TODO - Send email to the member

        return array("result"=>true, "msg"=>"The member has been added with success", "memberOfId"=>$memberOfId, "memberId"=>$memberId,"notification" => $notification);
    }

    /**
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
    public static function removeMember($memberOfId, $memberOfType, $memberId, $memberType, $userId) {
        
        //0. Check if the $memberOfId and the $memberId exists
        $memberOf = Link::checkIdAndType($memberOfId, $memberOfType);
        $member = Link::checkIdAndType($memberId, $memberType);
        
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
    }

    private static function checkIdAndType($id, $type) {
		if ($type == Organization::COLLECTION) {
        	$res = Organization::getById($id); 
        } else if ($type == Person::COLLECTION) {
        	$res = Person::getById($id);
        } else if ($type== Event::COLLECTION){
        	$res = Event:: getById($id);
        } else if ($type== Project::COLLECTION){
        	$res = Project:: getById($id);
        } else if ($type== Need::COLLECTION){
        	$res = Need:: getById($id);
        } else {
        	throw new CTKException("Can not manage this type of MemberOf : ".$type);
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
     * @parem type $pendingAdmin boolean if new connection has to be validate by an admin
     * @param type $isPending boolean if new connection add itself and need admin validation
     * @param type $role array if user added has a role in parent item
     * @return result array with the result of the operation
     */
    public static function connect($originId, $originType, $targetId, $targetType, $userId, $connectType,$isAdmin=false,$pendingAdmin=false,$isPending=false, $role="") {
	    $links=array("links.".$connectType.".".$targetId.".type" => $targetType);
	    if($isPending){
		    $links["links.".$connectType.".".$targetId.".toBeValidated"] = $isPending;
	    }
        if($isAdmin){
        	$links["links.".$connectType.".".$targetId.".isAdmin"]=$isAdmin;
			if ($pendingAdmin) {
                $links["links.".$connectType.".".$targetId.".isAdminPending"] = true;
            }
        }
        if ($role != ""){
        	$links["links.".$connectType.".".$targetId.".roles"] = $role;
        }
        //0. Check if the $originId and the $targetId exists
        $origin = Link::checkIdAndType($originId, $originType);
		$target = Link::checkIdAndType($targetId, $targetType);
	    //2. Create the links
        PHDB::update($originType, 
                       array("_id" => $origin["_id"]) , 
                       array('$set' => $links));
        //3. Send Notifications
	    //TODO - Send email to the member
		
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
    public static function disconnect($originId, $originType, $targetId, $targetType, $userId, $connectType) {
        
        //0. Check if the $originId and the $targetId exists
        $origin = Link::checkIdAndType($originId, $originType);
        $target = Link::checkIdAndType($targetId, $targetType);

        //2. Remove the links
        PHDB::update( $originType, 
                       array("_id" => $origin["_id"]) , 
                       array('$unset' => array("links.".$connectType.".".$targetId => "") ));

        //3. Send Notifications
        //TODO - Send email to the member

        return array("result"=>true, "msg"=>"The link knows has been removed with success", "originId"=>$originId, "targetId"=>$targetId);
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
                    "links.knows.".$targetId =>  array('$exists' => 1));

        $originLinksKnows = PHDB::findOne($originType, $where);
        
        $res = isset($originLinksKnows);     

        return $res;
    }

    /** 
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
	 * Add a organization to an event
	 * Create a link between the 2 actors. The link will be typed event and organizer
	 * @param type $organizerId The Id (organization) where an event will be linked. 
	 * @param type $eventId The Id (event) where an organization will be linked. 
	 * @param type $userId The user Id who try to link the organization to the event
	 * @return result array with the result of the operation
	 */
    public static function addOrganizer($organizerId, $organizerType, $eventId, $userId) {
		$res = array("result"=>false, "msg"=>"You can't add this event to this organization");
		if ($organizerType=="organizations"){
	   		$isUserAdmin = Authorisation::isOrganizationAdmin($userId, $organizerId);
	   		if($isUserAdmin){
	   			PHDB::update(Organization::COLLECTION,
	   						array("_id" => new MongoId($organizerId)),
	   						array('$set' => array("links.events.".$eventId.".type" => PHType::TYPE_EVENTS))
	   			);
	   			PHDB::update(PHType::TYPE_EVENTS,
	   						array("_id"=>new MongoId($eventId)),
	   						array('$set'=> array("links.organizer.".$organizerId.".type"=>Organization::COLLECTION))
	   			);
	   			$res = array("result"=>true, "msg"=>"The event has been added with success");
	   		};
	   	}
	   	else if ($organizerType=="projects"){
		   	$isUserAdmin = Authorisation::isProjectAdmin($organizerId,$userId);
	   		if($isUserAdmin){
	   			PHDB::update(Project::COLLECTION,
	   						array("_id" => new MongoId($organizerId)),
	   						array('$set' => array("links.events.".$eventId.".type" => Event::COLLECTION))
	   			);
	   			PHDB::update(Event::COLLECTION,
	   						array("_id"=>new MongoId($eventId)),
	   						array('$set'=> array("links.organizer.".$organizerId.".type"=>Project::COLLECTION))
	   			);
	   			$res = array("result"=>true, "msg"=>"The event has been added with success");
	   		};
	   	}
	   	else {
		   	PHDB::update(Person::COLLECTION,
	   						array("_id" => new MongoId($organizerId)),
	   						array('$set' => array("links.events.".$eventId.".type" => Event::COLLECTION))
	   			);
   			PHDB::update(Event::COLLECTION,
   						array("_id"=>new MongoId($eventId)),
   						array('$set'=> array("links.organizer.".$organizerId.".type"=>Person::COLLECTION))
   			);
   			$res = array("result"=>true, "msg"=>"The event has been added with success");

	   	}
   		return $res;
   	}

    /**
	* Link a person to an event
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
   		Link::addLink($userId, Person::COLLECTION, $eventId, PHType::TYPE_EVENTS, $userId, "events");
   		Link::addLink($eventId, PHType::TYPE_EVENTS, $userId, Person::COLLECTION, $userId, "attendees");
   		$userType=Person::COLLECTION;
   		$link2person="links.events.".$eventId;
   		$link2event="links.attendees.".$userId;
   		$where2person=array($link2person.".type"=>Event::COLLECTION, $link2person.".isAdmin" => $isAdmin);
   		$where2event=array($link2event.".type" => $userType, $link2event.".isAdmin" => $isAdmin);
   		if($creator) {
	   		$where2person[$link2person.".isCreator"] = true;
	   		$where2event[$link2event.".isCreator"] = true;
   		}
		PHDB::update(Person::COLLECTION, 
          		array("_id" => new MongoId($userId)), 
                array('$set' => $where2person)
        );

        PHDB::update( PHType::TYPE_EVENTS, 
          		array("_id" => new MongoId($eventId)), 
                array('$set' => $where2event)
        );
    }

    /**
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
    private static function addLink($originId, $originType, $targetId, $targetType, $userId= null, $connectType){

    	//0. Check if the $originId and the $targetId exists
        $origin = Link::checkIdAndType($originId, $originType);
        $target = Link::checkIdAndType($targetId, $targetType);

        //2. Create the links
        PHDB::update( $originType, 
                       array("_id" => $originId) , 
                       array('$unset' => array("links.".$connectType.".".$targetId => "") ));

        //3. Send Notifications
        //TODO - Send email to the member

        return array("result"=>true, "msg"=>"The link ".$connectType." has been added with success", "originId"=>$originId, "targetId"=>$targetId);
    }

    public static function isLinked($itemId, $itemType, $userId) {
    	$res = false;
        if ($itemType == Person::COLLECTION) $linkType = self::person2person;
        elseif ($itemType == Organization::COLLECTION) $linkType = self::organization2person;
        elseif ($itemType == Event::COLLECTION) $linkType = self::event2person;
        elseif ($itemType == Project::COLLECTION) $linkType = self::project2person;
        else $linkType = "unknown";

    	$item = PHDB::findOne( $itemType ,array("_id"=>new MongoId($itemId)));
    	if(isset($item["links"]) && isset($item["links"][$linkType])){
            foreach ($item["links"][$linkType] as $key => $value) {
                if( $key == $userId) {
    				$res = true;
    			}
    		}
    	}
    	return $res;
    }

    public static function removeEventLinks($eventId){
    	$events = Event::getById($eventId);
    	foreach ($events["links"] as $type => $item) {
			foreach ($item as $id => $itemInfo) {
				if($type == "organizer"){
					$res = PHDB::update( Organization::COLLECTION, 
                  			array("_id" => new MongoId($id)) , 
                  			array('$unset' => array( "links.events.".$eventId => "") ));
				}else{
					$res = PHDB::update( Person::COLLECTION, 
                  			array("_id" => new MongoId($id)) , 
                  			array('$unset' => array( "links.events.".$eventId => "") ));
				}
			}
    	}
    	return $res;
    }

    public static function removeRole($memberOfId, $memberOfType, $memberId, $memberType, $role, $userId) {
        
        //0. Check if the $memberOfId and the $memberId exists
        $memberOf = Link::checkIdAndType($memberOfId, $memberOfType);
        $member = Link::checkIdAndType($memberId, $memberType);
        
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

    /**
     * Delete a link between the 2 actors.
     * @param $ownerId is the person who want to remowe a link
     * @param $targetId is the id of item we want to be unlink with
     * @param $ownerLink is the type of link between the owner and the target
     * @param $targetLink is the type of link between the target and the owner
     * @return result array with the result of the operation
     */
    public static function disconnectPerson($ownerId, $ownerType, $targetId, $targetType, $ownerLink, $targetLink = null) {
        
        //0. Check if the $owner and the $target exists
        $owner = Link::checkIdAndType($ownerId, $ownerType);
        $target = Link::checkIdAndType($targetId, $targetType);
       
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


     /**
     * Add a link between the 2 actors.
     * @param $ownerId is the person who want to add a link
     * @param $targetId is the id of item we want to be link with
     * @param $ownerLink is the type of link between the owner and the target
     * @param $targetLink is the type of link between the target and the owner
     * @return result array with the result of the operation
     */
    public static function connectPerson($ownerId, $ownerType, $targetId, $targetType, $ownerLink, $targetLink = null){
    	 //0. Check if the $owner and the $target exists
        $owner = Link::checkIdAndType($ownerId, $ownerType);
        $target = Link::checkIdAndType($targetId, $targetType);

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
    }
	
	/**
	 * Add someone as admin of an organization.
	 * If there are already admins of the organization, they will receive a notification and email to 
	 * accept or not the new admin
	 * @param String $idOrganization The id of the organization
	 * @param String $idPerson The id of the person asking to become an admin
	 * @param String $userId The userId doing the action
	 * @return array of result (result => bool, msg => string)
	 */
	public static function addPersonAsAdmin($parentId, $parentType, $personId, $userId) {

		if($parentType == Organization::COLLECTION){
			$parentData = Organization::getById($parentId);
			$usersAdmin = Authorisation::listOrganizationAdmins($parentId, false);
			$parentController=Organization::CONTROLLER;
		}
		else if ($parentType == Project::COLLECTION){
			$parentData = Project::getById($parentId);			
			$usersAdmin = Authorisation::listAdmins($parentId,  $parentType, false);
			$parentController=Project::CONTROLLER;
		}
		
		$pendingAdmin = Person::getById($personId);
		if (!$parentData || !$pendingAdmin) {
			return array("result" => false, "msg" => "Unknown ".$parentController." or person. Please check your parameters !");
		} else {
			$res = array("result" => true, "msg" => Yii::t("common", "You are now admin of")." ".Yii::t("common","this ".$parentController), "parent" => $parentData);
		}
		//First case : The organization doesn't have an admin yet : the person is automatically added as admin
		
		if (in_array($personId, $usersAdmin)) 
			return array("result" => false, "msg" => Yii::t("common", "Your are already admin of")." ".Yii::t("common","this ".$parentController)." !");

		if (count($usersAdmin) == 0) {
			if($parentType==Organization::COLLECTION)
				Link::addMember($parentId, $parentType, $personId, Person::COLLECTION, $userId, true, "", false);
			else if ($parentType == Project::COLLECTION){
				Link::connect($parentId, $parentType, $personId, Person::COLLECTION,Yii::app() -> session["userId"], "contributors", true,false);
				Link::connect($personId, Person::COLLECTION, $parentId, $parentType, Yii::app() -> session["userId"], "projects", true, false);
			}
			Notification::actionOnPerson ( ActStr::VERB_JOIN, ActStr::ICON_SHARE, $pendingAdmin , array("type"=>$parentType,"id"=> $parentId,"name"=>$parentData["name"]) ) ;
		} else {
			//Second case : there is already an admin (or few) 
			// 1. Admin link will be added but pending
			if($parentType==Organization::COLLECTION)
				Link::addMember($parentId, $parentType, $personId, Person::COLLECTION, $userId, true, "", true);
			else if ($parentType == Project::COLLECTION){
				Link::connect($parentId, $parentType, $personId, Person::COLLECTION, Yii::app() -> session["userId"], "contributors", true,true);
				Link::connect($personId, Person::COLLECTION, $parentId, $parentType, Yii::app() -> session["userId"], "projects", true, true);
			}
			Notification::actionOnPerson ( ActStr::VERB_AUTHORIZE, ActStr::ICON_SHARE, $pendingAdmin , array("type"=>$parentType,"id"=> $parentId,"name"=>$parentData["name"])) ;
			// 2. Notification and email are sent to the admin(s)
			$listofAdminsEmail = array();
			foreach ($usersAdmin as $adminId) {
				$currentAdmin = Person::getSimpleUserById($adminId);
				array_push($listofAdminsEmail, $currentAdmin["email"]);
			}
			$typeOfDemand="admin";
			Mail::someoneDemandToBecome($parentData, $parentType, $pendingAdmin, $listofAdminsEmail,$typeOfDemand);
			//TODO - Notification
			$res = array("result" => true, "msg" => Yii::t("common","Your request has been sent to other admins."), "parent" => $parentData);
			// After : the 1rst existing Admin to take the decision will remove the "pending" to make a real admin
		}

		return $res;
	}
	
	/*
	* This function is similar to disconnect but he just remove a value as pending, isAdminPending, isPending
	* @valueUpdate is string to know which line disconnect
	* Using for acceptAsAdmin, AcceptAsMember, etc...
	*/
	public static function updateLink($parentType,$parentId,$userId,$userType,$connectType,$connectTypeOf,$valueUpdate){
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