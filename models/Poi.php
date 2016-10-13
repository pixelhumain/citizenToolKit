<?php

class Poi {
	const COLLECTION = "poi";
	
	//TODO Translate
	public static $types = array (
		"compostPickup" => "récolte de composte",
		"video" => "video",
		"sharedLibrary" => "bibliothèque partagée",
		"artPiece" => "oeuvres",
		"recoveryCenter" => "ressourcerie",
		"trash" => "poubelle",
		"history" => "histoire",
		"something2See" => "chose a voir",
		"funPlace" => "endroit Sympas (skatepark, vue...)",
		"place" => "place publique",
		"streetArts" => "arts de rue",
		"openScene" => "scène ouverte",
		"stand" => "stand"
	);

	//From Post/Form name to database field name
	public static $dataBinding = array (
	    "type" => array("name" => "type"),
	    "name" => array("name" => "name", "rules" => array("required")),
	    "address" => array("name" => "address"),
	    "addresses" => array("name" => "addresses"),
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
	    "urls" => array("name" => "urls"),
	    "medias" => array("name" => "medias"),
	    "tags" => array("name" => "tags"),

	    "modified" => array("name" => "modified"),
	    "updated" => array("name" => "updated"),
	    "creator" => array("name" => "creator"),
	    "created" => array("name" => "created"),
	    );
}
?>