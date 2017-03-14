<?php 

class Translate {
	const FORMAT_SCHEMA = "schema";
	const FORMAT_PLP = "plp";
	const FORMAT_AS = "activityStream";
	const FORMAT_COMMUNECTER = "communecter";
	const FORMAT_RSS = "rss";
	const FORMAT_KML = "kml";
	const FORMAT_GEOJSON = "geojson";

	public static function convert($data,$bindMap)
	{
		$newData = array();
		foreach ($data as $keyID => $valueData) {
			if ( isset($valueData) ) {
				$newData[$keyID] = self::bindData($valueData,$bindMap);
			}
		}
		return $newData;
	}

	public static function convert_geojson($data,$bindMap)
	{
		$newData = array();
		foreach ($data as $keyID => $valueData) {
			if ( isset($valueData) ) {
				$newData[] = self::bindData($valueData,$bindMap);
			}
		}
		return $newData;
	}

	private static function bindData ( $data, $bindMap )
	{
		$newData = array();

		foreach ( $bindMap as $key => $bindPath ) 
		{

			if ( is_array( $bindPath ) && isset( $bindPath["valueOf"] ) ) 
			{
				/*if( $key == "@id")
					$newData["debug"] = strpos( $bindPath["valueOf"], ".");*/

				if( is_array( $bindPath["valueOf"] ))
				{
					//var_dump($bindPath["valueOf"]);
					//parse recursively for objects value types , ex links.projects
					if(isset($bindPath["object"]) )
					{
						//if dots are specified , we adapt the valueData map by focusing on a subpart of it
						//var_dump($bindPath["object"]);

						$currentValue = ( strpos( $bindPath["object"], "." ) > 0 ) ? self::getValueByPath( $bindPath["object"] ,$data ) : (!empty($data[$bindPath["object"]])?$data[$bindPath["object"]] : "" );
						
						//parse each entry of the list
						//var_dump(strpos( $bindPath["object"], "." ));
						if(!empty($currentValue)){
							$newData[$key] = array();
							foreach ( $currentValue as $dataKey => $dataValue) 
							{

								$refData = $dataValue;
								//if "collection" field  is set , we'll be fetching the data source of a reference object
								//we consider the key as the dataKey if no "refId" is set
								if( isset( $bindPath["collection"] ) ){
									if ( isset( $bindPath["refId"] ) ) 
										$dataKey = $bindPath["refId"];
									$refData = PHDB::findOne( $bindPath["collection"], array( "_id" => new MongoId( $dataKey ) ) );
								}
								$valByPath = self::bindData( $refData, $bindPath["valueOf"]);
								if(!empty($valByPath))
									array_push( $newData[$key] , $valByPath );
							}
						}
					} 
					//parse recursively for array value types, ex : address
					else if( isset($bindPath["parentKey"]) && isset( $data[ $bindPath["parentKey"] ] ) ){
						$valByPath = self::bindData( $data[ $bindPath["parentKey"] ], $bindPath["valueOf"] );
						if(!empty($valByPath))
							$newData[$key] = $valByPath;
						//resulting array has more than one level 
					}
					else{
						$valByPath = self::checkAndGetArray(self::bindData( $data, $bindPath["valueOf"]));

						if(!empty($valByPath))
							$newData[$key] = $valByPath;
					}
						
				} 
				else if( strpos( $bindPath["valueOf"], ".") > 0 )
				{
					//the value is fetched deeply in the source data map
					$valByPath = self::getValueByPath( $bindPath["valueOf"] ,$data );
					if(!empty($valByPath))
						$newData[$key] = $valByPath;
				}
				else if( isset( $data[ $bindPath[ "valueOf" ] ] )  )
				{
					//otherwise simply get the value of the requested element
					$valByPath = $data[ $bindPath["valueOf"] ];
					if(!empty($valByPath))
						$newData[$key] = $valByPath;
				}

			}  else if( is_array( $bindPath )){
				// there can be a first level with a simple key value
				// but can have following more than a single level 
				$valByPath = self::bindData( $data, $bindPath ) ;

				if(!empty($valByPath))
						$newData[$key] = $valByPath;
			}	
			else
				// otherwise it's just a simple label element 
				$newData[$key] = $bindPath;

			//post processing once the data has been fetched
			

			if( isset($newData[$key]) && ( isset( $bindPath["type"] ) || isset( $bindPath["prefix"] ) || isset( $bindPath["suffix"] ) ) ) 
				$newData[$key] = self::formatValueByType( $newData[$key] , $bindPath );			
		}

		return $newData;
	}


	private static function getValueByPath( $path , $currentValue ){
		//The value is somewhere in an array position is definied in a json syntax
		//explode dot seperators
		$path = explode(".", $path);
		//follow path until the leaf value
		foreach ($path as $pathKey) 
		{	
			if(!empty($currentValue[ $pathKey ])){
				if( is_object($currentValue[ $pathKey ]) && get_class( $currentValue[ $pathKey ] ) == "MongoId" ){
					$currentValue = (string)$currentValue[ $pathKey ];
					break;
				} 
				else
					$currentValue = $currentValue[ $pathKey ];
			}else{
				$currentValue = "" ;
			}
			
		}
		return $currentValue;
	}

	private static function formatValueByType($val, $bindPath ){	
		//prefix and suffix can be added to anything
		$prefix = ( isset( $bindPath["prefix"] ) ) ? $bindPath["prefix"] : "";
		$suffix = ( isset( $bindPath["suffix"] ) ) ? $bindPath["suffix"] : "";
		$outsite = ( isset( $bindPath["outsite"] ) ) ? $bindPath["outsite"] : null;
		//var_dump($val);
		if( isset( $bindPath["type"] ) && $bindPath["type"] == "url" )
		{	
			$val = $prefix.$val.$suffix ;
			if(empty($outsite)){
				$server = ((isset($_SERVER['HTTPS']) AND (!empty($_SERVER['HTTPS'])) AND strtolower($_SERVER['HTTPS'])!='off') ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];
				$val = $server.Yii::app()->createUrl($val);
			}		
			//$val = $server.Yii::app()->createUrl(Yii::app()->controller->module->id.$prefix.$val.$suffix);
		}
		else if( isset( $bindPath["type"] ) && $bindPath["type"] == "urlOsm" )
		{
			$val = $prefix.$val["latitude"]."/".$val["longitude"].$suffix;
		} 
		else if ( isset($bindPath["type"]) && $bindPath["type"] == "date")
		{
			
			//$datetime = date_create($val->pubDate);
			//$val = date_format($datetime, 'd M Y H\hi' );

			$val = date('D, d M Y H:i:s O',$val->sec);	
			
				 
		} 

		elseif (isset($bindPath["type"]) && $bindPath["type"] == "coor_news") {

			if ((isset($val["coordinates"]["cities"]["0"]["geo"]["latitude"])) && (isset($val["coordinates"]["cities"]["0"]["geo"]["longitude"]))) 
			{
				// var_dump($val["coordinates"]["cities"]["0"]["geo"]["latitude"]);

				$latitude = $val["coordinates"]["cities"]["0"]["geo"]["latitude"];
				$longitude = $val["coordinates"]["cities"]["0"]["geo"]["longitude"];

				$coor = $longitude.','.$latitude;

				$val['coordinates'] = $coor;
				unset($val["type"]);
		
			} 
			elseif ((!isset($val["coordinates"]["cities"]["0"]["geo"]["latitude"])) || (!isset($val["coordinates"]["cities"]["0"]["geo"]["longitude"]))) {

					// var_dump($val);
					$val['coordinates'] = '0,0';
					
					unset($val["type"]);


			}
		}			

		elseif (isset($bindPath["type"]) && $bindPath["type"] == "coor_orga") {


			if (isset($val["coordinates1"])) {


				$latitude = $val["coordinates1"]["coordinates"]["0"];
			 	$longitude = $val["coordinates1"]["coordinates"]["1"];

			 	$coor = $latitude.','.$longitude;
				$val['coordinates1'] = $coor;

				$val["coordinates"] = $val["coordinates1"];
				unset($val["coordinates1"]);

				unset($val["type"]);
		 	
			} 
			elseif (!isset($val["coordinates"])) {

					$val['coordinates'] = '0,0';
					
					unset($val["type"]);


			}

			
		}


		elseif (isset($bindPath["type"]) && $bindPath["type"] == "coor") {


			if ((isset($val["coordinates"]["latitude"])) && (isset($val["coordinates"]["longitude"]))) {

				$latitude = $val["coordinates"]["latitude"];
			 	$longitude = $val["coordinates"]["longitude"];

			 	$coor = $longitude.','.$latitude;
				$val['coordinates'] = $coor;

				unset($val["type"]);
				
			} elseif ((!isset($val["coordinates"]["latitude"])) || (!isset($val["coordinates"]["longitude"]))) {

					$val['coordinates'] = '0,0';
					
					unset($val["type"]);


			}
			
		}

		elseif (isset($bindPath["type"]) && $bindPath["type"] == "Point_news") {

			if ((isset($val["coordinates"]["cities"]["0"]["geo"]["latitude"])) && (isset($val["coordinates"]["cities"]["0"]["geo"]["longitude"]))) 
			{

				// var_dump($val);
				$latitude = $val["coordinates"]["cities"]["0"]["geo"]["latitude"];
				$longitude = $val["coordinates"]["cities"]["0"]["geo"]["longitude"];

				$latitude = floatval($latitude);
				$longitude = floatval($longitude);


				$val["coordinates"] = array();
				array_push($val["coordinates"], $longitude);				
				array_push($val["coordinates"], $latitude);


			} elseif ((!isset($val["coordinates"]["cities"]["0"]["geo"]["latitude"])) && (!isset($val["coordinates"]["cities"]["0"]["geo"]["longitude"]))) 
			{

				$val["coordinates"] = array();
				array_push($val["coordinates"], 0, 0);

			}


			$val["type"] = "Point";

		}
		elseif (isset($bindPath["type"]) && $bindPath["type"] == "Point_orga") {


			if (isset($val["coordinates1"])) {


				$latitude = $val["coordinates1"]["coordinates"]["0"];
			 	$longitude = $val["coordinates1"]["coordinates"]["1"];

			 	$coor = $latitude.','.$longitude;
				$val['coordinates1'] = $coor;

				$val["coordinates"] = $val["coordinates1"];
				unset($val["coordinates1"]);

				unset($val["type"]);
		 	
			} 
			elseif (!isset($val["coordinates"])) {

					$val["coordinates"] = array();
					array_push($val["coordinates"], 0, 0);

			}

			
		}


		elseif (isset($bindPath["type"]) && $bindPath["type"] == "Point") {


			if ((isset($val["coordinates"]["latitude"])) && (isset($val["coordinates"]["longitude"]))) {

				$latitude = $val["coordinates"]["latitude"];
			 	$longitude = $val["coordinates"]["longitude"];

			 	$latitude = floatval($latitude);
				$longitude = floatval($longitude);

				$val["coordinates"] = array();
				array_push($val["coordinates"], $longitude);				
				array_push($val["coordinates"], $latitude);

			 	
				
			} elseif ((!isset($val["coordinates"]["latitude"])) || (!isset($val["coordinates"]["longitude"]))) {

				$val["coordinates"] = array();
				array_push($val["coordinates"], 0, 0);


			}
			
		}

		elseif (isset($bindPath["type"]) && $bindPath["type"] == "properties") {

			$val["name"] = $val["0"]["prop0"];
			unset($val["0"]);
			unset($val["type"]);
			
		}
		
	
		else if (isset($bindPath["type"]) && $bindPath["type"] == "title" /*&& isset($bindPath["verb"]["valueOf"])*/) 
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
		else if (isset($bindPath["type"]) && $bindPath["type"] == "description_kml") {
			if (isset($val["text"])) {
				$val = $val["text"];
			} else {
				$val = "Pas de description pour cette news";
			}
		}

		else if (isset($bindPath["type"]) && $bindPath["type"] == "description") {
				//var_dump($val);
			if (isset($val["text"])) {
				$val = $val["text"];
				//var_dump($val);
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
		
		else if( isset( $bindPath["prefix"] ) || isset( $bindPath["suffix"] ) )
		{
			$val = $prefix.$val.$suffix;
		}
	
		return $val;
	}


	private static function checkAndGetArray($array){	
		$val = $array ;
		if(count($array) == 0 || (count($array) == 1 && !empty($array["@type"])))
			$val = null ;

		return $val;
	}


	public static function pastTime($date,$type, $timezone=null) {

		date_default_timezone_set("UTC");

		if($type == "timestamp") {
	        $date2 = $date; // depuis cette date
	    } elseif($type == "date") {
	        $date2 = strtotime($date); // depuis cette date
	    } else {
	        return "Non reconnu";
	    }

	    if(isset($timezone) && $timezone != "" && date_default_timezone_get()!=$timezone){
			date_default_timezone_set($timezone); //'Pacific/Noumea'
		}

	    $Ecart = time()-$date2;
	    $Annees = date('Y',$Ecart)-1970;
	    $Mois = date('m',$Ecart)-1;
	    $Jours = date('d',$Ecart)-1;
	    $Heures = date('H',$Ecart)-1;
	    $Minutes = date('i',$Ecart);
	    $Secondes = date('s',$Ecart);
	    if($Annees > 0) {
	        return "il y a ".$Annees." an".($Annees>1?"s":"")." et ".$Jours." jour".($Jours>1?"s":""); // on indique les jours avec les année pour être un peu plus précis
	    }
	    if($Mois > 0) {
	        return "il y a ".$Mois." mois et ".$Jours." jour".($Jours>1?"s":""); // on indique les jours aussi
	    }
	    if($Jours > 0) {
	        return "il y a ".$Jours." jour".($Jours>1?"s":"");
	    }
	    if($Heures > 0) {
	        return "il y a ".$Heures." heure".($Heures>1?"s":"");
	    }
	    if($Minutes > 0) {
	        return "il y a ".$Minutes." minute".($Minutes>1?"s":"");
	    }
	    if($Secondes > 0) {
	        return "il y a ".$Secondes." seconde".($Secondes>1?"s":"");
	    }
	}
}