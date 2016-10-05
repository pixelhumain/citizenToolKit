<?php

class Poi {
	const COLLECTION = "poi";

	//From Post/Form name to database field name
	public static $dataBinding = array (
	    "name" => array("name" => "name", "rules" => array("required")),
	    "address" => array("name" => "address"),
	    "streetAddress" => array("name" => "address.streetAddress"),
	    "postalCode" => array("name" => "address.postalCode"),
	    "city" => array("name" => "address.codeInsee"),
	    "addressLocality" => array("name" => "address.addressLocality"),
	    "addressCountry" => array("name" => "address.addressCountry"),
	    "geo" => array("name" => "geo"),
	    "geoPosition" => array("name" => "geoPosition"),
	    "description" => array("name" => "description"),
	    "addresses" => array("name" => "addresses"),
	    "parentId" => array("name" => "parentId"),
	    "parentType" => array("name" => "parentType"),
	     "media" => array("name" => "media"),
	    "creator" => array("name" => "creator"),
	    "created" => array("name" => "created"),
	    "addresses" => array("name" => "addresses"),
	    "urls" => array("name" => "urls"),
	    "tags" => array("name" => "tags"),
	    );
}
?>