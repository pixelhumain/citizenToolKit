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

												"enclosure" => array(
													"type" => "image_rss",											
													"id" => array("valueOf" => '_id.$id'),
													"target_id" => array("valueOf" => "target.id"),
													"target_type" => array("valueOf" => "target.type"),
													"verb" => array ("valueOf" => "verb"),
													"object_type" => array("valueOf" => "object.objectType"),
													"object_id" => array("valueOf" => "object.id"),
													"type2" => array("valueOf" => "type"),
													"image_id" => array("valueOf" => "media.images.0"),

													
												),

												
						
											

							    				
							    		//		)
										//	),						

		//)



		);


	public static function specFormatByType($val, $bindPath ){

		if (isset($bindPath["type"]) && $bindPath["type"] == "description" /*&& isset($bindPath["verb"]["valueOf"])*/) 
		{

			if (isset($val["text"])) {
				$val = $val["text"];
			}
			else {
				
				
				//$element = PHDB::findOneById($val["object_news"]["objectType"] , $val["object_news"]["id"]);
				$element = Element::getByTypeAndId($val["object_news"]["objectType"] , $val["object_news"]["id"]);
				//var_dump($element);
				if (isset($val["target"]["type"]) && (isset($val["target"]["id"]))) {
					$author = Element::getByTypeAndId( $val["target"]["type"] , $val["target"]["id"]);
				}
				//$id_orga = PHDB::findOneById($val["object_news"]["objectType"] , $val["object_news"]["id"]);
				//$id_event = PHDB::findOneById(Event::COLLECTION, $val["object_news"]["id"]);
				//$id_projet = PHDB::findOneById(Project::COLLECTION, $val["object_news"]["id"]);
				$verb = $val["verb"];
				$type = $val["object_news"]["objectType"];

				if (($val["object_news"]["objectType"] == "events")  || ($val["object_news"]["objectType"] == organization::COLLECTION) || ($val["object_news"]["objectType"] == "projects")) {
					$val_nom = $element["name"];
				}

				/* else if ($val["object_news"]["objectType"] == "organizations") {
					$val_type = $element["name"];
				} else if ($val["object_news"]["objectType"] == "projects") {
					$val_type = $element["name"];
				} */
			

				//$val = $val["verb"];
				if (isset($author["username"])) {

					$val = $author["username"];
				} else {
					$val = 'Quelqu\'un ou quelque chose ';
				}	

				if ($verb == "create") {
					$verb = "a crée un(e) nouvel(le) ";
				}
				$val .= ' ' . $verb;

				if ($type == organization::COLLECTION) {
					$type = " Organisation";
				} else if ($type == "projects") {
					$type = "Projet";
				} else if ($type == "events") {
					$type = "Evenement";
				}
				$val .= ' ' . $type;
				if (isset($val_nom)) {
					$val .= ' sous le nom de "' . $val_nom . ' "';
				}

			}
		}	



		if (isset($bindPath["type"]) && $bindPath["type"] == "title") 
		{


			$type = $val["type_el"];

			if (isset($val["object_news"]["objectType"])) {
				$object_type= $val["object_news"]["objectType"];
			}
			
				if ($type == "news") {
					$val = "Rédaction d'un message";
				} else if ($type == "activityStream") {
					$val = "Création";
					if (isset($object_type)) {
						if ($object_type == Organization::COLLECTION) {
							$object_type = " d'une Organisation";
						} else if ($object_type == "projects") {
							$object_type = " d'un Projet";
						} else if ($object_type == "events") {
							$object_type = " d'un Evenement";
						}
							$val .= $object_type;
						}
				}

		}			

			return $val;


	}

	public static function getRssImage($val, $bindPath) {

			

			if (isset($val["verb"])) {
				// var_dump($val);
				$doc_crea = Document::getListDocumentsByIdAndType($val["object_id"], $val["object_type"]);

				if (isset($doc_crea["profil"])) {
					
					foreach ($doc_crea['profil'] as $key => $value) {
						foreach ($value as $key2 => $value2) {
							// if ($val["image_id"] == $value2) {
						 	$image_path = $value["imagePath"];
							// }

							//var_dump($value["imagePath"]);
						}
					}

					$image_path = "http://127.0.0.1".$image_path;
				
					$val = $image_path;

				} else {
					$val = "http://127.0.0.1/ph/assets/7d331fe5/images/thumbnail-default.jpg";
	
				}
				
				
			} elseif (isset($val["image_id"])) {	

				// var_dump($val["image_id"]);					
				$doc = Document::getListDocumentsByIdAndType($val["target_id"], $val["target_type"]);
				// //$params['images'] = Document::getListDocumentsByIdAndType($id, $type, $contentKey, Document::DOC_TYPE_IMAGE);

				// if ($val["verb"] == "create") {
				// 	$doc_crea = Document::getListDocumentsByIdAndType($val["target_id"], $val["target_type"]);
				// 	var_dump($doc_crea);
				// }		

				foreach ($doc['slider'] as $key => $value) {
					foreach ($value as $key2 => $value2) {
						if ($val["image_id"] == $value2) {
							$image_path = $value["imagePath"];
						}
					}
				}

				$image_path = "http://127.0.0.1".$image_path;
				
				$val = $image_path;

				//var_dump($val);


			} else {
				$val = "http://127.0.0.1/ph/assets/7d331fe5/images/thumbnail-default.jpg";
				

			}


		return $val;

	}


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