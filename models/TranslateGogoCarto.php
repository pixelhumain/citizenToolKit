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
		"@type"				=> "Organization",
		"id" 				=> array("valueOf"  	=> '_id.$id'),
		"status" 			=> 1,
		"moderationState"	=> 0,
	    "name" 		=> array("valueOf" => "name"),

	    "type" 		=> array("valueOf" => "type"),
	    "typeSig"		=> Organization::COLLECTION,
	    "typeElement"	=> Organization::CONTROLLER,
	    "image"		=> array("valueOf" => "image",
							 "type" 	=> "url"),
	    
	    "source" 	=> array(	"valueOf"  	=> '_id.$id', 
								"type" 	=> "url", 
								"prefix"   => "#element.detail.type.organizations.id.",
								"suffix"   => "" ),
    	"api" 		=> array(	"valueOf"  	=> '_id.$id', 
								"type" 	=> "url", 
								"prefix"   => "/api/organization/get/id/",
								"suffix"   => "" ),
		"address" 	=> array("valueOf"=>"address"),
	   	"geo" 	=> array("parentKey"=>"geoPosition", 
    					 "valueOf" => array(
								"latitude" 			=> array("valueOf" => "coordinates.1"),
								"longitude" 			=> array("valueOf" => "coordinates.0")
			 					)),
	   	"commitment" => "",
	   	"description"		=> array("valueOf" => "shortDescription"),
	   	"descriptionMore"		=> array("valueOf" => "description"),
	   	"email"		=> array("valueOf" => "email"),
	   	"website" 		=> array("valueOf" => 'url'),
	   	"openHours" => null,
		"openHoursMoreInfos"=> "",
		"sourceKey" => "Communecter",
		"optionValues" => array("valueOf" => "optionValues"),
	);
}
