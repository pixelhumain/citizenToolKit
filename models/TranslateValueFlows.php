<?php

class TranslateValueFlows {
	
/*Exemple type d'un fichier GeoJSON
	
model	"vocab.agent"
fields	
created_date	"2017-03-25"
changed_date	"2017-03-25"
name	"Bob"
url	""
note	"Me"
agent_subclass	"Person"
created_by	
0	"bob"
changed_by


*/

/*

Exemple pour la traduction d'une news en onthologie PH en format GeoJson

	public static $dataBinding_news = array(
		"type" => "Feature",
		"geometry" => array (	"type" => "Point",		
								"coordinates" => array ("valueOf" => "scope.cities.0.geo") ),
		"properties" => array(
			"type" => "properties",
			array( 	"email" 	=> array("valueOf" => "email"),
					"name" 		=> array("valueOf" => "name"),
					"username" 	=> array("valueOf" => "username"),
					"img"		=> array("valueOf" => "profilImageUrl",
										"type" 	=> "url", 
										"prefix"   => "/") ) ),
	);

*/

	public static $dataBinding_agent = array(

		"fields" => array (	
			"name" => array ("valueOf" => "name"),		
			"url" => array ("valueOf" => "url"),
			"note" => array("valueOf" => "description") 
		),
	);

}

?>