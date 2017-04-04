<?php

class TranslateKml {
	
	public static $dataBinding_news = array(


			"name" => array("valueOf" => '_id.$id'),
			"description" => 
						array ("type" => "description_kml",
									
								"text" => array ("valueOf" => "text"),

				),

			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "latitude" => array("valueOf" => "scope.cities.0.geo.latitude"),
								   "longitude" => array("valueOf" => "scope.cities.0.geo.longitude"),
							),
					),
	);



	public static $dataBinding_allOrganization = array(


			"name" => array("valueOf" => 'name'),

			"description" => array(
							"img"		=> array("valueOf" => "profilImageUrl",
							"type" 	=> "url", 
							"prefix"   => "/"),
							// "description" => array("valueOf" => "username"),
							),
			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "latitude" => array("valueOf" => "geo.latitude"),
								   "longitude" => array("valueOf" => "geo.longitude"),
							),
					),

		
	);

	public static $dataBinding_organization = array(


			"name" => array("valueOf" => 'name'),
			"description" => array(
							"img"		=> array("valueOf" => "profilImageUrl",
							"type" 	=> "url", 
							"prefix"   => "/"),
							// "description" => array("valueOf" => "username"),
							),
			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "latitude" => array("valueOf" => "geo.latitude"),
								   "longitude" => array("valueOf" => "geo.longitude"),
							),
					),


	);

	public static $dataBinding_allEvent  = array(

			"name" => array("valueOf" => 'name'),
			"description" => array(
							"img"		=> array("valueOf" => "profilImageUrl",
							"type" 	=> "url", 
							"prefix"   => "/"),
							// "description" => array("valueOf" => "username"),
							),
			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "latitude" => array("valueOf" => "geo.latitude"),
								   "longitude" => array("valueOf" => "geo.longitude"),
							),
					),
		
		);
	public static $dataBinding_event = array(

			"name" => array("valueOf" => 'name'),
			"description" => array(
							"img"		=> array("valueOf" => "profilImageUrl",
							"type" 	=> "url", 
							"prefix"   => "/"),
							// "description" => array("valueOf" => "username"),
							),
			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "latitude" => array("valueOf" => "geo.latitude"),
								   "longitude" => array("valueOf" => "geo.longitude"),
							),
					),
		
	);

	public static $dataBinding_allProject  = array(


			"name" => array("valueOf" => 'name'),
			"description" => array(
							"img"		=> array("valueOf" => "profilImageUrl",
							"type" 	=> "url", 
							"prefix"   => "/"),
							// "description" => array("valueOf" => "username"),
							),
			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "latitude" => array("valueOf" => "geo.latitude"),
								   "longitude" => array("valueOf" => "geo.longitude"),
							),
					),

	);

	public static $dataBinding_project = array(


			"name" => array("valueOf" => 'name'),
			"description" => array(
							"img"		=> array("valueOf" => "profilImageUrl",
							"type" 	=> "url", 
							"prefix"   => "/"),
							// "description" => array("valueOf" => "username"),
							),
			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "latitude" => array("valueOf" => "geo.latitude"),
								   "longitude" => array("valueOf" => "geo.longitude"),
							),
					),
		
	);

	public static $dataBinding_allPerson = array(



			"name" => array("valueOf" => 'name'),
			"description" => array(
							"img"		=> array("valueOf" => "profilImageUrl",
							"type" 	=> "url", 
							"prefix"   => "/"),
							// "description" => array("valueOf" => "username"),
							),
			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "latitude" => array("valueOf" => "geo.latitude"),
								   "longitude" => array("valueOf" => "geo.longitude"),
							),
					),

		
	);

	public static $dataBinding_person = array(

			"name" => array("valueOf" => 'name'),
			"description" => array(
							"img"		=> array("valueOf" => "profilImageUrl",
							"type" 	=> "url", 
							"prefix"   => "/"),
							// "description" => array("valueOf" => "username"),
							),
			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "latitude" => array("valueOf" => "geo.latitude"),
								   "longitude" => array("valueOf" => "geo.longitude"),
							),
					),

			);


	public static $dataBinding_city = array(

	

			"name" => array("valueOf" => 'name'),
			"description" => array("valueOf" => "structure"),
			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "latitude" => array("valueOf" => "geo.latitude"),
								   "longitude" => array("valueOf" => "geo.longitude"),
							),
					),

		


	);


	public static function getKmlCoor($val, $bindPath) {


		if ((isset($val["coordinates"]["latitude"])) && (isset($val["coordinates"]["longitude"]))) {

			$latitude = $val["coordinates"]["latitude"];
		 	$longitude = $val["coordinates"]["longitude"];

		 	$coor = $longitude.','.$latitude;
			$val['coordinates'] = $coor;

			unset($val["type"]);
			
		} elseif ((!isset($val["coordinates"]["latitude"])) || (!isset($val["coordinates"]["longitude"]))) {

			unset($val);


		}
		// if (isset($bindPath["type"]) && $bindPath["type"] == "description_kml") {

		// 	if (isset($val["text"])) {
		// 		$val = $val["text"];
		// 	} else {
		// 		$val = "Pas de description pour cette news";
		// 	}



		// }	
	
		if (isset($val)) {
			return $val;		
		}	
		

	}

	public static function specFormatByType($val, $bindPath) {

		if (isset($val["text"])) {
			$val = $val["text"];
		} else {
			$val = "Pas de description pour cette news";
		}

		return $val;
	}


}

?>