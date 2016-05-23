<?php 
class TranslateCommunecter {
/*

	----------------- PLP ----------------- 

	https://github.com/hackers4peace/plp-test-data/blob/master/graph.jsonld
	*/
	//http://127.0.0.1/ph/communecter/data/get/type/citoyens/id/520931e2f6b95c5cd3003d6c/format/plp
	public static $dataBinding_person = array(
	    "@context"  => "",
		"@type"		=> "Person",
		"id" 		=> array("valueOf"  	=> '_id.$id', 
							 "type" 	=> "url", 
							 "prefix"   => "/data/get/type/citoyens/id/",
							 "suffix"   => "/format/communecter" ),
	    "name" 		=> array("valueOf" => "name"),
	    /*"image"		=> array("valueOf" => "img",
							 "type" 	=> "url", 
							 "prefix"  => "/upload/communecter/citoyens/55e042ffe41d754428848363/zhq91z.jpg"),
	    "birthDate" => array("valueOf" => "bitrh"),*/
	    "address" 	=> array("parentKey"=>"address", 
	    					 "valueOf" => array(
									"@type" 			=> "PostalAddress", 
									"streetAddress" 	=> array("valueOf" => "streetAddress"),
									"postalCode" 		=> array("valueOf" => "postalCode"),
									"addressLocality"   => array("valueOf" => "addressLocality"),
									"codeInsee" 		=> array("valueOf" => "codeInsee"),
									"addressRegion" 	=> array("valueOf" => "addressRegion"),
									"addressCountry" 	=> array("valueOf" => "addressCountry")
				 					)),
	   	"geo" 	=> array("parentKey"=>"geo", 
	    					 "valueOf" => array(
									"@type" 			=> "GeoCoordinates", 
									"latitude" 			=> array("valueOf" => "latitude"),
									"longitude" 		=> array("valueOf" => "longitude")
				 					)),
	   	"geoPosition" 	=> array("parentKey"=>"geoPosition", 
	    					 "valueOf" => array(
									"@type" 			=> "Point", 
									"coordinates" 			=> array("valueOf" => "coordinates")
				 					)),
	   	"shortDescription"		=> array("valueOf" => "shortDescription"),
	   	"description"		=> array("valueOf" => "description"),
	   	"email"		=> array("valueOf" => "email"),
	   	"telephone" 	=> array("parentKey"=>"phone", 
	    					 "valueOf" => array(
									"fixe" 			=> array("parentKey"=>"fixe",
															 "valueOf" => "fixe"), 
									"mobile" 		=> array("parentKey"=>"mobile",
															 "valueOf" => "mobile"), 
									"fax" 			=> array("parentKey"=>"fax",
															 "valueOf" => "fax"), 
				 					)),

		"socialNetwork" 	=> array("parentKey"=>"socialNetwork", 
	    					 "valueOf" => array(
									"github" 		=> array("valueOf" => "github"),
									"twitter" 		=> array("valueOf" => "twitter"),
									"facebook" 		=> array("valueOf" => "facebook"),
									"googleplus" 	=> array("valueOf" => "googleplus"),
									"linkedin" 		=> array("valueOf" => "linkedin"),
									"skype" 		=> array("valueOf" => "skype")
				 					)),
		"tags"		=> array("valueOf" => "tags"),
		"links" 	=> array("parentKey"=>"links", 
	    					 "valueOf" => array(
									"memberOf" => array( 
										"object" => "memberOf",
										"collection" => "organizations" , 
										"valueOf" => array (
									   		"type" => "Organization",
									   		"name" => array("valueOf" => "name"),
									   		"url" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/#organization.detail.id.",
												"suffix"   => ""),
									   		"urlApi" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/data/get/type/organizations/id/",
												"suffix"   => "/format/communecter")
									   	) ),
									"projects" => array( 
										"object" => "projects",
										"collection" => "projects" , 
										"valueOf" => array (
									   		"type" => "Project",
									   		"name" => array("valueOf" => "name"),
									   		"url" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/#project.detail.id.",
												"suffix"   => ""),
											"urlApi" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url",
									   			"prefix"   => "/communecter/data/get/type/projects/id/",
												"suffix"   => "/format/communecter"))),
									"events" => array( 
										"object" => "events",
										"collection" => "events" , 
										"valueOf" => array (
									   		"type" => "Event",
									   		"name" => array("valueOf" => "name"),
									   		"url" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/#event.detail.id.",
												"suffix"   => ""),
									   		"urlApi" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/data/get/type/events/id/",
												"suffix"   => "/format/communecter")))
				 					)),
	);

	
}