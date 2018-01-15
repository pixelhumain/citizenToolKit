<?php

class Place {
	const COLLECTION = "places";
	const CONTROLLER = "place";
	
	//TODO Translate
	public static $types = array (
		"tierslieux" 	=> "Tiers Lieux",
		"fabLab" => "Fab Lab",
		"restaurant" => "Restaurant",
		"epicerieCollborative" => "Épicerie Collborative",
	);

	//From Post/Form name to database field name
	public static $dataBinding = array (
		"section" => array("name" => "section"),
	    "type" => array("name" => "type"),
	    "subtype" => array("name" => "placeType"),
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
	    "parent" => array("name" => "parent"),
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
	 * get all Place details of an element
	 * @param type $id : is the mongoId (String) of the parent
	 * @param type $type : is the type of the parent
	 * @return list of Places
	 */
	public static function getPlaceByIdAndTypeOfParent($id, $type){
		return PHDB::find(self::COLLECTION,array("parentId"=>$id,"parentType"=>$type));
	}
	/**
	 * get Place with limit $limMin and $limMax
	 * @return list of Places
	 */
	public static function getPlaceByTagsAndLimit($limitMin=0, $indexStep=15, $searchByTags=""){
		$where = array("name"=>array('$exists'=>1));
		if(@$searchByTags && !empty($searchByTags)){
			$queryTag = array();
			foreach ($searchByTags as $key => $tag) {
				if($tag != "")
					$queryTag[] = new MongoRegex("/".$tag."/i");
			}
			if(!empty($queryTag))
				$where["tags"] = array('$in' => $queryTag); 			
		}
		
		return PHDB::findAndSort( self::COLLECTION, $where, array("updated" => -1));
	}

	/**
	 * get a Place By Id
	 * @param String $id : is the mongoId of the Place
	 * @return Place
	 */
	public static function getById($id) { 
	  	$elem = PHDB::findOneById( self::COLLECTION ,$id );
	  	// Use case notragora
	  	if(@$elem["type"])
		  	$elem["typeSig"] = self::COLLECTION.".".$elem["type"];
	  	else
		  	$elem["typeSig"] = self::COLLECTION;
		if(@$elem["type"])
	  		$elem = array_merge($elem, Document::retrieveAllImagesUrl($id, self::COLLECTION, $elem["type"], $elem));

	  	return $elem;
	}

	public static function getDataBinding() {
	  	return self::$dataBinding;
	}
}
?>