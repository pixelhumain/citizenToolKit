<?php

class TranslateKml {
	
	public static $dataBinding_news = array(


			"name" => array("valueOf" => '_id.$id'),
			"description" => 
						array ("type" => "description_kml",		
								"text" => array ("valueOf" => "text"),
				),
			"Point" => array(
							"type" => "coor_news",
							"coordinates" => 
							array( "valueOf" => "scope"),
					),

	);

	public static $dataBinding_allOrganization = array(


			"name" => array("valueOf" => 'name'),
			"description" => array("valueOf" => "type"),
			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "valueOf" => "geo"),
					),

		
	);

	public static $dataBinding_organization = array(


			"name" => array("valueOf" => 'name'),
			"description" => array("valueOf" => "type"),
			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "valueOf" => "geo"),
					),


	);

	public static $dataBinding_allEvent  = array(

			"name" => array("valueOf" => 'name'),
			"description" => array("valueOf" => "type"),
			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "valueOf" => "geo"),
					),

		
		);
	public static $dataBinding_event = array(

			"name" => array("valueOf" => 'name'),
			"description" => array("valueOf" => "type"),
			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "valueOf" => "geo"),
					),

		
	);

	public static $dataBinding_allProject  = array(


			"name" => array("valueOf" => 'name'),
			"description" => array("valueOf" => "type"),
			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "valueOf" => "geo"),
					),

	);

	public static $dataBinding_project = array(


			"name" => array("valueOf" => 'name'),
			"description" => array("valueOf" => "type"),
			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "valueOf" => "geo"),
					),

		
	);

	public static $dataBinding_allPerson = array(



			"name" => array("valueOf" => 'name'),
			"description" => array("valueOf" => "username"),
			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "valueOf" => "geo"),
					),

		
	);

	public static $dataBinding_person = array(




			"name" => array("valueOf" => 'name'),
			"description" => array("valueOf" => "username"),
			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "valueOf" => "geo"),
					),

			);


	public static $dataBinding_city = array(

	

			"name" => array("valueOf" => 'name'),
			"description" => array("valueOf" => "structure"),
			"Point" => array(
							"type" => "coor",
							"coordinates" => 
							array( "valueOf" => "geo"),
					),

		


	);


}

?>