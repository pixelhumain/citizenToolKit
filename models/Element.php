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

}