<?php

class Booking {
	const COLLECTION = "bookings";
	const CONTROLLER = "booking";
	
	//TODO Translate
	public static $bookingTypes = array(
	   
    );

	//From Post/Form name to database field name
	public static $dataBinding = array (
	    "section" => array("name" => "section"),
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
	    "price" => array("name" => "price"),
	    "devise" => array("name" => "devise"),
	    "contactInfo" => array("name" => "contactInfo", "rules" => array("required")),
	    "toBeValidated"=>array("name" => "toBeValidated"),
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
	  	$product = PHDB::findOneById( self::COLLECTION ,$id );
	  	return $product;
	}

	public static function getListBy($where){
		$products = PHDB::find( self::COLLECTION , $where );
	  	return $products;
	}
	public static function getBookingByUser($where){
		$allBookings = PHDB::findAndSort( self::COLLECTION , $where, array("created"=>-1));
		foreach ($allBookings as $key => $value) {
			$itemBook=Product::getById($value["id"]);
			//$allBookings[$key] = array_merge($allBookings[$key], Document::retrieveAllImagesUrl($value["id"], $value["type"]));
			$allBookings[$key]["name"] = $itemBook["name"];
			$allBookings[$key]["description"] = $itemBook["description"];
			$allBookings[$key]["profilImageUrl"] = @$itemBook["profilImageUrl"];
			$allBookings[$key]["profilThumbImageUrl"] = @$itemBook["profilThumbImageUrl"];
			$allBookings[$key]["profilMediumImageUrl"] = @$itemBook["profilMediumImageUrl"];
		}
	  	return $allBookings;
	}
}
?>