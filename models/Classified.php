<?php

class Classified {
	const COLLECTION = "classified";
	
	//TODO Translate
	public static $classifiedTypes = array(
	    "health" => "Santé",
	    "work" => "Travail",
	    "retail" => "Immobilier"
    );
	public static $classifiedSubTypes = array(
	    "health" => array(
	        "subType" => array("remplacement", "emploi", "cession", "stagiaire", "assistanat", "collaborateur", "mission humanitaire")
	        ),
	    "work" => array(
	        "subType" => array("cherche travail", "proposition de travail", "bénévolat", "stagiaire")
	        ),
	    "retail" => array(
	        "subType" => array("cherche collocation", "cherche location", "offre de vends", "cherche achat", "offre de location")
	        ),
    );

	//From Post/Form name to database field name
	public static $dataBinding = array (
	    "type" => array("name" => "type"),
	    "subtype" => array("name" => "subtype"),
	    "name" => array("name" => "name", "rules" => array("required")),
	    "address" => array("name" => "address", "rules" => array("addressValid")),
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

	

	/**
	 * get a Poi By Id
	 * @param String $id : is the mongoId of the poi
	 * @return poi
	 */
	public static function getById($id) { 
	  	$poi = PHDB::findOneById( self::COLLECTION ,$id );
	  	return $poi;
	}
}
?>