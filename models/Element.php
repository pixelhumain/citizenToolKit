<?php 
class Element {

	public static $controler2Collection = array( 
		Person::CONTROLLER => Person::COLLECTION ,
		Project::CONTROLLER => Project::COLLECTION ,
		Organization::CONTROLLER => Organization::COLLECTION ,
	);
	public static $collection2Controller = array( 
		Person::COLLECTION => Person::CONTROLLER,
		Project::COLLECTION => Project::CONTROLLER,
		Organization::COLLECTION => Organization::CONTROLLER
	);
	public static function getControlerByCollection ($type) { 

		if($type == Person::COLLECTION)
	        $type = Person::CONTROLLER;
	    else if($type == Organization::COLLECTION)
	        $type = Organization::CONTROLLER;
	    else if($type == Project::COLLECTION)
	        $type = Project::CONTROLLER;
	    else if($type == Event::COLLECTION)
	        $type = Event::CONTROLLER;
	    
    	return $type;
    }
}