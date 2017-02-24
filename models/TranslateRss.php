<?php 

/* 

FLux RSS 

*/

// Début de travail de Raha : 


/* public static $dataBinding_news = array(
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

*/



class TranslateRss {
	
	public static $dataBinding_news = array(

		// Balises obligatoire 

		//"channel" 	=> array (
								
								//"title" => "Actu : Fils d'actualité de Communecter",


								//"link"  =>  array ("valueOf" => 'target.$id',
											
										
								//	   					"type" 	=> "url", 
								//						"prefix"   => "/#news.index.type.organizations.id.", 
								//						"suffix"   => ''), // .id.l'id de l'object  

		
								//"item"	=> array("valueOf" => array(
												"link" => array ("valueOf" => '_id.$id',
									   						"type" 	=> "url", //url de la news visible quand on à un très grand texte comme news
															"prefix"   => "/#news.detail.id.",
															"suffix"   => ""),
												//"verb" => array("valueOf" => "verb"),

												"title"  =>	array   (
																	"type" => "title",
																	"type_el" => array("valueOf" => "type"),
																	"object_news" => array("valueOf" => "object"),
																	 

																	//"titi" => array ("valueOf" => "type",
											    								
																					//"verb" => array("valueOf" => "verb"),
																					//),
																	//"toto" => array("valueOf" => "verb",
																					//),
																	),

												//"description"  => array("valueOf" => "text"),


												"description" => 
																array ("type" => "description",				
																		"verb" => array ( "valueOf" => "verb"),
																		"object_news" => array("valueOf" => "object"),
																		"text" => array ("valueOf" => "text"),
																		"author" => array ("valueOf" => "author"),
																		"target" => array ("valueOf" => "target"),
																		
																),					

												
							    				"pubDate" => array ("valueOf" => "date",
							    									"type" => "date",
							    									"prefix"   => "",
																	"suffix"   => ""),
				
												"guid" => array("valueOf" => "_id"),

												//"author" => "test.test@gmail.com", //présent dans le RSS mais n'apparait pas dans le flux.

												//"enclosure" => 'url = "http://127.0.0.1/ph/assets/7d331fe5/images/Communecter-32x32.svg"',								    				
							    			 	//"category" => array ("valueOf" => "type"),

							    				
							    		//		)
										//	),						

		//)



		);


	/*  


	#news.index.type.citoyens.id.5880b24a8fe7a1a65b8b456b


	**********Old version **********
	public static $dataBinding_news = array(
	    
		"title" 		=> array(	"type" => "description",
									"authorId" => 'target.id',
						   			"authorType" 	=> "target.type"),
		
		"description" 	=> array(	"type" => "description",
									"authorId" => 'target.id',
						   			"authorType" 	=> "target.type"),
		"lastBuildDate" => array(	"valueof" => "date"),
		"link" 			=> array(	"communecter" 	=> array(	
											"valueOf" => 'target.id',
				   							"type" 	=> "url",
				   							"authorType" 	=> "target.type")),
	);*/
}