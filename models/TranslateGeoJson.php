<?php

class TranslateGeoJson {
	
//Exemple type d'un fichier GeoJSON

// 	{
//   "type": "FeatureCollection",
//   "features": [
//     {
//       "type": "Feature",
//       "geometry": {
//         "type": "Point",
//         "coordinates": [
//           102,
//           0.5
//         ]
//       },
//       "properties": {
//         "prop0": "value0"
//       }
//     },
    
//      {
//       "type": "Feature",
//       "geometry": {
//         "type": "Point",
//         "coordinates": [
//           55,
//           0.5
//         ]
//       },
//       "properties": {
//         "prop0": "value0"
//       }
//     }
   
//   ]
// }
	public static $dataBinding_news = array(


			"type" => "Feature",
			"geometry" => 
						array ("type" => "Point_news",		
								"coordinates" => array ("valueOf" => "scope"),
				),
			"properties" => array("prop0" => "value0"),
	);

	

	public static $dataBinding_allOrganization = array(


			
			"type" => "Feature",
			"geometry" => 
						array ("type" => "Point",		
								"coordinates" => array ("valueOf" => "geo"),
				),
			"properties" => array("type" => "properties",
				array("prop0" => array("valueOf" => "name")),),

		
	);

	public static $dataBinding_organization = array(


			
			"type" => "Feature",
			"geometry" => 
						array ("type" => "Point",		
								"coordinates" => array ("valueOf" => "geo"),
				),
			"properties" => array("type" => "properties",
				array("prop0" => array("valueOf" => "name")),),
	);

	public static $dataBinding_allEvent  = array(

			
			"type" => "Feature",
			"geometry" => 
						array ("type" => "Point",		
								"coordinates" => array ("valueOf" => "geo"),
				),
			"properties" => array("type" => "properties",
				array("prop0" => array("valueOf" => "name")),),
		
		);
	public static $dataBinding_event = array(

			"type" => "Feature",
			"geometry" => 
						array ("type" => "Point",		
								"coordinates" => array ("valueOf" => "geo"),
				),
			"properties" => array("type" => "properties",
				array("prop0" => array("valueOf" => "name")),),

		
	);

	public static $dataBinding_allProject  = array(


			"type" => "Feature",
			"geometry" => 
						array ("type" => "Point",		
								"coordinates" => array ("valueOf" => "geo"),
				),
			"properties" => array("type" => "properties",
				array("prop0" => array("valueOf" => "name")),),

	);

	public static $dataBinding_project = array(


			"type" => "Feature",
			"geometry" => 
						array ("type" => "Point",		
								"coordinates" => array ("valueOf" => "geo"),
				),
			"properties" => array("type" => "properties",
				array("prop0" => array("valueOf" => "name")),),

		
	);

	public static $dataBinding_allPerson = array(


		"type" => "Feature",
			"geometry" => 
						array ("type" => "Point",		
								"coordinates" => array ("valueOf" => "geo"),
				),
			"properties" => array("type" => "properties",
				array("prop0" => array("valueOf" => "name")),),

		
	);

	public static $dataBinding_person = array(




			"type" => "Feature",
			"geometry" => 
						array ("type" => "Point",		
								"coordinates" => array ("valueOf" => "geo"),
				),
			"properties" => array("type" => "properties",
				array("prop0" => array("valueOf" => "name")),),

			);


	public static $dataBinding_city = array(

	

			"type" => "Feature",
			"geometry" => 
						array ("type" => "Point",		
								"coordinates" => array ("valueOf" => "geo"),
				),
			"properties" => array(
				"type" => "properties",
				array("prop0" => array("valueOf" => "name")),
				),

	);


}

?>