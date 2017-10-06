<?php

class TranslateGeoJson {
	
/*Exemple type d'un fichier GeoJSON

	{
		"type": "FeatureCollection",
		"features": [
			{
				"type": "Feature",
				"geometry": {
					"type": "Point",
					"coordinates": [ 102, 0.5 ]
				},
				"properties": {
					"prop0": "value0"
				}
			},
			{
				"type": "Feature",
				"geometry": {
					"type": "Point",
					"coordinates": [55,0.5]
				},
				"properties": {
					"prop0": "value0"
				}
			}

		]
	}
*/
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

	public static $dataBinding_allOrganization = array(
		"type" => "Feature",
		"geometry" => array ("type" => "Point",		
							"coordinates" => array ("valueOf" => "geo"),
			),
		"properties" => array(
			"type" => "properties",
			array(
				"email" => array("valueOf" => "email"),
				"name" => array("valueOf" => "name"),
				"username" => array("valueOf" => "username"),
				"img"		=> array("valueOf" => "profilImageUrl",
									"type" 	=> "url", 
									"prefix"   => "/"),
				),
			),


		
	);

	public static $dataBinding_organization = array(


			
			"type" => "Feature",
			"geometry" => 
						array ("type" => "Point",		
								"coordinates" => array ("valueOf" => "geo"),
				),
			"properties" => array(
				"type" => "properties",
				array(
					"email" => array("valueOf" => "email"),
					"name" => array("valueOf" => "name"),
					"username" => array("valueOf" => "username"),
					"img"		=> array("valueOf" => "profilImageUrl",
										"type" 	=> "url", 
										"prefix"   => "/"),
					),
				),

	);

	public static $dataBinding_allEvent  = array(

			
			"type" => "Feature",
			"geometry" => 
						array ("type" => "Point",		
								"coordinates" => array ("valueOf" => "geo"),
				),
			"properties" => array(
				"type" => "properties",
				array(
					"email" => array("valueOf" => "email"),
					"name" => array("valueOf" => "name"),
					"username" => array("valueOf" => "username"),
					"img"		=> array("valueOf" => "profilImageUrl",
										"type" 	=> "url", 
										"prefix"   => "/"),
					),
				),

		
		);
	public static $dataBinding_event = array(

			"type" => "Feature",
			"geometry" => 
						array ("type" => "Point",		
								"coordinates" => array ("valueOf" => "geo"),
				),
			"properties" => array(
				"type" => "properties",
				array(
					"email" => array("valueOf" => "email"),
					"name" => array("valueOf" => "name"),
					"username" => array("valueOf" => "username"),
					"img"		=> array("valueOf" => "profilImageUrl",
										"type" 	=> "url", 
										"prefix"   => "/"),
					),
				),


		
	);

	public static $dataBinding_allProject  = array(


			"type" => "Feature",
			"geometry" => 
						array ("type" => "Point",		
								"coordinates" => array ("valueOf" => "geo"),
				),
			"properties" => array(
				"type" => "properties",
				array(
					"email" => array("valueOf" => "email"),
					"name" => array("valueOf" => "name"),
					"username" => array("valueOf" => "username"),
					"img"		=> array("valueOf" => "profilImageUrl",
										"type" 	=> "url", 
										"prefix"   => "/"),
					),
				),


	);

	public static $dataBinding_project = array(

		"type" => "Feature",
		"geometry" => 
					array ("type" => "Point",		
							"coordinates" => array ("valueOf" => "geo"),
			),
		"properties" => array(
			"type" => "properties",
			array(
				"email" => array("valueOf" => "email"),
				"name" => array("valueOf" => "name"),
				"username" => array("valueOf" => "username"),
				"img"		=> array("valueOf" => "profilImageUrl",
									"type" 	=> "url", 
									"prefix"   => "/"),
				),
			),		
	);

	public static $dataBinding_allPerson = array(


		"type" => "Feature",
			"geometry" => 
						array ("type" => "Point",		
								"coordinates" => array ("valueOf" => "geo"),
				),
			"properties" => array(
				"type" => "properties",
				array(
					"email" => array("valueOf" => "email"),
					"name" => array("valueOf" => "name"),
					"username" => array("valueOf" => "username"),
					"img"		=> array("valueOf" => "profilImageUrl",
										"type" 	=> "url", 
										"prefix"   => "/"),
					),
				),

		
	);

	public static $dataBinding_person = array(




			"type" => "Feature",
			"geometry" => 
						array ("type" => "Point",		
								"coordinates" => array ("valueOf" => "geo"),
				),
			"properties" => array(
				"type" => "properties",
				array(
					"email" => array("valueOf" => "email"),
					"name" => array("valueOf" => "name"),
					"username" => array("valueOf" => "username"),
					"img"		=> array("valueOf" => "profilImageUrl",
										"type" 	=> "url", 
										"prefix"   => "/"),
					),
				),


			);


	public static $dataBinding_city = array(

	

			"type" => "Feature",
			"geometry" => 
						array ("type" => "Point",		
								"coordinates" => array ("valueOf" => "geo"),
				),
			"properties" => array(
				"type" => "properties",
				array(
					"email" => array("valueOf" => "email"),
					"name" => array("valueOf" => "name"),
					"username" => array("valueOf" => "username"),
					"img"		=> array("valueOf" => "profilImageUrl",
										"type" 	=> "url", 
										"prefix"   => "/"),
					),
				),


	);


	public static function getGeojsonCoor($val, $bindPath) {

		if (isset($bindPath["type"]) && $bindPath["type"] == "Point") {


			if ((isset($val["coordinates"]["latitude"])) && (isset($val["coordinates"]["longitude"]))) {

				// Damien : 1 ligne
				$latitude = $val["coordinates"]["latitude"];
			 	$longitude = $val["coordinates"]["longitude"];

			 	$latitude = floatval($latitude);
				$longitude = floatval($longitude);

				$val["coordinates"] = array();
				array_push($val["coordinates"], $longitude);				
				array_push($val["coordinates"], $latitude);
				// Fin Damien : 1 ligne
			 	
			} elseif ((!isset($val["coordinates"]["latitude"])) || (!isset($val["coordinates"]["longitude"]))) {
				unset($val);
			}
			
		}

		if (isset($val)) {
			return $val;	
		}
		
		
	}

	public static function getGeojsonProperties($val, $bindPath) {

			if (isset($val["0"]["name"])) {
				$val["name"] = $val["0"]["name"];
				//var_dump($val);
			}
			if (isset($val["0"]["username"])) {
				$val["username"] = $val["0"]["username"];
			}
			if (isset($val["0"]["email"])) {
				$val["email"] = $val["0"]["email"];
			}
				if (isset($val["0"]["img"])) {
				$val["img"] = $val["0"]["img"];
			}

			
			unset($val["0"]);
			
			unset($val["type"]);
			
		
		return $val;

	}


}






?>