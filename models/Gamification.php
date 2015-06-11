<?php
/*
This Class defines all rules and values applied across the plateforme 
Points a distributed to users and organizations for any activity inside the system
Things that count and give points : 
linking to someone : give both users 
creating : events, organizations, projects
answering surveys
asking questions
...
*/
class Gamification {

	const COLLECTION = "gamification";
	
	//platefrom actions
	const POINTS_USER_LOGIN = 5;
	const POINTS_USER_REGISTRATION = 15;
	const POINTS_SEARCH = 1;
	const POINTS_ADD_POST = 1;
	const POINTS_ANSWER_POST = 1;
	const POINTS_POST_LIKED = 2;

	//links
	const POINTS_LINK_USER_2_USER = 5;
	const POINTS_LINK_USER_2_ORGANIZATION = 5;
	const POINTS_LINK_USER_2_EVENT = 5;

	//creation
	const POINTS_CREATE_ORGANIZATION = 10;
	const POINTS_CREATE_EVENT = 10;
	const POINTS_CREATE_PROJECT = 10;

	//person activity 
	//points if profile is filled above 80%
	//presenting a MOAC

	//organization activity
	const POINTS_ORG_ADD_MEMBER = 5;
	//points if profile is filled above 80%	

	//event activity
	const POINTS_EVENT_INVITE_ATTENDEES = 5;	

	//project activity
	const POINTS_PROJECT_ADDING_BULLET_POINTS = 5;
	const POINTS_PROJECT_ADD_CONTRIBUTOR = 5;
	//points if profile is filled above 80%

	//city actions
	//a city feed url
	//a new place to discover
	//an object for sale 
	//a news entry
	//creating a city survey
	//answering a city survey 
	//doing a common task 
	//creating a common task
	//adding a new open data source or entry (interface add/propose an open data entry)

	/**
	 * adds an entry into the cron collection
	 * @param $params : a set of information for a proper cron entry
	*/
	public static function update($params){
	    $new = array(
			"id" => $params['id'],
	  		"person" => $params['type'],
	  		"folder" => $params['folder'],
	  		"moduleId" => $params['moduleId'],
	  		"doctype" => Document::getDoctype($params['name']),	
	  		"author" => $params['author'],
	  		"name" => $params['name'],
	  		"size" => $params['size'],
	  		'created' => time()
	    );

	    PHDB::insert(self::COLLECTION,$new);
	}
}
?>