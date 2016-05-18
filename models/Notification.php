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
	public static function invited2Project( $memberType, $memberId, $projectId,$projectName ) 
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
	}

	/*
	when someone joins or leaves or disables a project / organization / event
	notify all contributors

	the action/verb can be done by the person or by an admin (remove from project)
	$verb can be join, leave
	$icon : anicon to show
	$member : a map of the object member 
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
	    	//TODO notify only the admin of the event
	    	$members = Event::getAttendeesByEventId( $targetId ,"all", null ) ;
	    	$typeOfConnect="attendee";
	    }
		else if($target["type"] == Person::COLLECTION)
			$people = array($targetId);
		else if($target["type"] == News::COLLECTION){
			$author=News::getAuthor($target["id"]);
			$people = array($author["author"]);
		} 
		else if( $target["type"] == Survey::COLLECTION || $target["type"] == ActionRoom::COLLECTION ){
			$entryId = $target["id"];
			if($target["type"] == Survey::COLLECTION ){
				$entry = Survey::getById( $target["id"] );
				$entryId = (string)$entry["survey"];
			}else if($target["type"] == ActionRoom::COLLECTION_ACTIONS ){
				$entry = ActionRoom::getActionById( $target["id"] );
				$entryId = (string)$entry["survey"];
			}
			$room = ActionRoom::getById( $entryId );
			$target["room"] = $room;

			if( $room["parentType"] == Project::COLLECTION ) {
		    	$members = Project::getContributorsByProjectId( $room["parentId"] ,"all", null ) ;
				$typeOfConnect="contributor";
		    }
		    else if( $room["parentType"] == Organization::COLLECTION) {
		    	$members = Organization::getMembersByOrganizationId( $room["parentId"] ,"all", null ) ;
		    	$typeOfConnect="member";
		    }
		    else if( $room["parentType"] == Event::COLLECTION ) {
		    	//TODO notify only the admin of the event
		    	$members = Event::getAttendeesByEventId( $room["parentId"],"all", null ) ;
		    	$typeOfConnect="attendee";
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
		    $label = $target["name"]." : ".Yii::t("common","new post by")." ".Yii::app()->session['user']['name'];
	    	$url = 'news/index/type/'.$target["type"].'/id/'.$targetId.'?isFisrt=1';
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
		    $url = 'news/detail/id/'.$target["id"];
	    }
	    else if($verb == ActStr::VERB_COMMENT){
		    $label = Yii::app()->session['user']['name']." ".Yii::t("common","has commented your post");
		    $url = $ctrl.'/detail/id/'.$target["id"];
	    } else if($verb == ActStr::VERB_ADDROOM){
		    $label = Yii::app()->session['user']['name']." ".Yii::t("common","added a new Voting Room on ".$target['room']["parentType"]);
		    $url = 'survey/entries/id/'.$target["id"];
		    if( $target['room']["type"] == ActionRoom::TYPE_DISCUSS ){
		    	$label = Yii::app()->session['user']['name']." ".Yii::t("common","added a new Discussion Room on ".$target['room']["parentType"]);
		    	$url = 'comment/index/type/actionRooms/id/'.$target["id"];

		    }
	    }
	    else if($verb == ActStr::VERB_ADD_PROPOSAL){
		    $label = Yii::app()->session['user']['name']." ".Yii::t("common","added a new Proposal");
		    $url = 'survey/entry/id/'.$target["id"];
	    }
	    else if($verb == ActStr::VERB_ADD_ACTION){
		    $label = Yii::app()->session['user']['name']." ".Yii::t("common","added a new Action");
		    $url = 'rooms/action/id/'.$target["id"];
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

		if($invitation == ActStr::VERB_INVITE){
			 $label = Yii::app()->session['user']['name']." ".Yii::t("common","has invited")." ".$member["name"]." ".Yii::t("common","to join")." ".$target["name"];
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
	When a link is create between 2 people (follow)
	notify the followed person
	actionType : follow(default) or invite 
	*/
	public static function connectPeople ( $followedPersonId, $followerId, $followerName, $actionType=null ) 
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
	}
	/*
	When a project is create 
	The project is inject to activity stream
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


	/*
	When a moderate is occured, is create notification for author and superadmin
	notify the moderate
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
}
