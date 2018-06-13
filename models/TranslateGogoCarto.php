<?php 
class TranslateGogoCarto {
/*

	----------------- COMMUNECTER ----------------- 
*/
	

	public static $dataBinding_allOrganization  = array(
		"@type"		=> "Organization",
	    "name" 		=> array("valueOf" => "name"),
	    "typeCommunecter" 		=> array("valueOf" => "type"),
	    "image"		=> array("valueOf" => "image",
							 "type" 	=> "url"),
	    "typeSig"		=> Organization::COLLECTION,
	    "typeElement"	=> Organization::CONTROLLER,
	    "url" 		=> array("valueOf" => array(
								"communecter" 	=> array(	"valueOf" => '_id.$id',
										   						"type" 	=> "url", 
																"prefix"   => "/#organization.detail.id.",
																"suffix"   => ""),
							    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																"type" 	=> "url", 
																"prefix"   => "/api/organization/get/id/",
																"suffix"   => "" ),
	    						"website" 		=> array(	"valueOf" => 'url'))),
	);

	public static $dataBinding_organization = array(
		"id" 				=> array("valueOf"  	=> '_id.$id'),
	    "name" 		=> array("valueOf" => "name"),
	    "geo" 	=> array("parentKey"=>"geoPosition", 
    					 "valueOf" => array(
								"latitude" 			=> array("valueOf" => "coordinates.1"),
								"longitude" 			=> array("valueOf" => "coordinates.0")
			 					)),
		"address" 	=> array("parentKey"=>"address", 
	    					 "valueOf" => array(
									"streetAddress" 	=> array("valueOf" => "streetAddress"),
									"postalCode" 		=> array("valueOf" => "postalCode"),
									"addressLocality"   => array("valueOf" => "addressLocality"),
									"addressCountry" 	=> array("valueOf" => "addressCountry"))),
	   	"description"		=> array("valueOf" => "shortDescription"),
	   	"descriptionMore"		=> array("valueOf" => "description"),
	   	"website" 		=> array("valueOf" => 'url'),
	   	//"email"		=> array("valueOf" => "email"),
	   	"openHours" => null,
		"openHoursMoreInfos"=> "",
		"sourceKey" => "Communecter",
		"optionValues" => array(10507,10513,10512),
		"image"		=> array("valueOf" => "image",
							 "type" 	=> "url"),
	);

	public static $dataBinding_organization_symply = array(
		array("valueOf"  	=> '_id.$id'),
		array("valueOf" => "name"),
		array("valueOf" => "geoPosition.coordinates.1"),
	    array("valueOf" => "geoPosition.coordinates.0"),
	   	array(10507,10513,10512),
	);

	public static $dataBinding_network = array(
		
		"@type"		=> "Organization",
		"typeSig"=>	"organizations",
		"typeElement"=>	"organization",
		"typeOrganization"=>	"NGO",
		"type"=>	"NGO",

	    "name" 		=> array("valueOf" => "name"),

	    "geo" 	=> array("parentKey"=>"geo", 
    					 "valueOf" => array(
								"latitude" 			=> array("valueOf" => "latitude"),
								"longitude" 			=> array("valueOf" => "longitude")
			 					)),

	    "geoPosition" 	=> array("parentKey"=>"geo", 
    					 "valueOf" => array(
								"latitude" 			=> array("valueOf" => "latitude"),
								"longitude" 			=> array("valueOf" => "longitude")
			 					)),

		"address" 	=> array("parentKey"=>"address", 
	    					 "valueOf" => array(
									"streetAddress" 	=> array("valueOf" => "streetAddress"),
									"postalCode" 		=> array("valueOf" => "postalCode"),
									"addressLocality"   => array("valueOf" => "addressLocality"),
									"addressCountry" 	=> array("valueOf" => "addressCountry"))),
	   	"shortDescription"		=> array("valueOf" => "description"),
	   	"description"		=> array("valueOf" => "descriptionMore"),
	   	"url" 		=> array("valueOf" => 'website'),
	   	"source" 		=> array(	"valueOf" => 'id',
			   						"type" 	=> "url", 
									"prefix"   => "http://presdecheznous.fr/annuaire#/fiche/pdcn/",
									"suffix"   => ""),
	   	//"email"		=> array("valueOf" => "email"),
	);
}
