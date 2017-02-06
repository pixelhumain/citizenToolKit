<?php

class Url {
	const COLLECTION = "url";
	
	//TODO Translate
	/*public static $types = array (
		
	);*/

	//From Post/Form name to database field name
	public static $dataBinding = array (
	    "address" => array("name" => "address", "rules" => array("addressValid")),
	    "geo" => array("name" => "geo"),
	    "geoPosition" => array("name" => "geoPosition"),
	    "description" => array("name" => "description"),
	    "tags" => array("name" => "tags"),
	    "status" => array("name" => "status"),
	    "categories" => array("name" => "categories"),
		"keywords" => array("name" => "keywords"),
		"title" => array("name" => "title"),
		"favicon" => array("name" => "favicon"),
		"hostname" => array("name" => "hostname"),
		"nbClick" => array("name" => "hostname"),

		"url" => array("name" => "url", "rules" => array("notexist")),

	    "modified" => array("name" => "modified"),
	    "updated" => array("name" => "updated"),
	    "creator" => array("name" => "creator"),
	    "created" => array("name" => "created"),
	    );
	
}
?>