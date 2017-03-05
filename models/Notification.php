<?php 

class Notification{
	/* *
	*	Authors 
	*		@Bouboule [clement.damiens@gmail.com]
	*		@Bardot 
	**/
	//limit  the size of the notification map
	//when an organization/project has a huge number of members
	const PEOPLE_NOTIFY_LIMIT = 50;

	/**
	* $notificationTree is an multi-array defining each notification with different level
	* Levl:
	** First level is the verb
	** Second level is the type
		* $type::COLLECTION is some case is the second level (add, comment, etc)
		* Link::Type asAdmin || asMember in other case is the second on (join, ask, confirm, validate)
	** Third level is more about the target $targetIsAuthor for news
	* A part is composed by:
	* params boolean $repeat indicating if notification case can be repeat
	* params string $label defining the label at the creation of the notification
	* params string $labelRepeat defining the label when notification is updated
	* params string $url link of notification
	* params string $icon icon of notification
	* params array $labelArray indicating location of label to precising 
	* params boolean $notifyUser in case of notification should send to a user in addition to the target
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
			"type"=> array(
				"user"=> array(
					"label" => "{who} is following you",
					"labelRepeat"=>"{who} are following you"
				)
			),
			"label" => "{who} is following {where}",
			"labelRepeat"=>"{who} are following {where}",
			"labelArray" => array("who","where"),
			"icon" => "fa-link",
			"url" => "{ctrlr}/directory/id/{id}"
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
			"context" => "admin",
			"mail" => array(
				"type"=>"instantly"
			),
			"icon" => "fa-cog",
			"url" => "{ctrlr}/directory/id/{id}"
		),
		//// USED ONLY FOR EVENT
		// FOR ORGANIZATRION AND PROJECT IF ONLY MEMBER
		ActStr::VERB_JOIN => array(
			"repeat" => true,
			//"context" => "members",
			"mail" => array(
				"type"=>"daily"
			),
			"type" => array(
				"asMember"=> array(
					Event::COLLECTION => array(
						"label"=>"{who} participates to {where}",
						"labelRepeat"=>"{who} participate to {where}"
					),
					Organization::COLLECTION => array(
						"label"=>"{who} joins {where}",
						"labelRepeat"=>"{who} join {where}"
					),
					Project::COLLECTION => array(
						"label"=>"{who} contributes to {where}",
						"labelRepeat"=>"{who} contribute to {where}"
					)
				),
				"asAdmin" => array(
					"label"=>"{who} becomes administrator of {where}",
					"labelRepeat"=>"{who} become administrator of {where}"
				)
			),
			"labelArray" => array("who","where"),
			"icon" => "fa-group",
			"url" => "{ctrlr}/directory/id/{id}"
		),
		ActStr::VERB_COMMENT => array(
			"repeat" => true,
			"type" => array(
				News::COLLECTION => array(
					"label" => "{who} commented on your news {what}",
					"labelRepeat" => "{who} added comments on your news {what}",
					"targetIsAuthor"=> array(
						"label"=> "{who} commented a news {what} posted on {where}",
						"labelRepeat"=> "{who} added comments on a news {what} posted on {where}"
					),
					"notifyUser" => true,
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
						"label"=>"{who} likes a news {what} from {where}",
						"labelRepeat"=>"{who} like a news {what} from {where}"
					) ,
					"label"=>"{who} likes your news {what}",
					"labelRepeat"=>"{who} like your news {what}",
					"notifyUser" => true,
					"url" => "news/detail/id/{id}"
				),
				Comment::COLLECTION => array(
					"label"=>"{who} likes your comment on {where}",
					"labelRepeat"=>"{who} like your comment on {where}",
					"url" => "targetTypeUrl"
				)
			),
			"labelArray" => array("who", "where"),
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
						"label"=>"{who} disapproves a news {what} from {where}",
						"labelRepeat"=>"{who} disapprove a news {what} from {where}"
					),
					"label"=>"{who} disapproves your news {what}",
					"labelRepeat"=>"{who} disapproves your news {what}",
					"notifyUser" => true,
					"url" => "news/detail/id/{id}"
				),
				Comment::COLLECTION => array(
					"label"=>"{who} disapproves your comment on {where}",
					"labelRepeat"=>"{who} disapproves your comment on {where}",
					"url" => "targetTypeUrl"
				)
			),
			"labelArray" => array("who", "where"),
			"mail" => array(
				"type"=>"instantly",
				"to" => "author" //If orga or project to members
			),
			"icon" => "fa-thumbs-down"
		),
		ActStr::VERB_POST => array(
			"repeat" => true,
			"type" => array(
				"targetIsAuthor" => array(
					"label"=>"{where} publishes a new post",
					"labelRepeat"=>"{where} publishes new posts"
				),
				"userWall" => array(
					"label"=>"{who} writes a post on your wall",
					"labelRepeat"=>"{who} write posts on your wall"
				),
				"label"=>"{who} writes a post on the wall of {where}",
				"labelRepeat"=>"{who} write posts on the wall of {where}"
			),
			"url" => "news/index/type/{collection}/id/{id}",
			"labelArray" => array("who", "where"),
			"icon" => "fa-rss"
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
				ActionRoom::COLLECTION_ACTIONS=> array(
					"url"=>"rooms/actions/id/{objectId}",
					"label"=> "{who} added a new actions list on {where}"
				),
				ActionRoom::TYPE_DISCUSS => array(
					"url"=>"comment/index/type/actionRooms/id/{objectId}",
					"label" => "{who} added a new discussion room on {where}"
				),
				ActionRoom::TYPE_VOTE => array(
					"url"=>"survey/entries/id/{objectId}",
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
					"url" => "targetTypeUrl",
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
		),
		ActStr::VERB_VOTE => array(
			"repeat" => true,
			"label" => "{who} voted on {what} in {where}",
			"labelRepeat"=>"{who} have voted on {what} in {where}",
			"labelArray" => array("who","where"),
			"icon" => ActStr::ICON_VOTE,
			"url" => "survey/entry/id/{id}"
		),
		/*
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
		),*/
		ActStr::VERB_CONFIRM => array(
			"repeat" => true,
			"type"=>array(
				"asMember"=>array(
					"label" => "{who} confirmed the invitation to join {where}",
					"labelRepeat" => "{who} have confirmed the invitation to join {where}"
				),
				"asAdmin"=>array(
					"label" => "{who} confirmed the invitation to administrate {where}",
					"labelRepeat" => "{who} have confirmed the invitation to administrate {where}"
				)
			),
			"labelArray" => array("who","where"),
			"icon" => "fa-check",
			"url" => "{ctrlr}/directory/id/{id}"
		),
		//FROM USER LINK TO AN ELEMENT ACTING ON IT
		ActStr::VERB_INVITE => array(
			"repeat" => true,
			"notifyUser" => true,
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
				),
				"user" => array(
					"asMember" => array(
						"label"=>"{author} invited you to join {where}"
					),
					"asAdmin" => array(
						"label"=>"{author} invited you to administrate {where}"
					)
				)
			),
			"labelArray" => array("author","who","where"),
			"context" => "admin",
			"mail" => array(
				"type"=>"instantly",
				"to" => "user"
			),
			"icon" => "fa-send",
			"url" => "{ctrlr}/detail/id/{id}"
		),
		// AJouter la confirmation vers l'utilisateur
		//Creer le mail pour l'utilisateur accepté !!
		ActStr::VERB_ACCEPT => array(
			"repeat" => true,
			//"context" => array("user","members"),
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
			"icon" => "fa-check",
			"url" => "{ctrlr}/directory/id/{id}"
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
	/** TODO BOUBOULE
	* Get admins and member of target to notify
	* params string $id && $type defined the target
	* params string $impact is which part of community is notified
	* params string $authorId is used to avoid to notify author of the action
	* params string $alreadyAuthorNotify could be used if a notification for a specific user is already create
	* return array of id with two boolean for each id, isUnseen && isUnread
	**/
	public static function communityToNotify($construct, $alreadyAuhtorNotify=null){
		//inform the entities members of the new member
		//build list of people to notify
		$type=$construct["target"]["type"];
		$id=$construct["target"]["id"];
		$impactType="all";
		$impactRole=null;
		if(@$construct["context"]){
			$impactType=Person::COLLECTION;
			$impactRole="isAdmin";
		}
        $people = array();
	    $members = array();
	    if( $type == Project::COLLECTION )
	    	$members = Project::getContributorsByProjectId( $id ,$impactType, $impactRole);
	    else if( $type == Organization::COLLECTION)
	    	$members = Organization::getMembersByOrganizationId( $id ,$impactType, $impactRole) ;
	    else if( $type == Event::COLLECTION )
	    	$members = Event::getAttendeesByEventId( $id , "admin", "isAdmin" ) ;
		else if($type == Person::COLLECTION)
			$people[$id] = array("isUnread" => true, "isUnseen" => true);
		else if($type == News::COLLECTION){
			if(Yii::app()->session["userId"] != $alreadyAuhtorNotify){
				$news=News::getById($id);
				if(($alreadyAuhtorNotify != $news["author"]["id"] && $news["target"]["type"]==Person::COLLECTION) || ( $news["target"]["type"] !=Person::COLLECTION && Yii::app()->session["userId"]!=$news["author"]["id"])){
					//echo $alreadyAuhtorNotify;
					if($news["target"]["type"] !=Person::COLLECTION){
						if( $news["target"]["type"] == Project::COLLECTION )
	    					$members = Project::getContributorsByProjectId( $news["target"]["id"] ,$impactType, $impactRole);
	    				else if( $news["target"]["type"] == Organization::COLLECTION)
	    					$members = Organization::getMembersByOrganizationId( $news["target"]["id"] ,$impactType, $impactRole);
	    				else if( $news["target"]["type"] == Event::COLLECTION )
	    					$members = Event::getAttendeesByEventId( $news["target"]["id"] , "all", null ) ;
						$construct["target"]["parent"]=array("id"=>$news["target"]["id"],"type"=> $news["target"]["type"]);
					}
					else
						$people[$news["author"]["id"]] = array("isUnread" => true, "isUnseen" => true);
				}
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
			    else if( $room["parentType"] == Event::COLLECTION )
		    			$members = Event::getAttendeesByEventId( $room["parentId"] , "admin", "isAdmin" ) ;
		    	$construct["target"]["parent"]=array("id"=>$room["parentId"],"type"=> $room["parentType"]);
			}
		}
	    foreach ($members as $key => $value) 
	    {
	    	if( $key != Yii::app()->session['userId'] && !in_array($key, $people) && count($people) < self::PEOPLE_NOTIFY_LIMIT && $key != $alreadyAuhtorNotify){
	    		$people[$key] = array("isUnread" => true, "isUnseen" => true); 
	    	}
	    }
	    $construct["community"]=$people;
	    return $construct;
	}
	/** TODO BOUBOULE
	* getLabelNotification will create the specific label for notification to create or update
	* params array $construct is the constructor for a notification
	* params string $type gives if notification is for specific user or for the community
	* params integer $count in case of repeat indicates the number of repetition for a specific notif
	* params array $notification is the existed notification when come from checkIfAlreadyExist method 
	* params string $repeat indicates if label used is normal one or the repeat label
	* return label ready to push in dB
	**/
	public static function getLabelNotification($construct, $type=null, $count=1, $notification=null, $repeat=""){
		$specifyLabel = array();
		//GetLAbel
		//$type=""; else "Repeat"
		if($type && $construct["levelType"]!=Comment::COLLECTION){
			if($construct["levelType"] == News::COLLECTION)
				$label = $construct["type"][$construct["levelType"]]["label".$repeat];
			else
				$label = $construct["type"]["user"][$construct["levelType"]]["label".$repeat];
		}
		else if($construct["levelType"]){
 	    	if(@$target["targetIsAuthor"])
				$label = $construct["type"][$construct["levelType"]]["targetIsAuthor"]["label".$repeat];
			else if(!@$construct["type"][$construct["levelType"]]["label"])
				$label = $construct["type"][$construct["levelType"]][$construct["target"]["type"]]["label".$repeat];
			else{
				$label = $construct["type"][$construct["levelType"]]["label".$repeat];
				//Specific case for comment, like, unlike on news
				if($construct["levelType"]==News::COLLECTION){
					$news=News::getById($construct["target"]["id"]);
					if($news["target"]["type"] != Person::COLLECTION)
						$label = $construct["type"][$construct["levelType"]]["targetIsAuthor"]["label".$repeat];
				}
			}
 	    	//$notifyObject=$typeAction;
 	    }
 	    // CASE FOR NEWS
		else if (!@$construct["label".$repeat]){
			if(@$construct["target"]["targetIsAuthor"])
				$label = $construct["type"]["targetIsAuthor"]["label".$repeat];
			else if(@$construct["target"]["userWall"])
				$label = $construct["type"]["userWall"]["label".$repeat];
			else
				$label = $construct["type"]["label".$repeat];
		}
		else
			$label = $construct["label".$repeat];
		if($construct["labelUpNotifyTarget"]=="object"){
			$memberName="";
			if($construct["object"]){
				if(@$construct["object"]["name"])
					$memberName=$construct["object"]["name"];
				else{
					foreach($construct["object"] as $user){
						$memberName=$user["name"];
					}
				}
			}
			$specifyLabel["{author}"] = Yii::app()->session['user']['name'];
		}else {
			$memberName=Yii::app()->session['user']['name'];
		}

		if($count==1){
			$specifyLabel["{who}"] = $memberName;
		}
		else if($count==2){
			foreach($notification[$construct["labelUpNotifyTarget"]] as $data){
				$lastAuthorName=$data["name"];
				break; 
			}
			$specifyLabel["{who}"] = $memberName." ".Yii::t("common","and")." ".$lastAuthorName;
		}
		else {
			foreach($notification[$construct["labelUpNotifyTarget"]] as $data){
				$lastAuthorName=$data["name"];
				break;
			}
			$nbOthers = $count - 2;
			if($nbOthers == 1) $labelUser = "person"; else $labelUser = "persons";
			$specifyLabel["{who}"] = $memberName.", ".$lastAuthorName." ".Yii::t("common","and")." ".$nbOthers." ".Yii::t("common", $labelUser);
		}
		if(in_array("where",$construct["labelArray"])){
			if(@$construct["target"]["name"])
				$specifyLabel["{where}"] = $construct["target"]["name"];
			else{
				$resArray=self::getTargetInformation($construct["target"]["id"],$construct["target"]["type"], $construct["object"]);
				$specifyLabel["{where}"] = @$resArray["{where}"];
				if(@$resArray["{what}"])
					$specifyLabel["{what}"]=$resArray["{what}"];
			}
		}
		//if(in_array("type", $labelArray))
		//	$specifyLabel["{type}"] = $labelArray["typeValue"];
		if(in_array("what",$construct["labelArray"]))
			$specifyLabel["{what}"] = @$construct["object"]["name"];
		return Yii::t("notification",$label, $specifyLabel);	
	}

	public static function getUrlNotification($construct){
		if(@$construct["url"])
			$url=$construct["url"];
		else 
			$url=$construct["type"][$construct["levelType"]]["url"];
		if($url=="targetTypeUrl")
			$url=$construct["type"][$construct["target"]["type"]]["url"];
		$url = str_replace("{ctrlr}", Element::getControlerByCollection($construct["target"]["type"]), $url);
		$url = str_replace("{collection}", $construct["target"]["type"], $url);
		$url = str_replace("{id}", $construct["target"]["id"], $url);
		if(stripos($url, "{objectType}") > 0)
			$url = str_replace("{objectId}", $construct["object"]["id"], $url);
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
	public static function checkIfAlreadyNotifForAnotherLink($construct)	{
		$where=array("verb"=>$construct["verb"], "target.id"=>$construct["target"]["id"], "target.type"=>$construct["target"]["type"]);
		if($construct["labelUpNotifyTarget"]=="object"){
			$where["author.".Yii::app()->session["userId"]] = array('$exists' => true);
			$object=$construct["author"];
		}
		if($construct["levelType"])
			$where["notify.objectType"] = $construct["levelType"];
        else if($construct["verb"]==Actstr::VERB_POST && !@$construct["target"]["targetIsAuthor"] && !@$construct["target"]["userWall"])
		    $where["notify.objectType"]=News::COLLECTION;
		$notification = PHDB::findOne(ActivityStream::COLLECTION, $where);
		if(!empty($notification)){
			if((@$notification[$construct["labelUpNotifyTarget"]] && @$notification[$construct["labelUpNotifyTarget"]][$construct["author"]["id"]]
				) || (@$construct["type"] && @$construct["type"][$construct["levelType"]] && @$construct["type"][$construct["levelType"]]["noUpdate"] ))
				return true;
			else{
				$countRepeat=1;
				foreach($notification[$construct["labelUpNotifyTarget"]] as $i){$countRepeat++;}
				// Get new Label
				$newLabel=self::getLabelNotification($construct, null, $countRepeat, $notification, "Repeat");
				// Add new author to notification
				if($construct["labelUpNotifyTarget"] == "object")
					foreach($construct["object"] as $key => $data){
						$notification["object"][$key]=$data;
					}
				else
					$notification["author"][Yii::app()->session['userId']]=array("name" => Yii::app()->session['user']['name']);
				PHDB::update(ActivityStream::COLLECTION,
					array("_id" => $notification["_id"]),
					array('$set' => array(
						$construct["labelUpNotifyTarget"]=>$notification[$construct["labelUpNotifyTarget"]],
						"notify.id" => $construct["community"],
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
	* Create notification in db ActivityStream
	* return true
	*/
	public static function createNotification($construct, $type=null){
		$asParam = array(
	    	"type" => "notifications", 
            "verb" => $construct["verb"],
            "author"=> $construct["author"],
 			"target"=> $construct["target"]
        );
        if($construct["object"])
        	$asParam["object"]=$construct["object"];
 	    $stream = ActStr::buildEntry($asParam);
		$notif = array( 
	    	"persons" => $construct["community"],
            "label"   => self::getLabelNotification($construct,$type),
            "icon"    => $construct["icon"],
            "url"     => self::getUrlNotification($construct)
        );
        if($construct["levelType"] && $type==null)
        	$notif["objectType"]=$construct["levelType"];
        else if($construct["verb"]==Actstr::VERB_POST && !@$construct["target"]["targetIsAuthor"] && !@$construct["target"]["userWall"])
		    $notif["objectType"]=News::COLLECTION; 
	    $stream["notify"] = ActivityStream::addNotification( $notif );
    	ActivityStream::addEntry($stream);
	}

	/** TODO BOUBOULE  
	* construct notification is the constructor of a notification
	* Firstly this method will create a constructor common for all methods called
	* Secondly it checks if a specific user should be notify in addition to the target community
	* Thirdly it gets community to notyfy
	* Fourthly it checks if notification in this usecase already exists
	* Fively it creates notification
	* params string $verb indicates the verb of notification and the part of notificationTree to get
	* params array $author is in most of case people who executing the action or the person who is concerning by the action
	* params array $target is target of notification 
	* params array $object  is object of notification (could be null)
	* params string levelType indicates if there is subLevel
	* params string||array $context should be use to specify community to notify (only admin, only person, etc)
	*/
	public static function constructNotification($verb, $author, $target, $object = null, $levelType = null, $context = null){
		$notificationPart = self::$notificationTree[$verb];
		$notificationPart["verb"] = $verb;
		$notificationPart["target"]=$target;
		$notificationPart["object"]=$object;
		$notificationPart["levelType"]=$levelType;
		// Object could be the object in following method if action is by an other acting on an other person (ex: author add so as member {"member"=> $author})
		if(@$author["_id"])
			$authorId=(string)$author["_id"];
		else
			$authorId=$author["id"];
		$notificationPart["author"]=array("id"=>$authorId,"name"=>$author["name"]);
		//Move labelUpToNotify in getLabel
		$notificationPart["labelUpNotifyTarget"] = "author";
		// Create notification specially for user added to the next notify for community of target
		if(@$notificationPart["notifyUser"] || (@$notificationPart["type"] && @$notificationPart["type"][$levelType] && @$notificationPart["type"][$levelType]["notifyUser"])){
			$update=false;
			$isToNotify=true;
			// If answered on comment is the same than on the news or other don't notify twice the author of parent and comment
			if($verb==Actstr::VERB_COMMENT){
				$comment=Comment::getById($object["id"]);
				$userNotify=$comment["author"]["id"];
				// Case when user answer to his comment
				if($notificationPart["target"]["type"]==News::COLLECTION && $userNotify==Yii::app()->session["userId"])
					$isToNotify=false;
				if($notificationPart["target"]["type"]==News::COLLECTION){
					$news=News::getById($notificationPart["target"]["id"]);
					$userNotify=$news["author"]["id"];
					// Case when user comment a news where target is author
					if(@$news["targetIsAuthor"])
						$isToNotify=false;
				}

			}else
				$userNotify=$author["id"];
			if($isToNotify){
				$alreadyAuhtorNotify=$userNotify;
				$notificationPart["community"]=array($userNotify=>array("isUnread" => true, "isUnseen" => true));
				if(@$notificationPart["type"][$typeAction] && @$notificationPart["type"][$levelType]["repeat"])
					$update=self::checkIfAlreadyNotifForAnotherLink($notificationPart);
				if($update==false){
			 	    //$notifyObject=null;
			 	    //--------- MOVE ON GETLABEL -----------//
			 	   ////////// !!!!!! $type was to null ... change to user for comment on comment but could be a bug in other notification with user notification ... ???? !!!!! ////////////////
			 	   $type="user";
			 	    if(@$notificationPart["type"]["user"]){
						$type="user";
						$notificationPart["labelUpNotifyTarget"]="object";
					}
					//REFACTOR -- VERYFY ELSE NO IMPACT ON A NOTIF
					/*else{
						if(@$target["targetIsAuthor"])
							$label = $notificationPart["type"][$levelType]["targetIsAuthor"]["label"];
						else
							$label = $notificationPart["type"][$levelType]["label"];
						//$labelUpNotifyTarget="author";
						//$notifyObject=$typeAction;
					}*/
					// -------- END MOVE ON GETLABEL --------///
					$notificationPart["author"]=array($userNotify=> array("name"=> Yii::app()->session["user"]["name"]));
					self::createNotification($notificationPart,$type);
			    }
			} 
		}
		// COnstruct notification for target
		$notificationPart = self::communityToNotify($notificationPart, @$alreadyAuhtorNotify);
		//$["community"]=$community;
		$update = false;
		if(!empty($notificationPart["community"])){
		    if(in_array("author",$notificationPart["labelArray"])){
		        $notificationPart["object"] = array($authorId => array("name"=>$author["name"]));
		        $notificationPart["author"] = array(Yii::app()->session["userId"]=> array("name"=> Yii::app()->session["user"]["name"]));
		        $notificationPart["labelUpNotifyTarget"]="object";
		    }
		    else if($object){
		        $notificationPart["object"]=array("id" => $object["id"], "type" => $object["type"]);
		    }
		    if($notificationPart["verb"]==Actstr::VERB_COMMENT && $notificationPart["levelType"]==Comment::COLLECTION)
		    	$notificationPart["levelType"]=$notificationPart["target"]["type"];
			if((@$notificationPart["repeat"] && $notificationPart["repeat"]) || (@$notificationPart["type"] && @$notificationPart["type"][$levelType] && @$notificationPart["type"][$levelType]["repeat"])){	
				$update=self::checkIfAlreadyNotifForAnotherLink($notificationPart);
				/********* MAILING PROCEDURE *********/
				/** Update mail notification
				* Modifier le cron si le cron n'est pas déjà envoyé (sinon cf. création mail notification:
					** Ajouté l'object concerné
				* Le cron sera récupéré sur les cinq/dix minutes depuis sa création 
				* Regarder si la communauté notifiée par mail n'a pas vu la notification associée (isUnseen exists)
				* Envoie de l'email
				**/
				/********** END MAIL PROCEDURE ******/

			}
			if($update==false && !empty($notificationPart["community"]))
				 self::createNotification($notificationPart);
				/********* MAILING PROCEDURE *********/
				/** Création mail notification
				* Créer un cron avec:
					** type "notificaitons"
					** Id de la notification
					** object a notifié
					** tpl égale à $notificationPart["mail"]
				* Récupérer les id de la communauté notifiée qui n'est pas connectée (sinon on considère qu'elle a vu la notification)
				* Le cron sera récupéré sur les cinq/dix minutes depuis sa création 
				* Regarder si la communauté notifiée par mail n'a pas vu la notification associée (isUnseen exists)
				* Envoie de l'email
				**/
				/********** END MAILING PROCEDURE *********/
		}
	}
	/** TODO BOUBOULE
	* !!!!!???? Should be written on communityToNotify ???!!!!!! 
	* getTargetInbformation is used by getLabelNotification
	* return {where} and {what} values
	**/
	public static function getTargetInformation($id, $type, $object=null) {	
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
		else if(@$parent["name"]){
			if($object && $object["type"]==Comment::COLLECTION && $type==News::COLLECTION){
				$comment=Comment::getById($object["id"]);
				if($comment["author"]["id"]==$news["author"]["id"] && !@$news["targetIsAuthor"])
					$res["{where}"]=Yii::t("notification","your news");
				else
					$res["{where}"]=Yii::t("notification","the news of")." ".$parent["name"];
			}
			else
				$res["{where}"]=$parent["name"];
			if($type=="news"){
				if(@$news["title"])
					$res["{what}"]="&quot;".$news["title"]."&quot;";
				else
					$res["{what}"]="&quot;".strtr($news["text"], 0, 20)."...&quot;";
			}
			else if($object){
				$object=Element::getElementSimpleById($object["id"], $object["type"]);
				$res["{what}"]=$object["name"];
			}

		}
		else if (@$target["entry"]){
			$res["{what}"]=$target["entry"]["name"];
			if(@$target["parent"])
				$res["{where}"] = $target["parent"]["name"];
		} 
		else if(@$target["room"]){
			$res["{what}"]=$target["room"]["name"];
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
		
		    $param["label"] = "A crée";
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
