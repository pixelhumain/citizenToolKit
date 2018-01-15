<?php

class Ressource {
	const COLLECTION = "ressources";
	const CONTROLLER = "ressource";
	const TYPE_NEED = "needs";
	const TYPE_OFFER = "offers";
	//TODO Translate
	public static $category = array (
		//"need"			=> "Besoin",
		//"offer"			=> "Offre",
		"service"		=> "Service",
		"knowledge"		=> "knowledge",
		"material"		=> "Material",
		//"link" 			=> "Lien, Url",
		//"tool"			=> "Outil",
		//"machine"		=> "Machine",
		//"software"		=> "Software",
		//"rh"			=> "Ressource Humaine",
		//"RessourceMaterielle" => "Ressource Materielle",
		//"RessourceFinanciere" => "Ressource Financiere",
		//"ficheBlanche" => "Fiche Blanche",
		//"geoJson" 		=> "Url au format geojson ou vers une umap",
		//"video" 		=> "video"
	);
	public static $subCategory = array(

	);

	//From Post/Form name to database field name
	public static $dataBinding = array (
	    "section" => array("name" => "section"),
	    "type" => array("name" => "type"),
	    "category" => array("name" => "category"),
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
	 * get all Ressource details of an element
	 * @param type $id : is the mongoId (String) of the parent
	 * @param type $type : is the type of the parent
	 * @return list of Ressources
	 */
	public static function getByIdAndTypeOfParent($id, $type){
		$elems = PHDB::find(self::COLLECTION,array("parentId"=>$id,"parentType"=>$type));
	   	return $elems;
	}
	/**
	 * get Ressource with limit $limMin and $limMax
	 * @return list of Ressources
	 */
	public static function getByTagsAndLimit($limitMin=0, $indexStep=15, $searchByTags=""){
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
		
		$elems = PHDB::findAndSort( self::COLLECTION, $where, array("updated" => -1));
	   	return $elems;
	}

	/**
	 * get a Ressource By Id
	 * @param String $id : is the mongoId of the Ressource
	 * @return Ressource
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