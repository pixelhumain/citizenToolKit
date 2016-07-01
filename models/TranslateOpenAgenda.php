<?php 
class TranslateOpenAgenda {

	public static $dataBinding_event = array(
		"name" 				=> array(	"valueOf" => "title.fr"),
		"type" 				=> "other",
		"description" 		=> array(	"valueOf"=>"description.fr"),
		"shortDescription" 	=> array(	"valueOf"=>"freeText.fr"),
		"image" 			=> array(	"valueOf" => "image"),
		"tags" 				=> array(	"valueOf" => "tags.fr"),
		"allDay" 			=> "false",
		"public" 			=> true,
		"organizerType" 	=> Event::NO_ORGANISER,
		"source" 			=> array(	"valueOf" => array(
											"id" 	=> array("valueOf" => "uid"), 
											"url" 	=> array("valueOf" => "link"),
							 				"key" 	=> "openagenda")),
		"dates" 			=> array(	"valueOf" => "locations.0.dates"),
		"startDate" 		=> array(	"function" => array(
											"model" 		=> "Event",
											"name" 		=> "getStartDateByListDate",
											"params"	=> array(
												"dates" => array("valueOf" => "locations.0.dates")))),
		"endDate" 			=> array(	"function" => array(
											"model" 		=> "Event",
											"name" 		=> "getEndDateByListDate",
											"params"	=> array(
												"dates" => array("valueOf" => "locations.0.dates")))),

		"address" 			=> array(	"function" => array(
											"model" 		=> "Import",
											"name" 		=> "getAndCheckAddressForEntity",
											"params"	=> array(
												"address" 	=> array(
													"valueOf" => array(
														"@type" 			=> "PostalAddress", 
														"addressLocality"   => array("valueOf" => "locations.0.city"),
														"postalCode" 		=> array("valueOf" => "locations.0.postalCode"),
							 							"streetAddress" 	=> array("valueOf" => "locations.0.address"))),
												"geo" 		=> array(	
													"valueOf" => array(
														"@type" 		=> "GeoCoordinates", 
														"latitude" 		=> array("valueOf" => "locations.0.latitude"),
										 				"longitude" 	=> array("valueOf" => "locations.0.longitude")))),
											"result"	=> "address")),

		"geo" 			=> array(	"function" => array(
											"model" 		=> "Import",
											"name" 		=> "getAndCheckAddressForEntity",
											"params"	=> array(
												"address" 	=> array(
													"valueOf" => array(
														"@type" 			=> "PostalAddress", 
														"addressLocality"   => array("valueOf" => "locations.0.city"),
														"postalCode" 		=> array("valueOf" => "locations.0.postalCode"),
							 							"streetAddress" 	=> array("valueOf" => "locations.0.address"))),
												"geo" 		=> array(	
													"valueOf" => array(
														"@type" 		=> "GeoCoordinates", 
														"latitude" 		=> array("valueOf" => "locations.0.latitude"),
										 				"longitude" 	=> array("valueOf" => "locations.0.longitude")))),
											"result"	=> "geo")),
		"geoPosition" 			=> array(	"function" => array(
											"model" 		=> "Import",
											"name" 		=> "getAndCheckAddressForEntity",
											"params"	=> array(
												"address" 	=> array(
													"valueOf" => array(
														"@type" 			=> "PostalAddress", 
														"addressLocality"   => array("valueOf" => "locations.0.city"),
														"postalCode" 		=> array("valueOf" => "locations.0.postalCode"),
							 							"streetAddress" 	=> array("valueOf" => "locations.0.address"))),
												"geo" 		=> array(	
													"valueOf" => array(
														"@type" 		=> "GeoCoordinates", 
														"latitude" 		=> array("valueOf" => "locations.0.latitude"),
										 				"longitude" 	=> array("valueOf" => "locations.0.longitude")))),
											"result"	=> "geoPosition")),
		
	);
}