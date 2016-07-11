<?php 
class TranslatePlp {
	
/*

	----------------- PLP ----------------- 

	https://github.com/hackers4peace/plp-test-data/blob/master/graph.jsonld
	*/
	//http://127.0.0.1/ph/communecter/data/get/type/citoyens/id/520931e2f6b95c5cd3003d6c/format/plp
	public static $dataBinding_person = array(
	    "@context"  => "https://w3id.org/plp/v1",
		"@type"		=> "Person",
		"id" 		=> array("valueOf"  	=> '_id.$id', 
							 "type" 	=> "url", 
							 "prefix"   => "/data/get/type/citoyens/id/",
							 "suffix"   => "/format/schema" ),
	    "name" 		=> array("valueOf" => "name"),
	    "image"		=> array("valueOf" => "img",
							 "type" 	=> "url", 
							 "prefix"  => "/communecter/"),
	    "birthDate" => array("valueOf" => "bitrh"),
	    "currentLocation" 	=> array(
	    					 "valueOf" => array(
								"@type" => "Place", 
								"name" => "CURRENT",
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
								 ) ),
	    "homeLocation" 	=> array(
	    					 "valueOf" => array(
								"@type" => "Place", 
								"name" => "HOME",
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
								 ) ),
	    "description"		=> array("valueOf" => "description"),
	    "contactPoint" => array(
	    		array(
	    			"name" => "email",
	    			"@type" => "ContactPoint", 
		    		"id" => array("valueOf" => "email",
		    					  "prefix"  => "mailto:")),
	    		array(
	    			"name" => "phoneNumber",
	    			"@type" => "ContactPoint", 
		    		"id" => array("valueOf" => "telephone")), ///????
	    		array(
	    			"name" => "jabber",
	    			"@type" => "ContactPoint", 
		    		"id" => array("valueOf" => "jabber")),
	    		array(
	    			"name" => "irc",
	    			"@type" => "ContactPoint", 
		    		"id" => array("valueOf" => "jabber")),
	    		array(
	    			"name" => "jabber",
	    			"@type" => "ContactPoint", 
		    		"id" => array("valueOf" => "jabber")),

	    ), 
	    "sameAs" => array(
	    	array(
	    		"name" => "Website",
		    	"id" => array("valueOf" => "url")),
	    	array(
	    	 	"name" => "Github",
		    	"id" => array("valueOf" => "socialNetwork.github")),
	    	array(
	    		"name" => "Twitter",
		    	"id" => array("valueOf" => "socialNetwork.twitter")),
	    	array(
	    		"name" => "Facebook",
		    	"id" => array("valueOf" => "socialNetwork.facebook")),
	    	array(
	    		"name" => "Google+",
		    	"id" => array("valueOf" => "socialNetwork.googleplus")),
	    	array(
	    		"name" => "LinkedIn",
		    	"id" => array("valueOf" => "socialNetwork.linkedin")),
	    	array(
	    		"name" => "Skype",
		    	"id" => array("valueOf" => "socialNetwork.skype"))
	    ),
	    "cco:skill" => array("valueOf" => "positions"),
	    //"cco:habit" => array(),
	    "foaf:currentProject" => array( 
	    								"object" => "links.projects",
	    								"collection" => "projects" , 
	    								"valueOf" => array (
	    							   		"type" => "doap:Project",
	    							   		"id" => array(
	    							   			"valueOf" => '_id.$id',
	    							   			"type" 	=> "url", 
												"prefix"   => "/data/get/type/projects/id/",
												"suffix"   => "/format/schema"
	    							   					),
	    							   		"name" => array("valueOf" => "name")
	    							   	) ),
	    "member" => array( 
							"object" => "links.memberOf",
							"collection" => "organizations" , 
							"valueOf" => array (
						   		"type" => "Organization",
						   		"id" => array(
						   			"valueOf" => '_id.$id',
						   			"type" 	=> "url", 
									"prefix"   => "/data/get/type/organizations/id/",
									"suffix"   => "/format/schema"
						   					),
						   		"name" => array("valueOf" => "name")
						   	) ),
	    "attendeeIn" => array( 
							"object" => "links.events",
							"collection" => "events" , 
							"valueOf" => array (
						   		"type" => "Event",
						   		"id" => array(
						   			"valueOf" => '_id.$id',
						   			"type" 	=> "url", 
									"prefix"   => "/data/get/type/events/id/",
									"suffix"   => "/format/schema"
						   					),
						   		"name" => array("valueOf" => "name")
						   	) ),
	    //"visited" => array(),
	    //"seeks" => array(),
	    //"owns" => array(),
	    //"sec:publicKey" => array(),
	    //"subjectOf" => array()
	
	);
}