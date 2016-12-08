<?php 
class TranslateSchema {
	
/*
	----------------- SCHEMA ----------------- 

https://schema.org/Person

	{
  "@context": "http://schema.org",
  "@type": "Person",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Seattle",
    "addressRegion": "WA",
    "postalCode": "98052",
    "streetAddress": "20341 Whitworth Institute 405 N. Whitworth"
  },
  "colleague": [
    "http://www.xyz.edu/students/alicejones.html",
    "http://www.xyz.edu/students/bobsmith.html"
  ],
  "email": "mailto:jane-doe@xyz.edu",
  "image": "janedoe.jpg",
  "jobTitle": "Professor",
  "name": "Jane Doe",
  "telephone": "(425) 123-4567",
  "url": "http://www.janedoe.com"
}*/
//http://127.0.0.1/ph/communecter/data/get/type/citoyens/id/520931e2f6b95c5cd3003d6c/format/schema
	public static $dataBinding_allPerson = array(
		"@context"  => "http://schema.org",
		"@type"		=> "Person",
		"@id" 		=> array("valueOf"  	=> '_id.$id', 
							 "type" 	=> "url", 
							 "prefix"   => "/data/get/type/citoyens/id/",
							 "suffix"   => "/format/schema" ),
	    "name" 		=> array("valueOf" => "name"),
	  	"url"		=> array("valueOf" => "url")
	);

	public static $dataBinding_person = array(
		"@context"  => "http://schema.org",
		"@type"		=> "Person",
		"@id" 		=> array("valueOf"  	=> '_id.$id', 
							 "type" 	=> "url", 
							 "prefix"   => "/data/get/type/citoyens/id/",
							 "suffix"   => "/format/schema" ),
	    "name" 		=> array("valueOf" => "name"),
	    "address" 	=> array("parentKey"=>"address", 
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
							 "prefix"   => "/"),
		"jobTitle"	=> array("valueOf" => "positions"),
		"telephone"	=> array("valueOf" => "phoneNumber"),
		"url"		=> array("valueOf" => "url")
	);

	public static $dataBinding_allOrganization = array(
		"@context"  => "http://schema.org",
		"@type"		=> "Organization",
		"id" 		=> array("valueOf"  	=> '_id.$id', 
							 "type" 	=> "url", 
							 "prefix"   => "/data/get/type/organizations/id/",
							 "suffix"   => "/format/schema" ),
	    "name" 		=> array("valueOf" => "name"),
	  	"url"		=> array("valueOf" => "url")
	);

	public static $dataBinding_organization = array(
		"@context"  => "http://schema.org",
		"@type"		=> "Organization",
		"id" 		=> array("valueOf"  	=> '_id.$id', 
							 "type" 	=> "url", 
							 "prefix"   => "/data/get/type/organizations/id/",
							 "suffix"   => "/format/schema" ),
	    "name" 		=> array("valueOf" => "name"),
	    "address" 	=> array("parentKey"=>"address", 
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
		"url"		=> array("valueOf" => "url")
	);

	public static $dataBinding_allProject = array(
		"@context"  => "http://schema.org",
		"@type"		=> "CreativeWork",
		"@id" 		=> array("valueOf"  	=> '_id.$id', 
							 "type" 	=> "url", 
							 "prefix"   => "/data/get/type/projects/id/",
							 "suffix"   => "/format/schema" ),
	    "name" 		=> array("valueOf" => "name"),
	  	"url"		=> array("valueOf" => "url")
	);
	public static $dataBinding_project = array(
		"@context"  => "http://schema.org",
		"@type"		=> "CreativeWork",
		"@id" 		=> array("valueOf"  	=> '_id.$id', 
							 "type" 	=> "url", 
							 "prefix"   => "/data/get/type/projects/id/",
							 "suffix"   => "/format/schema" ),
	    "name" 		=> array("valueOf" => "name"),
	    "address" 	=> array("parentKey"=>"address", 
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
		"url"		=> array("valueOf" => "url")
	);

	public static $dataBinding_allEvent = array(
		"@context"  => "http://schema.org",
		"@type"		=> "CreativeWork",
		"@id" 		=> array("valueOf"  	=> '_id.$id', 
							 "type" 	=> "url", 
							 "prefix"   => "/data/get/type/projects/id/",
							 "suffix"   => "/format/schema" ),
	    "name" 		=> array("valueOf" => "name"),
	  	"url"		=> array("valueOf" => "url")
	);

	public static $dataBinding_event = array(
		"@context"  => "http://schema.org",
		"@type"		=> "Event",
		"@id" 		=> array("valueOf"  	=> '_id.$id', 
							 "type" 	=> "url", 
							 "prefix"   => "/data/get/type/events/id/",
							 "suffix"   => "/format/schema" ),
	    "name" 		=> array("valueOf" => "name"),
	    "address" 	=> array("parentKey"=>"address", 
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
					   	 ),
	);

//http://127.0.0.1/ph/communecter/data/get/type/cities/insee/97414/format/schema
public static $dataBinding_city = array(
		"@context"  => "http://schema.org",
		"@type"		=> "City",
		"@id" 		=> array("valueOf"  => 'insee', 
							 "type" 	=> "url", 
							 "prefix"   => "/data/get/type/cities/insee/",
							 "suffix"   => "/format/schema" ),
	    "name" 		=> array("valueOf" => "name"),
	    "alternateName" => array("valueOf" => "alternateName"),
	    "address" 	=> array("valueOf" => array(
								"@type" 			=> "PostalAddress", 
								"@id" 				=> array("valueOf"  => 'insee', 
															 "type" 	=> "url", 
															 "prefix"   => "/data/get/type/city/insee/",
															 "suffix"   => "/format/schema" ),
								"addressLocality"   => array("valueOf" => "addressLocality"),
								"addressRegion" 	=> array("valueOf" => "region"),
								"postalCode" 		=> array("valueOf" => "cp"),
				 				"streetAddress" 	=> array("valueOf" => "streetAddress")) ),
	    "geo" 		=> array("valueOf" => "geo"),
	    //photos
	    //telephone
	    //hasMap
	);
}