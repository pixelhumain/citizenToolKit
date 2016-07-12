<?php 
class TranslateCommunecter {
/*

	----------------- COMMUNECTER ----------------- 
*/
	public static $dataBinding_person = array(
		"@type"		=> "Person",
	    "name" 		=> array("valueOf" => "name"),
	    "image"		=> array("valueOf" => "image",
							 "type" 	=> "url"),
	  
	    "url" 	=> array("valueOf" => array(
	    					"website" 		=> array(	"valueOf" => 'url'),
							"communecter" 	=> array(	"valueOf" => '_id.$id',
									   						"type" 	=> "url", 
															"prefix"   => "/#person.detail.id.",
															"suffix"   => ""),
						    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
															"type" 	=> "url", 
															"prefix"   => "/communecter/data/get/type/citoyens/id/",
															"suffix"   => "" ),
						    /*"osm" 			=> array(	"valueOf"  	=> 'geo', 
															"type" 	=> "urlOsm", 
															"prefix"   => "http://www.openstreetmap.org/#map=16/",
															"suffix"   => "" ),*/
						    /*"city" 			=> array(	"valueOf"  	=> 'address.codeInsee', 
															"type" 	=> "url", 
															"prefix"   => "/communecter/data/get/type/cities/insee/",
															"suffix"   => "" )*/
						    
				 		)),
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
	   	"phone" 	=> array("parentKey"=>"telephone", 
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
										"collection" => Organization::COLLECTION , 
										"valueOf" => array (
									   		"type" => "Organization",
									   		"name" => array("valueOf" => "name"),
									   		/*"url" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/#organization.detail.id.",
												"suffix"   => ""),
									   		"urlApi" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/communecter/data/get/type/organizations/id/",
												"suffix"   => "")*/
									   		"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#organization.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/communecter/data/get/type/organizations/id/",
																							"suffix"   => "" ),
												 		))
									   	)),
									"projects" => array( 
										"object" => "projects",
										"collection" => Project::COLLECTION, 
										"valueOf" => array (
									   		"type" => "Project",
									   		"name" => array("valueOf" => "name"),
									   		/*"url" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/#project.detail.id.",
												"suffix"   => ""),
											"urlApi" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url",
									   			"prefix"   => "/communecter/data/get/type/projects/id/",
												"suffix"   => ""))),*/
											"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#project.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/communecter/data/get/type/projects/id/",
																							"suffix"   => "" ),
												 		))
											)),
									"events" => array( 
										"object" => "events",
										"collection" => Event::COLLECTION , 
										"valueOf" => array (
									   		"type" => "Event",
									   		"name" => array("valueOf" => "name"),
									   		/*"url" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/#event.detail.id.",
												"suffix"   => ""),
									   		"urlApi" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/communecter/data/get/type/events/id/",
												"suffix"   => "")))*/
											"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#event.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/communecter/data/get/type/events/id/",
																							"suffix"   => "" ),
												 		))
				 					)),
								))
	);

	public static $dataBinding_organization = array(
		"@type"		=> "Organization",
		
	    "name" 		=> array("valueOf" => "name"),
	    "typeCommunecter" 		=> array("valueOf" => "type"),
	    "image"		=> array("valueOf" => "image",
							 "type" 	=> "url"),
	    "url" 		=> array("valueOf" => array(
		    					"website" 		=> array(	"valueOf" => 'url'),
								"communecter" 	=> array(	"valueOf" => '_id.$id',
										   						"type" 	=> "url", 
																"prefix"   => "/#organization.detail.id.",
																"suffix"   => ""),
							    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																"type" 	=> "url", 
																"prefix"   => "/communecter/data/get/type/organizations/id/",
																"suffix"   => "" ),
							    )),
							    /*"osm" 			=> array(	"valueOf"  	=> 'geo', 
																"type" 	=> "urlOsm", 
																"prefix"   => "http://www.openstreetmap.org/#map=16/",
																"suffix"   => "" ),*/
							    /*"city" 			=> array(	"valueOf"  	=> 'address.codeInsee', 
																"type" 	=> "url", 
																"prefix"   => "/communecter/data/get/type/cities/insee/",
																"suffix"   => "" )*/
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
	   	"phone" 	=> array("parentKey"=>"telephone", 
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
									"followers" => array( 
										"object" => "followers",
										"collection" => Person::COLLECTION , 
										"valueOf" => array (
									   		"@type"		=> "Person",
									   		"name" => array("valueOf" => "name"),
									   		/*"url" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/#citoyens.detail.id.",
												"suffix"   => ""),
									   		"urlApi" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/communecter/data/get/type/citoyens/id/",
												"suffix"   => "")*/
									   		"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#person.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/communecter/data/get/type/citoyens/id/",
																							"suffix"   => "" ),
												 		))
											)),
									"members" => array( 
										"object" => "members",
										"collection" => Person::COLLECTION , 
										"valueOf" => array (
									   		"type" => "Person",
									   		"name" => array("valueOf" => "name"),
									   		/*"url" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/#person.detail.id.",
												"suffix"   => ""),
									   		"urlApi" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/communecter/data/get/type/citoyens/id/",
												"suffix"   => "")*/
									   		"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#person.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/communecter/data/get/type/citoyens/id/",
																							"suffix"   => "" ),
												 		))
									   	) ),
									"projects" => array( 
										"object" => "projects",
										"collection" => Project::COLLECTION , 
										"valueOf" => array (
									   		"type" => "Project",
									   		"name" => array("valueOf" => "name"),
									   		/*"url" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/#project.detail.id.",
												"suffix"   => ""),
											"urlApi" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url",
									   			"prefix"   => "/communecter/data/get/type/projects/id/",
												"suffix"   => "")*/
											"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#project.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/communecter/data/get/type/projects/id/",
																							"suffix"   => "" ),
												 		))
											)),
									"events" => array( 
										"object" => "events",
										"collection" => Event::COLLECTION  , 
										"valueOf" => array (
									   		"type" => "Event",
									   		"name" => array("valueOf" => "name"),
									   		/*"url" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/#event.detail.id.",
												"suffix"   => ""),
									   		"urlApi" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/communecter/data/get/type/events/id/",
												"suffix"   => "")*/
									   		"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#event.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/communecter/data/get/type/events/id/",
																							"suffix"   => "" ),
												 		))

									   		)),
									"needs" => array( 
										"object" => "needs",
										"collection" => Need::COLLECTION  , 
										"valueOf" => array (
									   		"type" => "Need",
									   		"name" => array("valueOf" => "name"),
									   		/*"url" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/#need.detail.id.",
												"suffix"   => ""),
									   		"urlApi" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/communecter/data/get/type/needs/id/",
												"suffix"   => "")*/
									   		"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#need.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/communecter/data/get/type/needs/id/",
																							"suffix"   => "" ),
												 		))
									   		))

				 					)),
	);


	public static $dataBinding_event = array(
		"@type"		=> "Event",
		
	    "name" 		=> array("valueOf" => "name"),
	    "typeCommunecter" 		=> array("valueOf" => "type"),
	    "image"		=> array("valueOf" => "image",
							 "type" 	=> "url"),
	    "url" 		=> array("valueOf" => array(
		    					"website" 		=> array(	"valueOf" => 'url'),
								"communecter" 	=> array(	"valueOf" => '_id.$id',
										   						"type" 	=> "url", 
																"prefix"   => "/#event.detail.id.",
																"suffix"   => ""),
							    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																"type" 	=> "url", 
																"prefix"   => "/communecter/data/get/type/events/id/",
																"suffix"   => "" ))),
							    /*"osm" 			=> array(	"valueOf"  	=> 'geo', 
																"type" 	=> "urlOsm", 
																"prefix"   => "http://www.openstreetmap.org/#map=16/",
																"suffix"   => "" ),*/
							    /*"city" 			=> array(	"valueOf"  	=> 'address.codeInsee', 
																"type" 	=> "url", 
																"prefix"   => "/communecter/data/get/type/cities/insee/",
																"suffix"   => "" )*/
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
	   	"allDay"		=> array("valueOf" => "allDay"),
	   	"startDate"		=> array("valueOf" => "startDate"),
	   	"endDate"		=> array("valueOf" => "endDate"),
	   	"email"		=> array("valueOf" => "email"),
	   	"phone" 	=> array("parentKey"=>"telephone", 
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
									"attendees" => array( 
										"object" => "attendees",
										"collection" => Person::COLLECTION , 
										"valueOf" => array (
									   		"@type"		=> "Person",
									   		"name" => array("valueOf" => "name"),
									   		/*"url" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/#citoyens.detail.id.",
												"suffix"   => ""),
									   		"urlApi" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/communecter/data/get/type/citoyens/id/",
												"suffix"   => "")*/
									   		"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#person.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/communecter/data/get/type/citoyens/id/",
																							"suffix"   => "" ),
												 		))
									   	) ),
									"needs" => array( 
										"object" => "needs",
										"collection" => Event::COLLECTION  , 
										"valueOf" => array (
									   		"type" => "Need",
									   		"name" => array("valueOf" => "name"),
									   		/*"url" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/#need.detail.id.",
												"suffix"   => ""),
									   		"urlApi" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/communecter/data/get/type/needs/id/",
												"suffix"   => "")*/
									   		"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#need.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/communecter/data/get/type/needs/id/",
																							"suffix"   => "" ),

									   		))
				 					))
								))
	);

	public static $dataBinding_project = array(
		"@type"		=> "Project",
		
	    "name" 		=> array("valueOf" => "name"),
	    "typeCommunecter" 		=> array("valueOf" => "type"),
	    "image"		=> array("valueOf" => "image",
							 "type" 	=> "url"),
	    "url" 		=> array("valueOf" => array(
		    					"website" 		=> array(	"valueOf" => 'url'),
								"communecter" 	=> array(	"valueOf" => '_id.$id',
										   						"type" 	=> "url", 
																"prefix"   => "/#project.detail.id.",
																"suffix"   => ""),
							    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																"type" 	=> "url", 
																"prefix"   => "/communecter/data/get/type/projects/id/",
																"suffix"   => "" ))),
							    /*"osm" 			=> array(	"valueOf"  	=> 'geo', 
																"type" 	=> "urlOsm", 
																"prefix"   => "http://www.openstreetmap.org/#map=16/",
																"suffix"   => "" ),*/
							    /*"city" 			=> array(	"valueOf"  	=> 'address.codeInsee', 
																"type" 	=> "url", 
																"prefix"   => "/communecter/data/get/type/cities/insee/",
																"suffix"   => "" )*/
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
	   	"allDay"		=> array("valueOf" => "allDay"),
	   	"startDate"		=> array("valueOf" => "startDate"),
	   	"endDate"		=> array("valueOf" => "endDate"),
	   	"email"		=> array("valueOf" => "email"),
	   	"phone" 	=> array("parentKey"=>"telephone", 
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
									"contributors" => array( 
										"object" => "contributors",
										"collection" => Person::COLLECTION , 
										"valueOf" => array (
									   		"@type"		=> "Person",
									   		"name" => array("valueOf" => "name"),
									   		/*"url" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/#citoyens.detail.id.",
												"suffix"   => ""),
									   		"urlApi" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/communecter/data/get/type/citoyens/id/",
												"suffix"   => "")*/
									   		"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#person.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/communecter/data/get/type/citoyens/id/",
																							"suffix"   => "" ),
												 		))
									   	) )))
	);


	public static $dataBinding_need = array(
		"@type"		=> "Project",
		
	    "name" 		=> array("valueOf" => "name"),
	    "typeCommunecter" 		=> array("valueOf" => "type"),
	    "duration"		=> array("valueOf" => "duration"),
	    "quantity"		=> array("valueOf" => "quantity"),
	    "benefits"		=> array("valueOf" => "benefits"),
	    "url" 		=> array("valueOf" => array(
		    					"website" 		=> array(	"valueOf" => 'url'),
								"communecter" 	=> array(	"valueOf" => '_id.$id',
										   						"type" 	=> "url", 
																"prefix"   => "/#need.detail.id.",
																"suffix"   => ""),
							    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																"type" 	=> "url", 
																"prefix"   => "/communecter/data/get/type/needs/id/",
																"suffix"   => "" ))),
	   	/*"parent"		=> array(	"object" => "parentId",
								 	"collection" => array("valueOf" => "parentType")  , 
									"valueOf" => array(
								   		"type" => array("valueOf" => "parentType"),
								   		"name" => array("valueOf" => "name"),
								   		"url" => array(
								   			"valueOf" => '_id.$id',
								   			"type" 	=> "url", 
											"prefix"   => "/#need.detail.id.",
											"suffix"   => ""),
								   		"urlApi" => array(
								   			"valueOf" => '_id.$id',
								   			"type" 	=> "url", 
											"prefix"   => "/communecter/data/get/type/needs/id/",
											"suffix"   => "")))*/
		"description"	=> array("valueOf" => "description"),
	   	"allDay"		=> array("valueOf" => "allDay"),
	   	"startDate"		=> array("valueOf" => "startDate"),
	   	"endDate"		=> array("valueOf" => "endDate"),
	   	"email"		=> array("valueOf" => "email"),
	   	"phone" 	=> array("parentKey"=>"telephone", 
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
									"contributors" => array( 
										"object" => "contributors",
										"collection" => Person::COLLECTION , 
										"valueOf" => array (
									   		"@type"		=> "Person",
									   		"name" => array("valueOf" => "name"),
									   		/*"url" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/#citoyens.detail.id.",
												"suffix"   => ""),
									   		"urlApi" => array(
									   			"valueOf" => '_id.$id',
									   			"type" 	=> "url", 
												"prefix"   => "/communecter/data/get/type/citoyens/id/",
												"suffix"   => "")*/
									   		"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#person.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/communecter/data/get/type/citoyens/id/",
																							"suffix"   => "" ),
												 		))
									   	) )))
	);

	

	public static $dataBinding_city = array(
		"@type"		=> "City",
		"@id" 		=> array("valueOf"  => 'insee', 
							 "type" 	=> "url", 
							 "prefix"   => "/communecter/data/get/type/cities/insee/",
							 "suffix"   => "" ),
	    "name" 		=> array("valueOf" => "name"),
	    "alternateName" => array("valueOf" => "alternateName"),
	    "url" 	=> array("valueOf" => array(
	    					/*"communecter" 	=> array(	"valueOf" => '_id.$id',
									   						"type" 	=> "url", 
															"prefix"   => "/#person.detail.id.",
															"suffix"   => ""),*/
						    "apiCitoyens" 			=> array(	"valueOf"  	=> 'insee', 
															"type" 	=> "url", 
															"prefix"   => "/communecter/data/get/type/citoyens/insee/",
															"suffix"   => "" ),
						    "apiOrganizations" 			=> array(	"valueOf"  	=> 'insee', 
															"type" 	=> "url", 
															"prefix"   => "/communecter/data/get/type/organizations/insee/",
															"suffix"   => "" ),
						    "apiProjects" 			=> array(	"valueOf"  	=> 'insee', 
															"type" 	=> "url", 
															"prefix"   => "/communecter/data/get/type/projects/insee/",
															"suffix"   => "" ),
						    "apiEvents" 			=> array(	"valueOf"  	=> 'insee', 
															"type" 	=> "url", 
															"prefix"   => "/communecter/data/get/type/events/insee/",
															"suffix"   => "" ),
						    
				 		)),
		"postalCodes" 	=> array("valueOf"=>"postalCodes"),
	    /*"postalCodes" 	=> array("parentKey"=>"postalCodes", 
	    					 	"valueOf" => array(
									"postalCode" 	=> array("parentKey"=>"postalCode", "valueOf" => "postalCode"), 
									"name" 			=> array(	"valueOf" => "name"),
									"geo" 			=> array(	"parentKey"=>"geo", 
							    					 			"valueOf" => array(
																	"@type" 			=> "GeoCoordinates", 
																	"latitude" 			=> array("valueOf" => "latitude"),
																	"longitude" 		=> array("valueOf" => "longitude"))),
							   		"geoPosition" 	=> array(	"parentKey"=>"geoPosition", 
							    					 			"valueOf" => array(
																	"@type" 			=> "Point", 
																	"coordinates" 		=> array("valueOf" => "coordinates")
										 						)),
				 					)),*/
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
	   	"geoShape" 	=> array("valueOf"=>"geoShape"),
	    
	);

}