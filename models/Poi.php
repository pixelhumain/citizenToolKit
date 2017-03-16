<?php

class Poi {
	const COLLECTION = "poi";
	const CONTROLLER = "poi";
	
	//TODO Translate
	public static $types = array (
		"link" 			=> "Lien, Url",
		"poi"			=>"points d'intérêt",
		"geoJson" 		=> "Url au format geojson ou vers une umap",
		"compostPickup" => "récolte de composte",
		"video" 		=> "video",
		"sharedLibrary" => "bibliothèque partagée",
		"artPiece" 		=> "oeuvres",
		"recoveryCenter"=> "ressourcerie",
		"trash" 		=> "poubelle",
		"history" 		=> "histoire",
		"something2See" => "chose a voir",
		"funPlace" 		=> "endroit Sympas (skatepark, vue...)",
		"place" 		=> "place publique",
		"streetArts" 	=> "arts de rue",
		"openScene" 	=> "scène ouverte",
		"stand" 		=> "stand",
		"parking" 		=> "Parking"
	);

	//From Post/Form name to database field name
	public static $dataBinding = array (
	    "type" => array("name" => "type"),
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
//From Post/Form name to database field name
	public static $collectionsList = array (
	   "Où sont les femmes",
		"Passeur d'images",
		"MHQM",
		"MIAA",
		"Portrait citoyens",
		"Parcours d'engagement"
	);
	public static $genresList=array(
		"Documentaire",
		"Fiction",
		"Docu-fiction",
		"Films outils",
		"Films de commande"
	);
	/**
	 * get all poi details of an element
	 * @param type $id : is the mongoId (String) of the parent
	 * @param type $type : is the type of the parent
	 * @return list of pois
	 */
	public static function getPoiByIdAndTypeOfParent($id, $type){
		$pois = PHDB::find(self::COLLECTION,array("parentId"=>$id,"parentType"=>$type));
	   	return $pois;
	}
	/**
	 * get poi with limit $limMin and $limMax
	 * @return list of pois
	 */
	public static function getPoiByTagsAndLimit($limitMin=0, $indexStep=15, $searchByTags=""){
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
		
		$pois = PHDB::findAndSort( self::COLLECTION, $where, array("updated" => -1));
	   	return $pois;
	}

	/**
	 * get a Poi By Id
	 * @param String $id : is the mongoId of the poi
	 * @return poi
	 */
	public static function getById($id) { 
	  	$poi = PHDB::findOneById( self::COLLECTION ,$id );
	  	// Use case notragora
	  	if(@$poi["type"])
		  	$poi["typeSig"] = self::COLLECTION.".".$poi["type"];
	  	else
		  	$poi["typeSig"] = self::COLLECTION;
		if(@$poi["type"])
	  		$poi = array_merge($poi, Document::retrieveAllImagesUrl($id, self::COLLECTION, $poi["type"], $poi));

	  	return $poi;
	}
}
?>