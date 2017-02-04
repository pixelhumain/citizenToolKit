<?php 

class Notification{
	
	//limit  the size of the notification map
	//when an organization/project has a huge number of members
	const PEOPLE_NOTIFY_LIMIT = 50;

	/*
	a person can be invited to a project
	an organization can be invited to a project

	notify invited member (person or Organization) if Org notify all admins
	notify the project admins
	*/
	/*public static function invited2Project( $memberType, $memberId, $projectId,$projectName ) 
	{
	    $asParam = array(
	    	"type" => ActStr::TEST, 
            "verb" => ActStr::VERB_INVITE,
            "author"=>array(
            	"type" => Person::COLLECTION,
            	"id"   => ( isset(Yii::app()->session["userId"]) ) ? Yii::app()->session["userId"] : null
            ),
            "object"=>array(
	            "type" => $memberType,
	            "id"   => (string)$memberId
            ),
            "target"=>array(
	            "type" => Project::COLLECTION,
	            "id"   => $projectId 
            )
        );
	    $stream = ActStr::buildEntry($asParam);

	    //build list of people to notify
	    //by default it's a person
	    $objectName = "you";
	    $people = array();
	    if( $memberType == Organization::COLLECTION ){
	    	$admins = Organization::getMembersByOrganizationId( $memberId, Person::COLLECTION , "isAdmin" );
		    foreach ($admins as $key => $value) 
		    {
		    	if( $key != Yii::app()->session['userId'] && !in_array($key, $people) && count($people) < self::PEOPLE_NOTIFY_LIMIT )
		    		array_push( $people, $key);
		    }	
	    }else
	    	array_push( $people, $memberId);

	    //notify all Projects admins
	    $projectAdmins = Project::getContributorsByProjectId( $projectId,"all", "isAdmin" );
	    foreach ($projectAdmins as $key => $value) 
	    {
	    	if( $key != Yii::app()->session['userId'] && count($people) < self::PEOPLE_NOTIFY_LIMIT )
	    		array_push( $people, $key);
	    }
	    $notif = array( "persons" => $people,
	                    "label"   => Yii::app()->session['user']["name"]." invited ".$objectName." to ".$projectName , 
	                    "icon"    => ActStr::ICON_SHARE ,
	                    "url"     => Yii::app()->createUrl('/'.Yii::app()->controller->module->id.'/project/dashboard/id/'.$projectId) 
	                );
	    $stream["notify"] = ActivityStream::addNotification( $notif );
	    ActivityStream::addEntry($stream);

	    //TODO mail::invited
	}*/
	/* TODO BOUBOULE
		=> récupérer les admins || / && les membres || / && les followers d’un élement suivant le niveau d’impact de la notification et les préférences des utilisateurs
		=>$impact == admin || member || community
		=> retourne id + mail + userName	
	*/
	public static $notificationTree = array(
		// Action realized by a user
		ActStr::VERB_FOLLOW => array(
			"repeat" => true,
			//"context" => array("user","members"),
			"mail" => array(
				"type"=>"daily"
			),
			//WHAT == you || elementName
			"label" => "{who} is following {where}",
			"labelRepeat"=>"{who} are following {where}",
			"labelArray" => array("who","where"),
			"icon" => "fa-link",
			"url" => "{ctrlr}/directory/id/{id}?tpl=directory2"
		),
		ActStr::VERB_ASK => array(
			"repeat" => true,
			"type" => array(
				"asMember" => array(
					"to"=> "members",
					"label"=>"{who} wants to join {where}",
					"labelRepeat"=>"{who} want to join {where}"
				),
				"asAdmin" => array(
					"to" => "admin",
					"label"=>"{who} wants to administrate {where}",
					"labelRepeat"=>"{who} want to administrate {where}"
				)
			),
			"labelArray" => array("who","where"),
			//"context" => "members",
			"mail" => array(
				"type"=>"instantly"
			),
			"icon" => "fa-cog",
			"url" => "{ctrlr}/directory/id/{id}?tpl=directory2"
		),
		//// USED ONLY FOR EVENT
		// FOR ORGANIZATRION AND PROJECT IF ONLY MEMBER
		/*"ActStr::VERB_JOIN" => array(
			"repeat" => true,
			"context" => "members",
			"mail" => array(
				"type"=>"daily"
			),
			"label"=>"{who} participates to {where}",
			"labelRepeat"=>"{who} participate to {where}",
			"labelArray" => array("who","where"),
			"icon" => "fa-link",
			"url" => "{whatController}/detail/id/{whatId}"
		),*/
		ActStr::VERB_COMMENT => array(
			"repeat" => true,
			"type" => array(
				News::COLLECTION => array(
					"label" => "{who} commented on your news",
					"labelRepeat" => "{who} added comments on your news",
					"targetIsAuthor"=> array(
						"label"=> "{who} commented the news posted by {where}",
						"labelRepeat"=> "{who} added comments on the news posted by {where}"
					),
					"url" => "news/detail/id/{id}"
				),
				Survey::COLLECTION => array(
					"label" => "{who} commented on proposal {what} in {where}",
					"labelRepeat" => "{who} added comments on proposal {what} in {where}",
					"url" => "survey/entry/id/{id}"
				),
				ActionRoom::COLLECTION_ACTIONS => array(
					"label" => "{who} commented on action {what} in {where}",
					"labelRepeat" => "{who} added comments on action {what} in {where}",
					"url" => "rooms/action/id/{id}"
				),
				ActionRoom::COLLECTION => array(
					"label" => "{who} commented on discussion {what} in {where}",
					"labelRepeat" => "{who} added comments on discussion {what} in {where}",
					"url" => "comment/index/type/actionRooms/id/{id}"
				),
				/*"needs" => array(
					"label" => "{who} added a comment on your need",
					"labelRepeat" => "{who} added comment on your need",
					"need/datail/id/{id}"
				),*/
				Comment::COLLECTION => array(
					"label" => "{who} answered to your comment posted on {where}",
					"labelRepeat" => "{who} added comments on your comments posted on {where}",
					"url" => "targetTypeUrl",
					"notifyUser" => true,
					"repeat"=>true
				)
			),
			"labelArray" => array("who","where"),
			"context" => array("user","members"),
			"mail" => array(
				"type"=>"instantly",
				"to" => "author" //If orga or project to members
			),
			"icon" => "fa-comment"
			//"url" => "{whatController}/detail/id/{whatId}"
		),
		ActStr::VERB_LIKE => array(
			"repeat" => true,
			"type" => array(
				News::COLLECTION => array(
					"targetIsAuthor" => array(
						"label"=>"{who} likes a news from {where}",
						"labelRepeat"=>"{who} like a news from {where}"
					) ,
					"label"=>"{who} likes your news",
					"labelRepeat"=>"{who} like your news",
					"url" => "news/detail/id/{id}"
				),
				Comment::COLLECTION => array(
					"label"=>"{who} likes your comment on {where}",
					"labelRepeat"=>"{who} like your comment on {where}",
					"url" => "targetTypeUrl"
				)
			),
			"labelArray" => array("who", "where"),
			//"context" => array("user","members"),
			"mail" => array(
				"type"=>"instantly",
				"to" => "author" //If orga or project to members
			),
			"icon" => "fa-thumbs-up"
		),
		ActStr::VERB_UNLIKE => array(
			"repeat" => true,
			"type" => array(
				News::COLLECTION => array(
					"targetIsAuthor" => array(
						"label"=>"{who} disapproves a news from {where}",
						"labelRepeat"=>"{who} disapprove a news from {where}"
					),
					"label"=>"{who} disapproves your post",
					"labelRepeat"=>"{who} disapproves your post",
					"url" => "news/detail/id/{id}"
				),
				Comment::COLLECTION => array(
					"label"=>"{who} disapproves your comment on {where}",
					"labelRepeat"=>"{who} disapproves your comment on {where}",
					"url" => "targetTypeUrl"
				)
			),
			"labelArray" => array("who", "where"),
			"context" => array("me","members"),
			"mail" => array(
				"type"=>"instantly",
				"to" => "author" //If orga or project to members
			),
			"icon" => "fa-thumbs-down"
		),
		ActStr::VERB_ADD => array(
			"type" => array(
				/*"need"=> array(
					"url" => "{ctrlr}/detail/id/{id}"
				),*/
				Project::COLLECTION => array(
					"url" => "{ctrlr}/detail/id/{id}",
					"label" => "{who} added a new project on {where}"
				),
				Event::COLLECTION=> array(
					"url" => "{ctrlr}/detail/id/{id}",
					"label" => "{who} added a new event on {where}"
				),
				News::COLLECTION => array(
					"url" => "news/index/type/{collection}/id/{id}",
					"label" => "{who} added a new post in {where}"
				),
				ActionRoom::COLLECTION_ACTIONS=> array(
					"url"=>"rooms/actions/id/{id}",
					"label"=> "{who} added a new actions list on {where}"
				),
				ActionRoom::TYPE_DISCUSS => array(
					"url"=>"comment/index/type/actionRooms/id/{id}",
					"label" => "{who} added a new discussion room on {where}"
				),
				ActionRoom::TYPE_VOTE => array(
					"url"=>"survey/entries/id/{id}",
					"label" => "{who} added a new voting room on {where}"
				),
				Survey::COLLECTION => array(
					"url" => "survey/entry/id/{id}",
					"label"=> "{who} added a new proposal {what} in {where}"
				),
				ActionRoom::TYPE_ACTION => array(
					"url" => "rooms/action/id/{id}",
					"label" => "{who} added a new action {what} in {where}"
				),
				"profilImage" => array(
					"url" => "{ctrlr}/detail/id/{id}",
					"label" => "{who} added a new profil image on {where}"
				),
				"albumImage" => array(
					"url" => "gallery/index/type/{collection}/id/{id}",
					"label" => "{who} added new images to the album of {where}",
					"repeat" => true,
					"noUpdate" => true
				)
			),
			/*"context" => array(
				"members" => array(
					"mail" => array(
						"type"=>"instantly",
						"to" => "members"
					)
				),
				"city" => true
			),*/
			//"label"=>"{who} added {type} {what} in {where}",
			"labelArray" => array("who","where"),
			"icon" => "fa-plus"
		),/*
		"ActStr::VERB_UPDATE" => array(
			"repeat" => true,
			"context" => "community",
			"mail" => array(
				"type"=>"daily",
				"to" => "members"
			),
			"label"=>"{who} modified {what} of {where}",
			"labelRepeat"=>"{who} confirmed the invitation to join {where}",
			"labelArray" => array("who","what","where"),
			"icon" => "fa-cog",
			"url" => "{whatController}/detail/id/{whatId}"
		),
		"ActStr::VERB_CONFIRM" => array(
			"repeat" => true,
			"context" => "community",
			"mail" => array(
				"type"=>"instantly",
				"to" => "invitor"
			),
			"label" => "{who} confirmed the invitation to join {where}",
			"labelRepeat" => "{who} confirmed the invitation to join {where}",
			"labelArray" => array("who","what"),
			"icon" => "fa-cog",
			"url" => "element/directory/type/{whatType}/id/{whatId}"
		),
		//FROM USER LINK TO AN ELEMENT ACTING ON IT
		"ActStr::VERB_INVITE" => array(
			"repeat" => true,
			"type" => array(
				"asMember" => array(
					"to"=> "members",
					"label"=>"{author} invited {who} to join {where}",
					"labelRepeat"=>"{author} invited {who} to join {where}"
				),
				"asAdmin" => array(
					"to"=> "members",
					"label"=>"{author} invited {who} to administrate {where}",
					"labelRepeat"=>"{author} invited {who} to administrate {where}"
				)
			),
			"labelArray" => array("author","who","where"),
			"context" => array("community","user"),
			"mail" => array(
				"type"=>"instantly",
				"to" => "user"
			),
			"icon" => "fa-send",
			"url" => "element/directory/type/{whatType}/id/{whatId}"
		),*/
		// AJouter la confirmation vers l'utilisateur
		//Creer le mail pour l'utilisateur accepté !!
		ActStr::VERB_ACCEPT => array(
			"repeat" => true,
			"context" => array("user","members"),
			"notifyUser" => true,
			"mail" => array(
				"type"=>"instantly",
				"to" => "invitor"
			),
			"type" => array(
				"asMember" => array(
					"to"=> "members",
					"label"=>"{author} confirmed {who} to join {where}",
					"labelRepeat"=>"{author} confirmed {who} to join {where}"
				),
				"asAdmin" => array(
					"to"=> "members",
					"label"=>"{author} confirmed {who} to administrate {where}",
					"labelRepeat"=>"{author} confirmed {who} to administrate {where}"
				),
				"user" => array(
					"asMember" => array(
						"label"=>"{author} confirmed your request to join {where}"
					),
					"asAdmin" => array(
						"label"=>"{author} confirmed your request to administrate {where}"
					)
				)
			),
			"labelArray" => array("author","who","where"),
			"icon" => "fa-cog",
			"url" => "{ctrlr}}/directory/id/{id}"
		)/*,
		"SIGNIN" => array(
			"repeat" => true,
			"context" => "user",
			"mail" => array(
				"type"=>"instantly",
				"to" => "invitor"
			)
		)*/
	);

	public static function communityToNotify($id, $type, $impact="all", $authorId=null, $alreadyAuhtorNotify=null){
		//inform the entities members of the new member
		//build list of people to notify
        $people = array();
	    $members = array();
	    if( $type == Project::COLLECTION ) {
	    	$members = Project::getContributorsByProjectId( $id ,"all", null ) ;
			$typeOfConnect="contributor";
	    }
	    else if( $type == Organization::COLLECTION) {
	    	$members = Organization::getMembersByOrganizationId( $id ,"all", null ) ;
	    	$typeOfConnect="member";
	    }
	    else if( $type == Event::COLLECTION ) {
	    	/**
		    * Notify only the admin of the event
	    	*	- if new attendee or new admin
	    	* Notify all
	    	*	- if a post in event wall
	    	*/
	    	if($verb == ActStr::VERB_POST)
	    		$members = Event::getAttendeesByEventId( $id , "all", null ) ;
	    	else
	    		$members = Event::getAttendeesByEventId( $id , "admin", "isAdmin" ) ;
	    	$typeOfConnect="attendee";
	    }
		else if($type == Person::COLLECTION)
			$people[$id] = array("isUnread" => true, "isUnsee" => true);
		else if($type == News::COLLECTION){
			if(Yii::app()->session["userId"] != $alreadyAuhtorNotify){
				$authorNews=News::getAuthor($id);
				if($authorId != $authorNews["author"])
					$people[$authorNews["author"]] = array("isUnread" => true, "isUnsee" => true);
			} 
		} 
		else if( in_array($type, array( Survey::COLLECTION, ActionRoom::COLLECTION, ActionRoom::COLLECTION_ACTIONS) ) )
		{
			$entryId = $id;
			if( $type == Survey::COLLECTION ){
				$target["entry"] = Survey::getById( $id );
				$entryId = (string)$target["entry"]["survey"];
			} else if( $type == ActionRoom::COLLECTION_ACTIONS ){
				$target["entry"] = ActionRoom::getActionById( $id );
				$entryId = $target["entry"]["room"];
			}
			$room = ActionRoom::getById( $entryId );
			if( @$room["parentType"] ){
				if( $room["parentType"] == Project::COLLECTION ) 
			    	$members = Project::getContributorsByProjectId( $room["parentId"] ,"all", null ) ;
			    else if( $room["parentType"] == Organization::COLLECTION) 
			    	$members = Organization::getMembersByOrganizationId( $room["parentId"] ,"all", null ) ;
			    else if( $room["parentType"] == Event::COLLECTION ) {
			    	//TODO notify only the admin of the event
			    	if($verb == ActStr::VERB_POST)
		    			$members = Event::getAttendeesByEventId( $room["parentId"] , "all", null ) ;
					else
		    			$members = Event::getAttendeesByEventId( $room["parentId"] , "admin", "isAdmin" ) ;
		    	}
			}
		}
	    foreach ($members as $key => $value) 
	    {
	    	if( $key != Yii::app()->session['userId'] && $key != $authorId && !in_array($key, $people) && count($people) < self::PEOPLE_NOTIFY_LIMIT && $key != $alreadyAuhtorNotify){
	    		$people[$key] = array("isUnread" => true, "isUnsee" => true); 
	    	}
	    }
	    return $people;
	}

	public static function getLabelNotification($label, $labelArray, $count, $target, $notification, $object, $labelUpNotifyTarget){
		$specifyLabel = array();
		if($labelUpNotifyTarget=="object"){
			$memberName=$object["name"];
			$specifyLabel["{author}"] = Yii::app()->session['user']['name'];
		}else {
			$memberName=Yii::app()->session['user']['name'];
		}

		if($count==1){
			$specifyLabel["{who}"] = $memberName;
		}
		else if($count==2){
			foreach($notification[$labelUpNotifyTarget] as $data){
				$lastAuthorName=$data["name"];
				break; 
			}
			$specifyLabel["{who}"] = $memberName." ".Yii::t("common","and")." ".$lastAuthorName;
		}
		else {
			foreach($notification[$labelUpNotifyTarget] as $data){
				$lastAuthorName=$data["name"];
				break;
			}
			$nbOthers = $count - 2;
			if($nbOthers == 1) $labelUser = "person"; else $labelUser = "persons";
			$specifyLabel["{who}"] = $memberName.", ".$lastAuthorName." ".Yii::t("common","and")." ".$nbOthers." ".Yii::t("common", $labelUser);
		}
		if(in_array("where",$labelArray)){
			if(@$target["name"])
				$specifyLabel["{where}"] = $target["name"];
			else{
				$resArray=self::getTargetInformation($target["id"],$target["type"]);
				$specifyLabel["{where}"] = $resArray["{where}"];
				if(@$resArray["{what}"])
					$specifyLabel["{what}"]=$resArray["{what}"];
			}
		}
		if(in_array("type", $labelArray))
			$specifyLabel["{type}"] = $labelArray["typeValue"];
		if(in_array("what",$labelArray))
			$specifyLabel["{what}"] = @$object["name"];
		return Yii::t("notification",$label, $specifyLabel);	
	}

	public static function getUrlNotification($notification,$target,$levelInformation){
		if(@$notification["url"])
			$url=$notification["url"];
		else 
			$url=$notification["type"][$levelInformation]["url"];
		if($url=="targetTypeUrl")
			$url=$notification["type"][$target["type"]]["url"];
		$url = str_replace("{ctrlr}", Element::getControlerByCollection($target["type"]), $url);
		$url = str_replace("{id}", $target["id"], $url);
		return $url;
	}
	/* TODO BOUBOULE
		Regarde si une notif portant sur le même type ajout comment like sur la même target existe alors:
		=> Si n’existe pas
			Return false
		=> sinon
			Check by ids si read is false
				Remove all ids of this notif where read is false
				Add id in resultArray notif $repeatAction 1
			Else is true
				Up notif and label $repeat +1 
		Return array of ids  
	*/
	public static function checkIfAlreadyNotifForAnotherLink($target, $author, $verb, $notificationPart, $object=null, $levelInformation=null)	{
		$where=array("verb"=>$verb, "target.id"=>$target["id"], "target.type"=>$target["type"]);
		$labelUpNotifyTarget="author";
		if(in_array("author",$notificationPart["labelArray"])){
			$where["author.".Yii::app()->session["userId"]] = array('$exists' => true);
			$labelUpNotifyTarget="object";
			$object=$author;
		} /*else if($object){
			$where["object.id"] = $object["id"];
			$where["object.type"]=$object["type"];
		}*/
		if($levelInformation)
			$where["notify.objectType"] = $levelInformation;
		$notification = PHDB::findOne(ActivityStream::COLLECTION, $where);
		if(!empty($notification)){
			if((@$notification[$labelUpNotifyTarget] && @$notification[$labelUpNotifyTarget][$author["id"]]) || (@$notificationPart["type"] && @$notificationPart["type"][$levelInformation] && @$notificationPart["type"][$levelInformation]["noUpdate"] ))
				return true;
			else{
				$countRepeat=1;
				foreach($notification[$labelUpNotifyTarget] as $i){$countRepeat++;}
				if($levelInformation){
					if(@$target["targetIsAuthor"])
						$labelRepeat = $notificationPart["type"][$levelInformation]["targetIsAuthor"]["label"];
					else
						$labelRepeat = $notificationPart["type"][$levelInformation]["labelRepeat"];
				}
				else{
					$labelRepeat = $notificationPart["labelRepeat"];
				}
				// Get new Label
				$newLabel=self::getLabelNotification($labelRepeat, $notificationPart["labelArray"], $countRepeat, $target, $notification, $object, $labelUpNotifyTarget);
				// Add new author to notification
				if($labelUpNotifyTarget == "object")
					$notification["object"][$author["id"]]=array("name" => $author["name"]);
				else
					$notification["author"][Yii::app()->session['userId']]=array("name" => Yii::app()->session['user']['name']);
				// Up isUnread and unSee to community to notify
				$communityToNotify=array();
				foreach ($notification["notify"]["id"] as $key => $data){
					if(!@$data["isUnread"])
						$data["isUnread"]=true;
					if(!@$data["isUnsee"])
						$data["isUnsee"]=true;
					$communityToNotify[$key]=$data;
				}
				PHDB::update(ActivityStream::COLLECTION,
					array("_id" => $notification["_id"]),
					array('$set' => array(
						$labelUpNotifyTarget=>$notification[$labelUpNotifyTarget],
						"notify.id" => $communityToNotify,
						"notify.displayName"=> $newLabel,
						"updated" => new MongoDate(time())
						)
					)
				);
				return true;
			}
		}
		else
			return false;
	}
	/* TODO BOUBOULE
	*/
	public static function createNotification($verb, $author, $target, $community, $label,$notificationPart, $labelUpNotifyTarget, $typeAction, $object, $notifyObject=null){
		$asParam = array(
	    	"type" => "notifications", 
            "verb" => $verb,
            "author"=> $author,
 			"target"=> array("id" => $target["id"],"type" => $target["type"])
        );
        if($object)
        	$asParam["object"]=$object;
 	    $stream = ActStr::buildEntry($asParam);
		$notif = array( 
	    	"persons" => $community,
            "label"   => self::getLabelNotification($label, $notificationPart["labelArray"], 1, $target, null, $object, $labelUpNotifyTarget),
            "icon"    => $notificationPart["icon"],
            "url"     => self::getUrlNotification($notificationPart,$target, $typeAction)
        );
        if($notifyObject)
        	$notif["objectType"]=$notifyObject;
	    $stream["notify"] = ActivityStream::addNotification( $notif );
    	ActivityStream::addEntry($stream);
	}
	/* TODO BOUBOULE
		=> $label : “a ajouté un nouvel $type à $type”
		=> $community:
			$impact => community
			$notify => $ids => {id + isUnread == true}
	*/
	public static function constructNotification($verb, $author, $target, $object = null, $typeAction = null, $context = null){
		$notificationPart = self::$notificationTree[$verb];
		if(in_array("type",$notificationPart["labelArray"]))
			$notificationPart["labelArray"]["typeValue"]=$notificationPart["type"][$typeAction]["type"];
		// Object could be the object in following method if action is by an other acting on an other person (ex: author add so as member {"member"=> $author})
		if(@$author["_id"])
			$authorId=(string)$author["_id"];
		else
			$authorId=$author["id"];
		$author=array("id"=>$authorId,"name"=>$author["name"]);
		$labelUpNotifyTarget = "author";
		// Create notification specially for user added to the next notify for community of target
		if(@$notificationPart["notifyUser"] || (@$notificationPart["type"] && @$notificationPart["type"][$typeAction] && @$notificationPart["type"][$typeAction]["notifyUser"])){
			$update=false;
			// If answered on comment is the same than on the news or other don't notify twice the author of parent and comment
			if($verb==Actstr::VERB_COMMENT){
				$comment=Comment::getById($object["id"]);
				$userNotify=$comment["author"]["id"];
				$alreadyAuhtorNotify=$userNotify;
			}else
				$userNotify=$author["id"];
			$community=self::communityToNotify($userNotify,Person::COLLECTION,$context);
			if(@$notificationPart["type"][$typeAction] && @$notificationPart["type"][$typeAction]["repeat"])
				$update=self::checkIfAlreadyNotifForAnotherLink($target, $author, $verb, $notificationPart, $object, $typeAction);
			if($update==false){
		 	    //if($typeAction)
		 	    $notifyObject=null;
		 	    if(@$notificationPart["type"]["user"]){
					$label = $notificationPart["type"]["user"][$typeAction]["label"];
					$labelUpNotifyTarget="object";
				}else{
					if(@$target["targetIsAuthor"])
						$label = $notificationPart["type"][$typeAction]["targetIsAuthor"]["label"];
					else
						$label = $notificationPart["type"][$typeAction]["label"];
					$labelUpNotifyTarget="author";
					$notifyObject=$typeAction;
				}
				$authorUser=array(Yii::app()->session["userId"]=> array("name"=> Yii::app()->session["user"]["name"]));
				self::createNotification($verb,$authorUser,$target,$community,$label,$notificationPart,$labelUpNotifyTarget,$typeAction,$object, $notifyObject);
		    }
	        //To DO -- Create Mail of confirmation for user Or invitation for user !!!!!!
		}
		// COnstruct notification for target
		$community = self::communityToNotify($target["id"], $target["type"], $context, @$alreadyAuhtorNotify);
		$update = false;
		if((@$notificationPart["repeat"] && $notificationPart["repeat"]) || (@$notificationPart["type"] && @$notificationPart["type"][$typeAction] && @$notificationPart["type"][$typeAction]["repeat"])){	
			$update=self::checkIfAlreadyNotifForAnotherLink($target, $author, $verb, $notificationPart, $object, $typeAction);
		}
		if($update==false && !empty($community)){
	        // Case 
	        if(in_array("author",$notificationPart["labelArray"])){
	        	$object = array($author["id"]=>array("name"=>$author["name"]));
	        	$author = array(Yii::app()->session["userId"]=> array("name"=> Yii::app()->session["user"]["name"]));
	        	$labelUpNotifyTarget="object";
	        	//$object = $author;
	        }
	        else if($object){
	        	$object=array("id" => $object["id"], "type" => $object["type"]);
				//$target["name"]=self::getTargetName($target["id"],$target["type"]);
	        }
	        $notifyObject=null;
	 	    if($typeAction){
	 	    	if(@$target["targetIsAuthor"])
					$label = $notificationPart["type"][$typeAction]["targetIsAuthor"]["label"];
				else
					$label = $notificationPart["type"][$typeAction]["label"];
	 	    	$notifyObject=$typeAction;
	 	    }
			else
				$label = $notificationPart["label"];
			 self::createNotification($verb, $author, $target, $community, $label,$notificationPart, $labelUpNotifyTarget, $typeAction, $object, $notifyObject);
	    }
	}

	public static function getTargetInformation($id, $type) {	
	 	$target=array();
	 	if( in_array($type, array( Survey::COLLECTION, ActionRoom::COLLECTION, ActionRoom::COLLECTION_ACTIONS) ) )
		{
			$entryId = $id;
			if( $type == Survey::COLLECTION ){
				$target["entry"] = Survey::getById( $id );
				$entryId = (string)$target["entry"]["survey"];
			} else if( $type == ActionRoom::COLLECTION_ACTIONS ){
				$target["entry"] = ActionRoom::getActionById( $id );
				$entryId = $target["entry"]["room"];
			}
			$room = ActionRoom::getById( $entryId );
			$target["room"] = $room;
			if( @$room["parentType"] ){
				if( $room["parentType"] == City::COLLECTION ) 
			    	$target["parent"] = City::getByUnikey( $room["parentId"]);
				else 
					$target["parent"] = Element::getElementSimpleById($room["parentId"], $room["parentType"]); 
			}
		}else if($type=="news"){
			$news=News::getById($id);
			$parent=Element::getElementSimpleById($news["target"]["id"], $news["target"]["type"]); 
		} else if($type==Organization::COLLECTION || $type==Project::COLLECTION || $type==Event::COLLECTION)
			$parent=Element::getElementSimpleById($id, $type);
		$res=array();
		$res["{what}"] = Yii::t("common", "a ".Element::getControlerByCollection($type));
		if(@$target["name"])
			$res["{where}"]=$target["name"];
		else if(@$parent["name"])
			$res["{where}"]=$parent["name"];
		else if (@$target["entry"]){
			$res["{what}"]=$target["entry"]["name"];
			if(@$target["parent"])
				$res["{where}"] = $target["parent"]["name"];
		}
			
		return $res;
	}

	private static function array_column($array,$column_name)
    {
        return array_map(function($element) use($column_name){return $element[$column_name];}, $array);

    }
    // TODO BOUBOULE => Mention in news // comment (à développer)
    // A RENOMER mentionNotification
	public static function actionOnNews ( $verb, $icon, $author, $target, $mentions) 
	{
		$notification=array();
		$url = Yii::app()->createUrl('/'.Yii::app()->controller->module->id.'/'.'news/index/type/'.$target["type"].'/id/'.$target["id"]);
		foreach ($mentions as $data){
			if($data["type"]==Person::COLLECTION){
				if(!empty($notification) && array_search($data["id"], self::array_column($notification, 'persons'))){
			    	foreach($notications as $i => $list){
				    	foreach($list["persons"] as $id){
					    	if($id==$data["id"]){
						    	if($list["type"]==Organization::COLLECTION){
							    	$nameOrga = $list["name"];
							    	$pushNotif=array(
										"type"=> Organization::COLLECTION,
										"nameOrganization"=>@$nameOrga,
										"nbMention"=>2,
										"persons"=>array($data["id"]),
										"label"=> $author["name"]." vous a mentionné avec ".$data["name"]." dans un post",
										"url"=> $url,
										"icon" => $icon
									);
									unset($notication[$i]);
									array_push($notification, $pushNotif);
						    	}
					    	}
				    	}
			    	}
		    	}else{
	    			$people=array($data["id"]);
	    			$pushNotif=array(
							    "type"=> Person::COLLECTION,
							    "persons"=>$people,
							    "label"=> $author["name"]." vous a mentionné dans un post",
							    "url"=> $url,
							    "icon" => $icon
								);
					array_push($notification, $pushNotif);
			    }
				
				
			}
			if($data["type"]==Organization::COLLECTION){
				$admins = Organization::getMembersByOrganizationId( $data["id"], Person::COLLECTION , "isAdmin" );
				$people=array();
			    foreach ($admins as $key => $value) 
			    {
			    	if( $key != Yii::app()->session['userId'] && !in_array($key, $people) && count($people) < self::PEOPLE_NOTIFY_LIMIT ){
				    	if(!empty($notification)){
					    	foreach($notification as $i => $list){
						    	foreach($list["persons"] as $id){
							    	if($id==$key){
								    	if($list["type"]==Organization::COLLECTION && @$list["nbMention"]!=2){
									    	$nameOrga = @$list["nameOrganization"];
									    	$pushNotif=array(
												"type"=> Organization::COLLECTION,
												"nameOrganization"=>$nameOrga,
												"nbMention"=>2,
												"persons"=>array($key),
												"label"=> $author["name"]." a mentionné ".$data["name"]." et ".$nameOrga." dans un post",
												"icon" => $icon,
												"url"=> $url
											);
											array_push($notification, $pushNotif);
								    	}
										if($list["type"]==Person::COLLECTION){
									    	$nameOrga = @$list["name"];
									    	$pushNotif=array(
												"type"=> Person::COLLECTION,
												"nameOrganization"=>$data["name"],
												"nbMention"=>2,
												"persons"=> array($key),
												"label"=> $author["name"]." vous a mentionné ainsi que ".$data["name"]." dans un post",
												"icon" => $icon,
												"url"=> $url
											);
											unset($notification[$i]);
											array_push($notification, $pushNotif);
								    	}
							    	}
						    	}
					    	}
					    }else{
			    			array_push($people, $key);
		    			}
			    	}	
			    }
			    $pushNotif=array(
							    "type"=> Organization::COLLECTION,
							    "nameOrganization"=>$data["name"],
							    "persons"=>$people,
							    "label"=> $author["name"]." a mentioné ".$data["name"]." dans un post",
							    "url"=> $url,
							    "icon" => $icon 
				);
				array_push($notification, $pushNotif);
			}
		}
		foreach($notification as $notif){
			$asParam = array(
		    	"type" => ActStr::TEST, 
	            "verb" => $verb,
	            "author"=>array(
	            	"type" => Person::COLLECTION,
	            	"id"   => $author["id"]
	            ),
	            "object"=>array(
	            	"type" => Person::COLLECTION,
	            	"id"   => $author["id"]
	            ),
	        );
		    $stream = ActStr::buildEntry($asParam);
		    $stream["notify"] = ActivityStream::addNotification( $notif );
		    ActivityStream::addEntry($stream);
		}
		// Verbe ActStr::VERB_POST || ActStr::VERB_MENTION
		
	}
	/*
	when someone joins or leaves or disables a project / organization / event
	notify all contributors

	the action/verb can be done by the person or by an admin (remove from project)
	$verb can be join, leave
	$icon : anicon to show
	$member : a map of the object member , 
		should contain : id ,type, name of the member (person or Orga)
	$target : context of the action (project, orga,event)
	$invitation : adapt notification's text if it's an invitation from someone
	*/
	public static function actionOnPerson ( $verb, $icon, $member, $target, $invitation=false) 
	{
		$targetId = ( isset( $target["id"] ) ) ? $target["id"] : (string)$target["_id"] ;
		if( $member )
			$memberId = ( isset( $member["id"] ) ) ? $member["id"] : (string)$member["_id"] ;
	    $asParam = array(
	    	"type" => ActStr::TEST, 
            "verb" => $verb,
            "author"=>array(
            	"type" => Person::COLLECTION,
            	"id"   => ( isset(Yii::app()->session["userId"]) ) ? Yii::app()->session["userId"] : null
            ),
            "object"=>array(
            	"type" => Person::COLLECTION,
            	"id"   => ( isset(Yii::app()->session["userId"]) ) ? Yii::app()->session["userId"] : null
            ),
            "target"=>array(
	            "type" => $target["type"],
	            "id"   => $targetId
            )
        );
        //build list of people to notify
        $people = array();
        //when admin makes the change
        //notify the people concerned by the entity
        if( isset($memberId) && $memberId != Yii::app()->session["userId"] ){
        	if(@$member['type'] && $member['type'] == Organization::COLLECTION )
        	{
        		$asParam["object"] = array(
		            "type" => Organization::COLLECTION,
		            "id"   => $memberId
	            );

	            //inform the organisations admins
		    	$admins = Organization::getMembersByOrganizationId( $memberId, Person::COLLECTION , "isAdmin" );
			    foreach ($admins as $key => $value) 
			    {
			    	if( $key != Yii::app()->session['userId'] && !in_array($key, $people) && count($people) < self::PEOPLE_NOTIFY_LIMIT )
			    		array_push( $people, $key);
			    }
        	} 
        	else 
        	{ 
	        	$asParam["object"] = array(
		            "type" => Person::COLLECTION,
		            "id"   => $memberId
	            );
	        	array_push( $people, $memberId );
	        }
        }

	    $stream = ActStr::buildEntry($asParam);
	    //inform the entities members of the new member
	    $members = array();
	    if( $target["type"] == Project::COLLECTION ) {
	    	$members = Project::getContributorsByProjectId( $targetId ,"all", null ) ;
			$typeOfConnect="contributor";
	    }
	    else if( $target["type"] == Organization::COLLECTION) {
	    	$members = Organization::getMembersByOrganizationId( $targetId ,"all", null ) ;
	    	$typeOfConnect="member";
	    }
	    else if( $target["type"] == Event::COLLECTION ) {
	    	/**
		    * Notify only the admin of the event
	    	*	- if new attendee or new admin
	    	* Notify all
	    	*	- if a post in event wall
	    	*/
	    	if($verb == ActStr::VERB_POST)
	    		$members = Event::getAttendeesByEventId( $targetId , "all", null ) ;
	    	else
	    		$members = Event::getAttendeesByEventId( $targetId , "admin", "isAdmin" ) ;
	    	$typeOfConnect="attendee";
	    }
		else if($target["type"] == Person::COLLECTION)
			$people = array($targetId);
		else if($target["type"] == News::COLLECTION){
			$author=News::getAuthor($target["id"]);
			$people = array($author["author"]);
		} 
		else if( in_array($target["type"], array( Survey::COLLECTION, ActionRoom::COLLECTION, ActionRoom::COLLECTION_ACTIONS) ) )
		{
			$entryId = $target["id"];
			if( $target["type"] == Survey::COLLECTION ){
				$target["entry"] = Survey::getById( $target["id"] );
				//var_dump($target); echo (string)$target["entry"]["_id"]; return;
				$entryId = (string)$target["entry"]["survey"];
			} else if( $target["type"] == ActionRoom::COLLECTION_ACTIONS ){
				$target["entry"] = ActionRoom::getActionById( $target["id"] );
				//echo "tageettttt ". var_dump($target["entry"]); //return;
				$entryId = $target["entry"]["room"];
				//echo "entryId : ".$entryId;return;
			}

			$room = ActionRoom::getById( $entryId );
			$target["room"] = $room;
			//echo "target : ".$entryId; var_dump($target); return;
			if( @$room["parentType"] ){
				if( $room["parentType"] == Project::COLLECTION ) {
					$target["parent"] = Project::getById( $room["parentId"]);
			    	$members = Project::getContributorsByProjectId( $room["parentId"] ,"all", null ) ;
					$typeOfConnect="contributor";
			    }
			    else if( $room["parentType"] == Organization::COLLECTION) {
			    	$target["parent"] = Organization::getById( $room["parentId"]);
			    	$members = Organization::getMembersByOrganizationId( $room["parentId"] ,"all", null ) ;
			    	$typeOfConnect="member";
			    }
			    else if( $room["parentType"] == Event::COLLECTION ) {
			    	//TODO notify only the admin of the event
			    	$target["parent"] = Event::getById( $room["parentId"]);
			    	if($verb == ActStr::VERB_POST)
		    			$members = Event::getAttendeesByEventId( $room["parentId"] , "all", null ) ;
					else
		    			$members = Event::getAttendeesByEventId( $room["parentId"] , "admin", "isAdmin" ) ;

			    	//$members = Event::getAttendeesByEventId( $room["parentId"],"admin", "isAdmin" ) ;
			    	$typeOfConnect="attendee";
			    } else if( $room["parentType"] == City::COLLECTION ) {
			    	//TODO notify only the admin of the event
			    	$target["parent"] = City::getByUnikey( $room["parentId"]);
			    }
			}
		}
	    foreach ($members as $key => $value) 
	    {
	    	if( $key != Yii::app()->session['userId'] && !in_array($key, $people) && count($people) < self::PEOPLE_NOTIFY_LIMIT )
	    		array_push( $people, $key);
	    }

	    $ctrl = Element::getControlerByCollection($target["type"]);
	    $url = $ctrl.'/detail/id/'.$targetId;

	    if( $verb == ActStr::VERB_CLOSE ){
		    $label = $target["name"]." ".Yii::t("common","has been disabled by")." ".Yii::app()->session['user']['name'];
	    }
	    else if( $verb == ActStr::VERB_POST ){
		    if($target["type"] == Person::COLLECTION)
			    $label = Yii::app()->session['user']['name']." ".Yii::t("common","wrote a message on your wall");
			else	
				$label = $target["name"]." : ".Yii::t("common","new post by")." ".Yii::app()->session['user']['name'];
	    	$url = 'news/index/type/'.$target["type"].'/id/'.$targetId;
	    }
		else if( $verb == ActStr::VERB_FOLLOW ){
			if($target["type"]==Person::COLLECTION)
				$specificLab = Yii::t("common","is following you");
			else
				$specificLab = Yii::t("common","is following")." ".$target["name"];
		    $label = Yii::app()->session['user']['name']." ".$specificLab;
	    	$url = Person::CONTROLLER.'/detail/id/'.Yii::app()->session['userId'];
	    }
	    else if($verb == ActStr::VERB_WAIT){
		    $label = Yii::app()->session['user']['name']." ".Yii::t("common","wants to join")." ".$target["name"];
		    $url = $ctrl.'/directory/id/'.$target["id"].'?tpl=directory2';
	    }
	    else if($verb == ActStr::VERB_AUTHORIZE){
		    $label = Yii::app()->session['user']['name']." ".Yii::t("common","wants to administrate")." ".$target["name"];
		    $url = $ctrl.'/directory/id/'.$target["id"].'?tpl=directory2';
	    }
	    else if($verb == ActStr::VERB_JOIN){
		    $label = Yii::app()->session['user']['name']." ".Yii::t("common","participates to the event")." ".$target["name"];
		    $url = 'event/detail/id/'.$target["id"];
	    }
	    else if($verb == ActStr::VERB_COMMENT ){
		    $label = Yii::t("common","{who} commented your post", array("{who}"=>Yii::app()->session['user']['name']));
		    $url = $ctrl.'/detail/id/'.$target["id"];
		    if( in_array( $target["type"], array( Survey::COLLECTION, ActionRoom::COLLECTION_ACTIONS) ) ){
		    	$label = Yii::t("common","{who} commented on {what}", array("{who}"=>Yii::app()->session['user']['name'],
		    																"{what}"=>$target["entry"]["name"]));
		    	$base = 'survey/entry';
		    	if($target["type"] == ActionRoom::COLLECTION_ACTIONS)
		    		$base = 'rooms/action';
		    	$url = $base.'/id/'.$target["id"];
		    }
	    } 
	    else if($verb == ActStr::VERB_ADDROOM && @$target["parent"]){
		    $label = Yii::t("rooms","{who} added a new Voting Room on {where}",array("{who}"=>Yii::app()->session['user']['name'],
		    																					"{where}"=>$target["parent"]["name"]),Yii::app()->controller->module->id);
		    $url = 'survey/entries/id/'.$target["id"];
		    if( $target['room']["type"] == ActionRoom::TYPE_DISCUSS ){
		    	$label = Yii::t("rooms","{who} added a new Discussion Room on {where}",array("{who}"=>Yii::app()->session['user']['name'],
		    																						"{where}"=>$target["parent"]["name"]),Yii::app()->controller->module->id);
		    	$url = 'comment/index/type/actionRooms/id/'.$target["id"];

		    }else if( $target['room']["type"] == ActionRoom::TYPE_ACTIONS ){
		    	$label = Yii::t("rooms","{who} added a new Actions List on {where}",array("{who}"=>Yii::app()->session['user']['name'],
		    																					"{where}"=>$target["parent"]["name"]),Yii::app()->controller->module->id);
		    	$url = 'rooms/actions/id/'.$target["id"];
		    }
	    }
	    else if($verb == ActStr::VERB_ADD_PROPOSAL){
		    $label = Yii::t("rooms","{who} added a new Proposal {what} in {where}", array("{who}" => Yii::app()->session['user']['name'],
		    																	"{what}"=>$target['entry']["name"],
		    																	"{where}"=>$target['parent']["name"]),Yii::app()->controller->module->id);
		    $url = 'survey/entry/id/'.$target["id"];
	    }
	    else if($verb == ActStr::VERB_ADD_ACTION){
	    	$label = Yii::t("rooms","{who} added a new Action {what} in {where}", array("{who}" => Yii::app()->session['user']['name'],
		    																"{what}"=>$target["entry"]["name"],
		    																"{where}"=>$target['parent']["name"]),Yii::app()->controller->module->id);
		    $url = 'rooms/action/id/'.$target["id"];
	    } else if( $verb == ActStr::VERB_VOTE ){
		    $label = Yii::t("rooms","{who} voted on {what} in {where}", array("{who}" => Yii::app()->session['user']['name'],
		    																"{what}"=>$target["entry"]["name"],
		    																"{where}"=>$target['parent']["name"]),Yii::app()->controller->module->id);
		    $url = 'survey/entry/id/'.$target["id"];
	    }
	    /*if( $res = ActStr::getParamsByVerb($verb,$ctrl,$target,Yii::app()->session["user"]){
	    	$label = $res['label'];
	    	$url = $res['url']; 
	    } */
		else if($verb == ActStr::VERB_CONFIRM){
		    $label = Yii::app()->session['user']['name']." ".Yii::t("common","just added")." ".$member["name"]." ".Yii::t("common","as admin of")." ".$target["name"];
		    $url = $ctrl.'/directory/id/'.$target["id"].'?tpl=directory2';
	    }
	    else if($verb == ActStr::VERB_ACCEPT){
		    $label = Yii::app()->session['user']['name']." ".Yii::t("common","just added")." ".$member["name"]." ".Yii::t("common","as ".$typeOfConnect." of")." ".$target["name"];
		    // No directory for event but detail page
		    if ($target["type"] == Event::COLLECTION)
		    	$url = $ctrl.'/detail/id/'.$target["id"];
		    else 
		    	$url = $ctrl.'/directory/id/'.$targetId.'?tpl=directory2';
	    }
		else if($verb == ActStr::VERB_JOIN){
		    $label = Yii::app()->session['user']['name']." ".Yii::t("common","participates to the event")." ".$target["name"];
		    $url = $ctrl.'/detail/id/'.$targetId;
	    }
	    else if($verb == ActStr::VERB_SIGNIN){
			 $label = $member["name"]." ".Yii::t("common","confirms your invitation and create an account.");
			 $url = $ctrl.'/detail/id/'.$memberId;
		} 
		else
	    	$label = Yii::app()->session['user']['name']." ".$verb." you to ".$target["name"] ;

		if($invitation == ActStr::VERB_INVITE && $verb != ActStr::VERB_CONFIRM){
			 $label = Yii::app()->session['user']['name']." ".Yii::t("common","has invited")." ".$member["name"]." ".Yii::t("common","to join")." ".$target["name"];
			 if ($target["type"] == Event::COLLECTION)
		    	$url = $ctrl.'/detail/id/'.$target["id"];
		    else 
			 	$url = $ctrl.'/directory/id/'.$target["id"].'?tpl=directory2';
		}

		
	    $notif = array( 
	    	"persons" => $people,
            "label"   => $label,
            "icon"    => $icon ,
            "url"     => Yii::app()->createUrl('/'.Yii::app()->controller->module->id.'/'.$url ) 
        );
	    $stream["notify"] = ActivityStream::addNotification( $notif );
	    ActivityStream::addEntry($stream);

	    //TODO mail::invited
	}

	/* NOT USED ANYMORE
	When a link is create between 2 people (follow)
	notify the followed person
	actionType : follow(default) or invite 
	*/
	/*public static function connectPeople ( $followedPersonId, $followerId, $followerName, $actionType=null ) 
	{
	    $asParam = array(
	    	"type" => ActStr::TEST, 
            "verb" => ActStr::VERB_FOLLOW,
            "author"=>array(
            	"type" => Person::COLLECTION,
            	"id"   => ( isset(Yii::app()->session["userId"]) ) ? Yii::app()->session["userId"] : null
            ),
            "object"=>array(
	            "type" => Person::COLLECTION,
	            "id"   => $followedPersonId
            )
        );
	    $stream = ActStr::buildEntry($asParam);

	    $actionMsg = ($actionType == ActStr::VERB_INVITE ) ? " invited you" : " is following you";
		$notif = array( 
	    	"persons" => array($followedPersonId),
            "label"   => $followerName.$actionMsg , 
            "icon"    => ActStr::ICON_SHARE ,
            "url"     => Yii::app()->createUrl('/'.Yii::app()->controller->module->id.'/person/detail/id/'.$followerId)
        );
	    $stream["notify"] = ActivityStream::addNotification( $notif );
	    ActivityStream::addEntry($stream);
	    
	    //TODO mail::following
	    //add a link to follow back easily
	}*/
	/* 
	TODO BOUBOULE => A DECALER OU NON OU RENOMER ACTIVITYSTREAM EMBED CREER LA NEWS ACTIVITY HISTORY
	inject to activity stream
	When a project, or event is create 
	It will appear for person or organization
	// => advanced notification to add if one user wants to be notified for all news projects in certain field (Tags)
	*/

	public static function createdObjectAsParam($authorType, $authorId, $objectType, $objectId, $targetType, $targetId, $geo, $tags, $address, $verb="create"){
		$param=array("type" => ActivityStream::COLLECTION, "verb" => ActStr::VERB_CREATE);
		if (!empty($objectType)){
			$param["object"] = array(
				"type" => $objectType, 
				"id" => $objectId
			);
		}
		if (!empty($targetType)){
			$param["target"] = array(
				"type" => $targetType, 
				"id" => $targetId
			);
		}

		if (!empty($tags))
			$param["tags"]=$tags;
		if(!empty($geo))
			$param["geo"]=$geo;
		if (!empty($address))
			$param["address"]=$address;	
		$stream = ActivityStream::buildEntry($param);
	    ActivityStream::addEntry($stream);

	}


	/**
	 * When a moderate is occured, is create notification for author and superadmin
	notify the moderate
	 * @param type $news the news moderated
	 * @return type
	 */
	public static function moderateNews ($news) 
	{
	    $asParam = array(
	    	"type" => ActStr::TEST, 
            "verb" => ActStr::VERB_MODERATE,
            "author"=>array(
            	"type" => Person::COLLECTION,
            	"id"   => ( isset(Yii::app()->session["userId"]) ) ? Yii::app()->session["userId"] : null
            ),
            "object"=>array(
	            "type" => News::COLLECTION,
	            "id"   => (string)$news['_id']
            )
        );

	    $stream = ActStr::buildEntry($asParam);

	    $actionMsg = ($news['isAnAbuse'] == true ) ? "Modération : Votre news postée le ".date('d-m-Y à H:i', $news['created']->sec)." ne sera plus affichée" : "Modération : Votre news postée le ".date('d-m-Y à H:i', $news['created']->sec)." restera affichée";

		$notif = array( 
	    	"persons" => array($news['author']['id']),
            "label"   => $actionMsg , 
            "icon"    => "fa-rss" ,
            "url"     => Yii::app()->createUrl('/'.Yii::app()->controller->module->id.'/news/detail/id/'.@(string)$news['_id'])
        );

	    $stream["notify"] = ActivityStream::addNotification( $notif );
    	ActivityStream::addEntry($stream);
	    
	    //TODO mail::following
	    //add a link to follow back easily
	}

	/**
	 * Notification for the super admins.
	 * Exemple : The cron return a mail error caused by alice@example.com
	 * => The cron is the author
	 * => return is the verb
	 * => A mail error is the object
	 * => alice@example.com is the target
	 * @param String $verb Can be find on const of the ActStr class
	 * @param array $author the one making the action array(type, id)
	 * @param array $object the object. array(type, id, event)
	 * @param array $target the target. array(type, id, email)
	 * @return array : result : boolean / msg : string
	 */
	public static function actionToAdmin ( $verb, $author, $object, $target)  {
 		//Retrieve all super admins of the plateform
 		//TODO SBAR => superAdmins ID should be cached in order to make this request quicker ?
 		$superAdmins = Person::getCurrentSuperAdmins();

 		$asParam = array(
	    	"type" => ActStr::TEST, 
            "verb" => $verb,
            "author"=>$author,
            "object"=>$object,
 			"target"=>$target
        );

 		//Error 
 		if ($verb == ActStr::VERB_RETURN) {
 			if (@$object["event"] == MailError::EVENT_BOUNCED_EMAIL) {
 				$actionMsg = "Fatal error sending an email to ".$target["email"].". User should be deleted.";	
 			} else if (@$object["event"] == MailError::EVENT_DROPPED_EMAIL || @$object["event"] == MailError::EVENT_SPAM_COMPLAINTS) {
 				$actionMsg = "Error sending an email to ".$target["email"].". User is flagged and will not receive a mail anymore.";	
 			} else {
 				error_log("Unknown event in Mail Error : no notification generated.");
 				return(array("result" => false, "msg" => "Unknown event in Mail Error : no notification generated."));
 			}
 			
 		}
	    $stream = ActStr::buildEntry($asParam);

		$notif = array( 
	    	"persons" => array_keys($superAdmins),
            "label"   => $actionMsg , 
            "icon"    => "fa-cog" ,
            "url"     => Yii::app()->createUrl('/'.Yii::app()->controller->module->id.'/admin/mailerrordashboard')
        );

	    $stream["notify"] = ActivityStream::addNotification( $notif );
    	ActivityStream::addEntry($stream);
	}
}
