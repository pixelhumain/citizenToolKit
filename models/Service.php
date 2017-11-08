<?php

class Service {
	const COLLECTION = "services";
	const CONTROLLER = "service";
	
	//TODO Translate
	public static $productTypes = array(
	   
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
	    "shortDescription" => array("name" => "shortDescription"),
	    "descriptionHTML" => array("name" => "descriptionHTML"),
	    "addresses" => array("name" => "addresses"),
	    "parentId" => array("name" => "parentId"),
	    "parentType" => array("name" => "parentType"),
	    "media" => array("name" => "media"),
	    "urls" => array("name" => "urls"),
	    "medias" => array("name" => "medias"),
	    "tags" => array("name" => "tags"),
	    "price" => array("name" => "price"),
	    "devise" => array("name" => "devise"),
	    "capacity" => array("name" => "capacity"),
	    "openingHours" => array("name" => "openingHours"),
	    "contactInfo" => array("name" => "contactInfo", "rules" => array("required")),
	    "toBeValidated"=>array("name" => "toBeValidated"),
	    "modified" => array("name" => "modified"),
	    "updated" => array("name" => "updated"),
	    "creator" => array("name" => "creator"),
	    "created" => array("name" => "created"),
	);

	

    public static function getDataBinding() {
        return self::$dataBinding;
    }

	/**
	 * get a Service By Id
	 * @param String $id : is the mongoId of the poi
	 * @return poi
	 */
	public static function getById($id) { 
	  	$service = PHDB::findOneById( self::COLLECTION ,$id );
	  	return $service;
	}

	public static function getListBy($where){
		$services = PHDB::find( self::COLLECTION , $where );
	  	return $services;
	}
	public static function getServiceByCreator($id){
		$allServices = PHDB::findAndSort( self::COLLECTION , array("creator"=> $id), array("updated"=>-1));
		foreach ($allServices as $key => $value) {
			if(@$value["creator"]){// && @$value["parentType"])
				$parent = Element::getElementById(@$value["creator"], "citoyens");//@$value["parentType"]);
				$aParent = array("name"=>@$parent["name"],
								 "profilThumbImageUrl"=>@$parent["profilThumbImageUrl"],
								);
			}else{
				$aParent=array();
			}

			$allServices[$key]["parent"] = $aParent;
			$allServices[$key]["category"] = @$allServices[$key]["type"];
			$allServices[$key]["type"] = "classified";
			//if(@$value["type"])
			//	$allClassified[$key]["typeSig"] = Classified::COLLECTION.".".$value["type"];
			//else
			$allServices[$key]["typeSig"] = Classified::COLLECTION;
		}
	  	return $allServices;
	}
}
?>