<?php 

class Thing {
	//TODO changer collection things en datas (dans mongodb dev aussi)
	//TODO : utilisé un formulaire avec device et adresse mac pour compléter les métadatas

	const COLLECTION = "metadata";
	const CONTROLLER = "thing";
	const COLLECTION_DATA = "data";
	const URL_API_SC = "https://api.smartcitizen.me/v0"; // vérifier que l'url de l'api est à jour
	const SCK_TYPE = 'smartCitizen';
	//autorisation doit etre changé pour le compte de fablab.re
	const AUTHORIZATION = "beb94bb79a163a9d01a45a1b869874b93067788e65de06b8b53de7ccda5df3ef"; //compte DanZal

	//public static $types = array ();

	public static $dataBinding = array (
	    "type" 	=> array("name" => "type"),//"smartCitizen" SCK_TYPE
	    "temp" 	=> array("name" => "temp"),
	    "hum"	=> array("name" => "hum"),
	    "light"	=> array("name" => "light"),
	    "bat"	=> array("name" => "bat"),
	    "panel"	=> array("name" => "panel"),
	    "co"	=> array("name" => "co"),
	    "no2"	=> array("name" => "no2"),
	    "noise"	=> array("name" => "noise"),
	    "nets"	=> array("name" => "nets"),
	    "timestamp"	=> array("name" =>"timestamp", "rules" => array("required")),
	    "boardId" 	=> array("name" =>"macId"),	
	    "namepoi" => array("name"=>"namepoi"),
	   	//"userId" => array("name"=>"userId"), 		//id for data in mongodb need if authentified user (admin or user) can update metadata
	    "macId" => array("name" => "macId"),
	    "deviceId" 	=> array("name"=> "deviceId"), 	//id for smartcitizen.me
		"version" 	=> array("name" => "sckVersion"),
		"kit" 	=> array("name"=> "kit"),
		"sensors" 	=> array("name"=> "sensors"),
		"address"	=> array("name" => "address"),
		"geo" 	=> array("name" => "geo", "rules" => array("required","geoValid")), //poi
	    "location" 	=> array("name" => "location"),
	    "sckUpdatedAt"	=>array("name"=>"sckUpdatedAt"),
	    "url"	=> array("name" =>"url"),
	    "status" 	=> array("name" => "status"), //in metadata updated for POI realy need?

	    "modified" 	=> array("name" => "modified"),
	    "updated" 	=> array("name" => "updated"),
	    "creator" 	=> array("name" => "creator"),
	    "created" 	=> array("name" => "created"),
	);

	//TODO : Prévoir l'edition forcer (ajout d'argument et de condition (passer outre la limite de temps $forceUpdate), ne pas utiliser un poi mais directement le deviceId SCK) par une post via le controller UpdateSckDevicesAction.php
	//ajouter les metadatas si le sck n'est pas en base 
	public static function setMetadata($poi,$forceUpdate=false,$deviceId=null,$macId=null){

		$wheremeta = array('type'=>'smartCitizen');

		$sckurl=$poi['urls'][0];
		$deviceId = self::getSCKDeviceIdByPoiUrl($sckurl); //TODO remplacer par un argument direct obtenue par le formulaire
		//echo $deviceId;
		$wheremeta['deviceId']=$deviceId;
		$deviceMetadata = self::getSCKDevice($wheremeta);
		$tLReadings=$deviceMetadata['timestamp'];
		$gmttime=gmdate('Y-m-d\TH');
		
		if(preg_match("/".$gmttime."/i",$tLReadings)!=1 || $forceUpdate==true){
			$partReadings = self::getLastedReadViaAPI($deviceId);
			$partReadings['url']=$sckurl;
			$partReadings['namepoi']=$poi['name'];
			$address = $poi['address'];
			$geo=$poi['geo'];
			$toSave=array();
			$toSave = self::fillSmartCitizenMetadata($partReadings,$address,$geo);
			if(!empty($deviceMetadata)){
				$toSave['id']=$deviceMetadata['_id'];
			} else {
				$toSave['deviceId'] = $deviceId;
			}
			return $toSave;
		}
	}

	public static function updateMetadatas($pois=null){
		
		$sck=array(); //contient tous les metadata à jour
		$res=array(); //resultat de Element::save
		if(empty($pois)){
			$pois = self::getSCKInPoiByCountry();
		}
		$elementAlreadyUpdate= 0;
		foreach ($pois as $poi) {
			$toUpSave = self::setMetadata($poi);

			if(empty($toUpSave)){ $elementAlreadyUpdate++; }
			  else{	$res[] = Element::save($toUpSave); } 
		}

		if($elementAlreadyUpdate>0){
			$res['sckMetadata'] = "There are $elementAlreadyUpdate element already up to date (in last hour)";}
		return $res;
	}

	//chercher les sck enregistrer dans les pois dans la base de données CO
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

	//todo: vision sur carte, et api pour les données
	/*
	//utilise pour chercher l'_id dans metadata (si addresse mac présent aussi ?) 
	public static function getDBIdSCKDevice($deviceId=null){
		$where=array("type"=>self::SCK_TYPE);

		if(!empty($deviceId)){
			$where["deviceId"]=$deviceId;
		}
		$metadatasId = self::getSCKDevices($where,array('_id'));
		return $metadatasId;
	}*/

	public static function getSCKDevicesByCountryAndCP($country="RE", $cp="97490", $fields=null ){

		$where=array("type"=>self::SCK_TYPE);

		$where['address.addressCountry']=$country;
//$cp="0" pas de codepostale dans where. prend tous les sck du pays
		if($cp!="0"){$where['address.postalCode']=$cp;}

		$SCKDevices = self::getSCKDevices($where,$fields);
		return $SCKDevices;
	}

	public static function getSCKDevice($where=array("type"=>self::SCK_TYPE), $fields=null){
		//$where=array("type"=>"smartCitizen");
		$SCKDevice = PHDB::findOne(self::COLLECTION, $where,$fields);
		return $SCKDevice;
	}

	public static function getSCKDevices($where=array("type"=>self::SCK_TYPE), $fields=null){
		//$where=array("type"=>"smartCitizen");
		$SCKDevices = PHDB::find(self::COLLECTION, $where,$fields);
		return $SCKDevices;
	}
	/*
	private static function getDescriptionKitsViaAPI(){
		$kits=json_decode(file_get_contents(self::URL_API_SC."/kits"),true);

	}*/

	/* Par l'api pas de conversion à faire sur value (convertis par smartcitizen.me), 
	valeur brute raw_value conversion nécessaire  */
	public static function getLastedReadViaAPI($deviceId=4162){  //4162 pour test
		if(is_string($deviceId)){
			settype($deviceId, "integer");
		}

		$lastReadDevice = json_decode(file_get_contents(self::URL_API_SC."/devices/".$deviceId."?access_token=".self::AUTHORIZATION),true);
		
		$partReadings =array();
		
		if( $lastReadDevice['id'] == $deviceId ){
			// for readings , TODO : parse pour avoir la date
			//location : latitude longitude geohash city country_code country exposure
			//sensors[] chaque item : id name description unit value raw_value prev_value prev_raw_value ancestry created_at updated_at
			$partReadings['sckUpdatedAt'] = $lastReadDevice["updated_at"];
			$partReadings['timestamp'] = $lastReadDevice["data"]["recorded_at"]; 
			$partReadings['location'] = $lastReadDevice["data"]["location"]; 
			$partReadings['sensors'] = $lastReadDevice["data"]["sensors"];
			$partReadings['macId'] = $lastReadDevice["mac_address"];
			$partReadings['kit'] = $lastReadDevice["kit"];
			unset($partReadings['location']['ip']);
			unset($partReadings['location']['elevation']);

		}
		return $partReadings;

	}

	public static function getLastestRecordsInDB($macId=null,$where=array("type"=>self::SCK_TYPE),$sort=array("created"=>-1),$limit=2){
		$lastRecords = array();
		if(!empty($macId)&&(strlen($macId)<=17)){
			$where["boardId"] = $macId;
			$lastRecords = PHDB::findAndSort(self::COLLECTION_DATA,$where,$sort,$limit); 
		}
		return $lastRecords;
	}

	public static function getBoardReadingsByTime($boardId,$date=null,$hour=null){

		$where= array("type"=>self::SCK_TYPE);
		//date example :"2017-02-27"
		if(!empty($date)){
			$time=$date;
			if(!empty($hour)&&($hour>=0 && $hour<=23)){ 
				$time=$time." ".$hour;
			}
		} else { 
			$time=gmdate('Y-m-d');
		}
		$queryTimestamp[] = new MongoRegex("/".$time."/i");
		$where["timestamp"] = array("$in"=>$queryTimestamp);
		$sort = array("timestamp"=>1);
		$limit = null;

		$dataInDB = self::getLastestRecordsInDB($boardId,$where,$sort,$limit);

		//print_r($dataInDB);

		$data=array();
		foreach ($dataInDB as $rawData) {
			$data[]=SCKSensorData::SCK11Convert($rawData);
			# code...
		}
		return $data;
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

	public static function fillSmartCitizenMetadata($partReadings,$address,$geo){
		$mdata = array();
		$mdata['collection']=self::COLLECTION;
		$mdata['type'] = self::SCK_TYPE;
		$mdata['key'] = 'thing';
		$mdata['boardId'] = $partReadings['macId'];
		if($partReadings['macId'] !="[FILTERED]"){
			unset($partReadings['macId']);
		}
		
		$mdata['address']= $address;
		$mdata['geo'] = $geo;
		return array_merge($mdata,$partReadings);

	}

	private static function getSCKDeviceIdByPoiUrl($sckUrl){
	
		$eUrl= explode("/",$sckUrl);
		$deviceid = $eUrl[(count($eUrl)-1)];
		return $deviceid;
	}



}





?>