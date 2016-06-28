<?php 
class TranslateOpenAgenda {

	public static $dataBinding_event = array(
		"name" 				=> array(	"parentKey"=>"name"
										"valueOf" => "fr"),
		"description" 		=> array(	"parentKey"=>"description"
										"valueOf" => "fr"),
		"shortDescription" 	=> array(	"parentKey"=>"freeText"
										"valueOf" => "fr"),
		"address" 			=> array(	"object"=>"location.0.",
										"valueOf" => array(
											"@type" 			=> "PostalAddress", 
											"addressLocality"   => array("valueOf" => "city"),
											"postalCode" 		=> array("valueOf" => "postalCode"),
							 				"streetAddress" 	=> array("valueOf" => "address"))),

		"geo" 				=> array(	"object"=>"location.0.",
										"valueOf" => array(
											"@type" 			=> "GeoCoordinates", 
											"latitude" 		=> array("valueOf" => "latitude"),
							 				"longitude" 	=> array("valueOf" => "longitude"))),
		















		//-----------------------------------------------------------------
	    /*"address" 	=> array("parentKey"=>"address", 
	    					 "valueOf" => array(
								"@type" 			=> "PostalAddress", 
								"@id" 				=> array("valueOf"  	=> 'codeInsee', 
															 "type" 	=> "url", 
															 "prefix"   => "/data/get/type/city/insee/",
															 "suffix"   => "/format/schema" ),
								"addressLocality"   => array("valueOf" => "addressLocality"),
								"addressRegion" 	=> array("valueOf" => "region"),
								"postalCode" 		=> array("valueOf" => "postalCode"),
				 				"streetAddress" 	=> array("valueOf" => "streetAddress")) ),
	    "email"		=> array("valueOf" => "email"),
		"image"		=> array("valueOf" => "img","type" 	=> "url", 
							 "prefix"   => "/communecter/"),
		"telephone"	=> array("valueOf" => "phoneNumber"),
		"url"		=> array("valueOf" => "url"),
		"startDate" => array("valueOf" => "startDate"),
		"endDate" 	=> array("valueOf" => "endDate"),
		"eventStatus" => array("valueOf" => "eventStatus"),
		"organizers" => array ( 
						"object" => "links.organizer",
						"collection" => "organizations" , 
						"valueOf" => array (
					   		"@type" => "Organization",
					   		"@id" => array (
					   			"valueOf"   => '_id.$id',
					   			"type" 		=> "url", 
								"prefix"    => "/data/get/type/organizations/id/",
								"suffix"    => "/format/schema"),
					   		"name" => array("valueOf" => "name")) 
					   	),
		"attendees" => array ( 
						"object" => "links.attendees",
						"collection" => "citoyens" , 
						"valueOf" => array (
					   		"@type" => "Person",
					   		"@id" => array(
					   			"valueOf"   => '_id.$id',
					   			"type" 		=> "url", 
								"prefix"    => "/data/get/type/citoyens/id/",
								"suffix"    => "/format/schema"),
					   		"name" => array("valueOf" => "name"))
					   	 ),*/
	);

}