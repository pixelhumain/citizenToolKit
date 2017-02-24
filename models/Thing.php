<?php 

class Thing {
	//TODO changer collection things en datas (dans mongodb dev aussi)
	//TODO : utilisé un formulaire avec device et adresse mac pour compléter les métadatas

	const COLLECTION = "metadatas";
	const CONTROLLER = "thing";
	const COLLECTION_DATA = "data";
	const URL_API_SC = "https://api.smartcitizen.me/v0"; // vérifier que l'url de l'api est à jour
	const SCK_TYPE = 'smartCitizen';
	//autorisation doit etre changé pour le compte de fablab.re
	const AUTHORIZATION = "beb94bb79a163a9d01a45a1b869874b93067788e65de06b8b53de7ccda5df3ef"; //compte DanZal

	//public static $types = array ();

	public static $dataBinding = array (
	    "type" => array("name" => "type"),//"smartCitizen" SCK_TYPE
	    "temp"=>array("name"=>"temp"),
	    "hum"=>array("name"=>"hum"),
	    "light"=>array("name"=>"light"),
	    "bat"=>array("name"=>"bat"),
	    "panel"=>array("name"=>"panel"),
	    "co"=>array("name"=>"co"),
	    "no2"=>array("name"=>"no2"),
	    "noise"=>array("name"=>"noise"),
	    "nets"=>array("name"=>"nets"),
	    "timestamp"=>array("name"=>"timestamp", "rules" => array("required")),
	    "boardId" => array("name"=>"macId", "rules" => array("required")), //mettre a jours l'addresse mac par une requete post
	    //"userId" => array("name"=>"userId"), 		//id for data in mongodb
	    "deviceId" => array("name"=> "deviceId"), 	//id for smartcitizen.me
		"version" => array("name" => "sckVersion"),
		"sensors" => array("name"=> "sensors"),
		"geo" => array("name" => "geo", "rules" => array("required","geoValid")), //poi
		//"geoPosition" => array("name" => "geoPosition", "rules" => array("required","geoPositionValid")),
		//"geohash" => array("name" => "geohash" ), //smartcitizen.me
	    "location" => array("name" => "location"),
	    //"countryCode" => array("name" =>"countryCode"),
	    //"country" => array("name" =>"country"),
	    //"city"=> array("name"=> "city"),
	    "sckUpdatedAt"=>array("name"=>"sckUpdatedAt"),
	    //"exposure" => array("name" => "exposure"),
	    "status" => array("name" => "status"), //in metadata updated for POI 
	    
	    "modified" => array("name" => "modified"),
	    "updated" => array("name" => "updated"),
	    "creator" => array("name" => "creator"),
	    "created" => array("name" => "created"),
	);

	//ajouter les metadatas si le sck n'est pas en base 
	public static function setMetadata($poi,$deviceId=null,$macId=null){

			$wheremeta = array('type'=>'smartCitizen');
			
			$toSave=array();

			$sckurl=$poi['urls'][0];
			$deviceId=  self::getSCKDeviceIdByPoiUrl($sckurl);
			//echo $deviceId;
			$wheremeta['deviceId']=$deviceId;
			$deviceMetadata = self::getSCKDevice($wheremeta);

			$partReadings = self::getLastedReadViaAPI($deviceId);
			$geo=$poi['geo'];
			$toSave = self::fillSmartCitizenMetadata($partReadings,$geo);
			if(!empty($deviceMetadata)){
				$toSave['id']=$deviceMetadata['_id'];
			} else {
				$toSave['deviceId'] = $deviceId;
			}
			return $toSave;
	}

	//vérifier que la données est à jours (lire la date de modification de la métadatas)
	public static function updateMetadatas($pois=null){
		
		$sck=array(); //contient tous les metadata à jour
		$res=array(); //resultat de Element::save
		if(empty($pois)){
			$pois = self::getSCKInPoiByCountry();
		}
		
		foreach ($pois as $poi) {
			$toUpSave= self::setMetadata($poi);

			$res[] = Element::save($toUpSave);
			$sck[]=$toUpSave;
			 
		}
		echo Rest::json( $res );

		return $sck;

	}

	//chercher les sck enregistrer dans les poi dans la base de données
	public static function getSCKInPoiByCountry($country="RE",$fields=null){

		$where = array('type' => array('$exists'=>1));
		//poi : addressCountry
		//mongo regex pour le code postal
		$queryUrls[] = new MongoRegex("/".self::SCK_TYPE."/i");
		$where['address.addressCountry'] = $country;
		$where['urls'] = array('$in'=> $queryUrls);

		$pois = PHDB::find(Poi::COLLECTION, $where, $fields);
		return $pois;
	}

	/* TODO : Faire avec deviceId renseigner dans le formulaire poi type smartcitizen
	*/

	public static function getSCKDeviceIdByPoiUrl($sckUrl){
	
		$eUrl= explode("/",$sckUrl);
		$deviceid = $eUrl[(count($eUrl)-1)];
		return $deviceid;
	}

	//todo: vision sur carte, et api pour les données
	/*
	//utilise pour chercher l'_id dans metadata (si addresse mac présent aussi ?) 
	public static function getDBIdSCKDevice($deviceId=null){
		$where=array("type"=>"smartCitizen");

		if(!empty($deviceId)){
			$where["deviceId"]=$deviceId;
		}
		$metadatasId = self::getSCKDevices($where,array('_id'));
		return $metadatasId;
	}*/

	public static function getSCKDevice($where=array("type"=>"smartCitizen"), $fields=null){
		//$where=array("type"=>"smartCitizen");
		$SCKDevices = PHDB::findOne(self::COLLECTION, $where,$fields);
		return $SCKDevices;
	}

	/* Par l'api pas de conversion à faire sur value (convertis par smartcitizen.me), 
	valeur brute raw_value conversion nécessaire  */
	public static function getLastedReadViaAPI($deviceId=4162){  //4162 pour test
		if(is_string($deviceId)){
			settype($deviceId, "integer");
		}

		$lastReadDevice = json_decode(file_get_contents(self::URL_API_SC."/devices/".$deviceId."?access_token=".self::AUTHORIZATION),true);
		
		$partReadings =array();
		
		if( $lastReadDevice["id"] == $deviceId ){
			// for readings , TODO : parse pour avoir la date
			//location : latitude longitude geohash city country_code country exposure
			//sensors[] chaque item : id name description unit value raw_value prev_value prev_raw_value ancestry created_at updated_at
			$partReadings['sckUpdatedAt'] = $lastReadDevice["updated_at"];
			$partReadings['timestamp'] = $lastReadDevice["data"]["recorded_at"]; 
			$partReadings['location'] = $lastReadDevice["data"]["location"]; 
			$partReadings['sensors'] = $lastReadDevice["data"]["sensors"];
			$partReadings['macId'] = $lastReadDevice["mac_address"];
			unset($partReadings['location']['ip']);
			unset($partReadings['location']['elevation']);

		}
		return $partReadings;

	}

	public static function getLastestRecordsInDB($macId, $limit=2, $sort=array("created"=>-1)){

		$where = array('type' => array('$exists'=>1));
		$lastRecords = array();

		if(!empty($macId)&&(strlen($macId)<=17)){
			$queryMacId[] = new MongoRegex("/".$macId."/i");
			$where["type"] = self::SCK_TYPE;
			//$fields = array('boardId'=> $macId);
			$where["boardId"] = array('$in'=>$queryMacId);

			$lastRecords = PHDB::findAndSort(self::COLLECTION_DATA,$where,$sort,$limit); // TODO : faire une recherche de la derniere valeur
		}
		return $lastRecords;


	}

	public static function getSensorsReadings($boardId){

	}

	public static function getSensorReading($sensor,$boardId){


	}


	public static function getDateTime($bindMap) {
		//TODO regler le probleme $datetime qui n'est pas correctement converti
		//TODO gérer fuseau horaire
		
		$datetime = getdate();
		print_r($datetime);
		$resDateTime = Translate::convert($datetime, $bindMap);

		return $resDateTime; 

	}
	
	public static function fillSmartCitizenData($headers){
		$dataThing = array();

		$data = $headers['X-SmartCitizenData']; 
        $datasub = substr($data, 1, (strlen($data)-2));
        $datapoints = json_decode($datasub,true);
        
        $dataThing['collection']=self::COLLECTION_DATA;        
        $dataThing['key'] = 'thing';
        $dataThing['type'] 	 = self::SCK_TYPE;
        $dataThing['boardId']=$headers['X-SmartCitizenMacADDR'];
        $dataThing['version']=$headers['X-SmartCitizenVersion'];
        return array_merge($dataThing, $datapoints);
	}

	public static function fillSmartCitizenMetadata($partReadings,$geo){
		$mdata = array();
		$mdata['collection']=self::COLLECTION;
		$mdata['type'] = self::SCK_TYPE;
		$mdata['key'] = 'thing';
		$mdata['boardId'] = $partReadings['macId'];
		if($partReadings['macId'] !="[FILTERED]"){
			unset($partReadings['macId']);
		}
		$mdata['geo'] = $geo;
		return array_merge($mdata,$partReadings);

	}





}
?>