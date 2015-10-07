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
            "actor"=>array(
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
	*/
	public static function actionOnPerson ( $verb, $icon, $member, $target ) 
	{
		$targetId = ( isset( $target["id"] ) ) ? $target["id"] : (string)$target["_id"] ;
		if( $member )
			$memberId = ( isset( $member["id"] ) ) ? $member["id"] : (string)$member["_id"] ;
	    $asParam = array(
	    	"type" => ActStr::TEST, 
            "verb" => $verb,
            "actor"=>array(
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
        	if( $member['type'] == Organization::COLLECTION )
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

	    //inform the projects members of the new member
	    $members = null;
	    if( $target["type"] == Project::COLLECTION ) 
	    	$members = Project::getContributorsByProjectId( $targetId ,"all", null ) ;
	    else if( $target["type"] == Organization::COLLECTION ) 
	    	$members = Organization::getMembersByOrganizationId( $targetId ,"all", null ) ;
	    else if( $target["type"] == Event::COLLECTION ) 
	    	$members = Event::getAttendeesByEventId( $targetId ,"all", null ) ;

	    foreach ($members as $key => $value) 
	    {
	    	if( $key != Yii::app()->session['userId'] && !in_array($key, $people) && count($people) < self::PEOPLE_NOTIFY_LIMIT )
	    		array_push( $people, $key);
	    }
	    $label = Yii::app()->session['user']['name']." ".$verb." you to ".$target["name"] ;
	    $url = $target["type"].'/dashboard/id/'.$targetId;
	    if( $verb == ActStr::VERB_CLOSE )
	    	$target["name"]." has been disabled by ".Yii::app()->session['user']['name'];
	    else if( $verb == ActStr::VERB_POST ){
	    	$target["name"]." : new post by ".Yii::app()->session['user']['name'];
	    	$url = 'news/index/type/'.$target["type"].'/id/'.$targetId;
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
            "actor"=>array(
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
            "url"     => Yii::app()->createUrl('/'.Yii::app()->controller->module->id.'/person/dashboard/id/'.$followerId)
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
	public static function createdProject($authorType, $authorId, $projectId, $projectName, $codeInsee) 
	{
	    $asParam = array(
	    	"type" => "Creation of project", 
            "verb" => ActStr::VERB_CREATE,
            "actor"=>array(
            	"type" => $authorType,
            	"id"   => $authorId
            ),
            "object"=>array(
	            "type" => Project::COLLECTION,
	            "id"   => $projectId
            ),
            "codeInse" => $codeInsee
        );
	    $stream = ActStr::buildEntry($asParam);

	    //$actionMsg = ($actionType == ActStr::VERB_INVITE ) ? " invited you" : " is following you";
	    ActivityStream::addEntry($stream);
	    //TODO mail::following
	    //add a link to follow back easily
	}
	public static function createdNeed($targetType, $targetId, $objectId, $objectName, $authorId) 
	{
	    $asParam = array(
	    	"type" => "Creation of Need", 
            "verb" => ActStr::VERB_CREATE,
            "actor"=>array(
            	"type" => Person::COLLECTION,
            	"id"   => $authorId
            ),
            "object"=>array(
	            "type" => Need::COLLECTION,
	            "id"   => $objectId
            ),
            "target" => array(
	            "type" => $targetType,
	            "id" => $targetId
            )
        );
	    $stream = ActStr::buildEntry($asParam);

	    //$actionMsg = ($actionType == ActStr::VERB_INVITE ) ? " invited you" : " is following you";
	    ActivityStream::addEntry($stream);
	    //TODO mail::following
	    //add a link to follow back easily
	}
}