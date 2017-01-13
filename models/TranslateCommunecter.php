<?php 
class TranslateCommunecter {
/*

	----------------- COMMUNECTER ----------------- 
*/
	

	public static $dataBinding_allPerson = array(
		"@type"		=> "Person",
	    "name" 		=> array("valueOf" => "name"),
	    "url" 	=> array("valueOf" => array(
	    					"website" 		=> array(	"valueOf" => 'url'),
							"communecter" 	=> array(	"valueOf" => '_id.$id',
									   						"type" 	=> "url", 
															"prefix"   => "/#person.detail.id.",
															"suffix"   => ""),
						    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
															"type" 	=> "url", 
															"prefix"   => "/api/person/get/id/",
															"suffix"   => "" )))
	);

	public static $dataBinding_time = array(
		"@type"		=> "Date",
		"date"		=> array("valueOf" => array(
	    					"year" 		=> array(	"valueOf" => 'year'),
	    					"mon" 		=> array(	"valueOf" => 'mon'),
	    					"mday" 		=> array(	"valueOf" => 'mday')
	    					)
		),
	    
	    "time" 	=> array("valueOf" => array(
	    					"hours" 		=> array(	"valueOf" => 'hours'),
	    					"minutes" 		=> array(	"valueOf" => 'minutes'),
	    					"secondes" 		=> array(	"valueOf" => 'secondes')
	    					)
	    )

	);

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
															"prefix"   => "/api/person/get/id/",
															"suffix"   => "" ),
						    /*"osm" 			=> array(	"valueOf"  	=> 'geo', 
															"type" 	=> "urlOsm", 
															"prefix"   => "http://www.openstreetmap.org/#map=16/",
															"suffix"   => "" ),*/
						    /*"city" 			=> array(	"valueOf"  	=> 'address.codeInsee', 
															"type" 	=> "url", 
															"prefix"   => "/api/data/get/type/cities/insee/",
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
									   		"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#organization.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/api/organization/get/id/",
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
									   			"prefix"   => "/api/data/get/type/projects/id/",
												"suffix"   => ""))),*/
											"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#project.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/api/project/get/id/",
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
												"prefix"   => "/api/data/get/type/events/id/",
												"suffix"   => "")))*/
											"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#event.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/api/event/get/id/",
																							"suffix"   => "" ),
												 		))
				 					)),
								))
	);

	public static $dataBinding_allOrganization  = array(
		"@type"		=> "Organization",
	    "name" 		=> array("valueOf" => "name"),
	    "typeCommunecter" 		=> array("valueOf" => "type"),
	    "image"		=> array("valueOf" => "image",
							 "type" 	=> "url"),
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
																"prefix"   => "/api/organization/get/id/",
																"suffix"   => "" ),
							    )),
							    /*"osm" 			=> array(	"valueOf"  	=> 'geo', 
																"type" 	=> "urlOsm", 
																"prefix"   => "http://www.openstreetmap.org/#map=16/",
																"suffix"   => "" ),*/
							    /*"city" 			=> array(	"valueOf"  	=> 'address.codeInsee', 
																"type" 	=> "url", 
																"prefix"   => "/api/data/get/type/cities/insee/",
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
												"prefix"   => "/api/data/get/type/citoyens/id/",
												"suffix"   => "")*/
									   		"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#person.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/api/person/get/id/",
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
												"prefix"   => "/api/data/get/type/citoyens/id/",
												"suffix"   => "")*/
									   		"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#person.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/api/person/get/id/",
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
									   			"prefix"   => "/api/data/get/type/projects/id/",
												"suffix"   => "")*/
											"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#project.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/api/project/get/id/",
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
												"prefix"   => "/api/data/get/type/events/id/",
												"suffix"   => "")*/
									   		"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#event.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/api/event/get/id/",
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
												"prefix"   => "/api/data/get/type/needs/id/",
												"suffix"   => "")*/
									   		"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#need.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/api/need/get/id/",
																							"suffix"   => "" ),
												 		))
									   		))

				 					)),
	);

	public static $dataBinding_allEvent  = array(
		"@type"		=> "Event",
	    "name" 		=> array("valueOf" => "name"),
	    "typeCommunecter" 		=> array("valueOf" => "type"),
	    "image"		=> array("valueOf" => "image",
							 "type" 	=> "url"),
	    "url" 		=> array("valueOf" => array(
								"communecter" 	=> array(	"valueOf" => '_id.$id',
										   						"type" 	=> "url", 
																"prefix"   => "/#event.detail.id.",
																"suffix"   => ""),
							    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																"type" 	=> "url", 
																"prefix"   => "/api/event/get/id/",
																"suffix"   => "" ),
	    						"website" 		=> array(	"valueOf" => 'url')))
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
																"prefix"   => "/api/event/get/id/",
																"suffix"   => "" ))),
							    /*"osm" 			=> array(	"valueOf"  	=> 'geo', 
																"type" 	=> "urlOsm", 
																"prefix"   => "http://www.openstreetmap.org/#map=16/",
																"suffix"   => "" ),*/
							    /*"city" 			=> array(	"valueOf"  	=> 'address.codeInsee', 
																"type" 	=> "url", 
																"prefix"   => "/api/data/get/type/cities/insee/",
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
									   		"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#person.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/api/person/get/id/",
																							"suffix"   => "" ),
												 		))
									   	) ),
									"needs" => array( 
										"object" => "needs",
										"collection" => Event::COLLECTION  , 
										"valueOf" => array (
									   		"type" => "Need",
									   		"name" => array("valueOf" => "name"),
									   		"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#need.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/api/need/get/id/",
																							"suffix"   => "" ),

									   		))
				 					))
								))
	);

	public static $dataBinding_allProject  = array(
		"@type"		=> "Project",
	    "name" 		=> array("valueOf" => "name"),
	    "image"		=> array("valueOf" => "image",
							 "type" 	=> "url"),
	    "url" 		=> array("valueOf" => array(
		    					
								"communecter" 	=> array(	"valueOf" => '_id.$id',
										   						"type" 	=> "url", 
																"prefix"   => "/#project.detail.id.",
																"suffix"   => ""),
							    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																"type" 	=> "url", 
																"prefix"   => "/api/project/get/id/",
																"suffix"   => "" ),
							    "website" 		=> array(	"valueOf" => 'url'))),
	);

	public static $dataBinding_project = array(
		"@type"		=> "Project",
	    "name" 		=> array("valueOf" => "name"),
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
																"prefix"   => "/api/project/get/id/",
																"suffix"   => "" ))),
							    /*"osm" 			=> array(	"valueOf"  	=> 'geo', 
																"type" 	=> "urlOsm", 
																"prefix"   => "http://www.openstreetmap.org/#map=16/",
																"suffix"   => "" ),*/
							    /*"city" 			=> array(	"valueOf"  	=> 'address.codeInsee', 
																"type" 	=> "url", 
																"prefix"   => "/api/data/get/type/cities/insee/",
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
									   		"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#person.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/api/person/get/id/",
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
																"prefix"   => "/api/need/get/id/",
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
											"prefix"   => "/api/data/get/type/needs/id/",
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
									   		"url" 	=> array("valueOf" => array(
									    					"communecter" 	=> array(	"valueOf" => '_id.$id',
																	   						"type" 	=> "url", 
																							"prefix"   => "/#person.detail.id.",
																							"suffix"   => ""),
														    "api" 			=> array(	"valueOf"  	=> '_id.$id', 
																							"type" 	=> "url", 
																							"prefix"   => "/api/person/get/id/",
																							"suffix"   => "" ),
												 		))
									   	) )))
	);

	

	public static $dataBinding_city = array(
		"@type"		=> "City",
		"@id" 		=> array("valueOf"  => 'insee', 
							 "type" 	=> "url", 
							 "prefix"   => "/api/data/get/type/cities/insee/",
							 "suffix"   => "" ),
	    "name" 		=> array("valueOf" => "name"),
	    "alternateName" => array("valueOf" => "alternateName"),
	    "insee" 		=> array("valueOf" => "insee"),
	    "dep" 		=> array("valueOf" => "dep"),
	    "depName" 		=> array("valueOf" => "depName"),
	    "region" 		=> array("valueOf" => "region"),
	    "regionName" 		=> array("valueOf" => "regionName"),
	    "country" 		=> array("valueOf" => "country"),
	    "url" 	=> array("valueOf" => array(
	    					"communecter" 	=> array(	"valueOf" => 'insee',
									   						"type" 	=> "url", 
															"prefix"   => "/#city.detail.insee.",
															"suffix"   => ""),
	    					"wikidata" 	=> array(	"valueOf" => 'wikidataID',
									   						"type" 	=> "url", 
															"prefix"   => "http://www.wikidata.org/entity/",
															"suffix"   => "",
															"outsite"   => true),
						    "citoyens" 			=> array(	"valueOf"  	=> 'insee', 
															"type" 	=> "url", 
															"prefix"   => "/api/person/get/insee/",
															"suffix"   => "" ),
						    "organizations" 			=> array(	"valueOf"  	=> 'insee', 
															"type" 	=> "url", 
															"prefix"   => "/api/organization/get/insee/",
															"suffix"   => "" ),
						    "projects" 			=> array(	"valueOf"  	=> 'insee', 
															"type" 	=> "url", 
															"prefix"   => "/api/project/get/insee/",
															"suffix"   => "" ),
						    "events" 			=> array(	"valueOf"  	=> 'insee', 
															"type" 	=> "url", 
															"prefix"   => "/api/event/get/insee/",
															"suffix"   => "" ),
						    
				 		)),
		"postalCodes" 	=> array("valueOf"=>"postalCodes"),
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


	public static $dataBinding_news = array(
		"@type"		=> "News",
		
	    "text" 		=> array("valueOf" => "text"),
	   	"date"		=> array("valueOf" => "startDate"),
	   	"created"		=> array("valueOf" => "endDate"),
	 	"scope" 	=> array("parentKey"=>"scope", 
	    					 "valueOf" => array(
									"type" 		=> array("valueOf" => "type")
				 					)),
		"target" 	=> array(	"communecter" 	=> array(	"valueOf" => 'id',
										   						"type" 	=> "url", 
																"prefix"   => "/#person.detail.id.",
																"suffix"   => ""),
							    "api" 			=> array(	"valueOf"  	=> 'id', 
																"type" 	=> "url", 
																"prefix"   => "/api/person/get/id/",
																"suffix"   => "" )),
		"author" 	=> array(	"valueOf" => '_id.$id',
			   						"type" 	=> "url", 
									"prefix"   => "/#person.detail.id.",
									"suffix"   => "")				   	
	);

}