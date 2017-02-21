<?php 

class Thing {
	//TODO changer collection things en datas (dans mongodb dev aussi)

	const COLLECTION = "metadatas";
	const CONTROLLER = "thing";
	const COLLECTION_DATA = "datas";
	const URL_API_SC = "https://api.smartcitizen.me/v0"; // vérifier que l'url de l'api est à jour
	//autorisation doit etre changé pour le compte de fablab.re
	const AUTHORIZATION = "beb94bb79a163a9d01a45a1b869874b93067788e65de06b8b53de7ccda5df3ef"; //compte DanZal

	

	//public static $types = array ();

	public static $dataBinding = array (
	    "type" => array("name" => "type"),//"smartCitizen"
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
	    "boardId" => array("name"=>"macId", "rules" => array("required")),
	    "userId" => array("name"=>"userId"), 		//id for data in mongodb
	    "deviceId" => array("name"=> "deviceId"), 	//id for smartcitizen.me
		"version" => array("name" => "sckVersion"),
		"sensorsIds" => array("name"=> "sensors"),
		"geo" => array("name" => "geo", "rules" => array("required","geoValid")), //poi
		//"geoPosition" => array("name" => "geoPosition", "rules" => array("required","geoPositionValid")),
		"geohash" => array("name" => "geohash" ),
	    "location" => array("name" => "location"),
	    "countryCode" => array("name" =>"countryCode"),
	    "country" => array("name" =>"country"),
	    "city"=> array("name"=> "city"),
	    "exposure" => array("name" => "exposure"),
	    
	    "modified" => array("name" => "modified"),
	    "updated" => array("name" => "updated"),
	    "creator" => array("name" => "creator"),
	    "created" => array("name" => "created"),
	);

	//ajouter les metadatas si le sck n'est pas en base 
	public static function insert($metadatas){
		//vérifier que le sck n'est pas dans la base
		//get
		if (! )



	}

	//vérifier que la données est à jours (lire la date de modification de la métadatas)
	public static function updateMetadata($macId,$deviceUpdatedDate,$location){
		


	}

	//chercher les sck enregistrer dans les poi dans la base de données basé sur l'url du kits
	public static function getNewSCKinPOI(){
		//PHDB::find


		
	}


	public static function getSCKDevices(){
		/*$numberSCKDevices = PHDB::count(self::COLLECTION, array("type"=>"smartCitizen"));
	
		return $numberSCKDevices;*/

	}



	//Par l'api pas de conversion à faire sur value (convertis par smartcitizen), valeur brute raw_value conversion nécessaire
	public static function getLastedReadViaAPI($deviceId=4162){  //4162 pour test

		$lastReadDevice = json_decode(file_get_contents(URL_API_SC."/devices/".$deviceId."?access_token=".AUTHORIZATION),true);

		$res =array();

		if($lastReadDevice["id"]==$deviceId){
			$timestamp = $lastReadDevice["data"]["recorded_at"]; // parse pour avoir la date
			$location= $lastReadDevice["data"]["location"]; //latitude longitude geohash city country_code country
			$sensors = $lastReadDevice["data"]["sensors"];//[] chaque item : id name description unit value raw_value prev_value prev_raw_value ancestry created_at updated_at
			$deviceUpdatedDate = $lastReadDevice["updated_at"];
			$macId = $lastReadDevice["mac_address"];
			if ($macId != "[FILTERED]"){
				updateMetadata($macId,$deviceUpdatedDate,$location) ;
			} else {
				echo "(l'adresse mac du device $deviceId n'est pas afficher.)"; 
			}
			$res  = array('macId' => $macId, 'updatedAt'=> $deviceUpdatedDate, 'location'=> $location,'timestamp' => $timestamp, 'sensors'=>$sensors);


		}
 		return $res;

	}

	public static function getLastestRecordsInDB($macId){
		$where = 
		$lastRecords = PHDB::find()


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
                
        $dataThing['key']='thing';
        $dataThing['collection']='thing';
        $dataThing['type']='smartCitizen';
        $dataThing['boardId']=$headers['X-SmartCitizenMacADDR'];
        $dataThing['version']=$headers['X-SmartCitizenVersion'];
        return array_merge($dataThing, $datapoints);
	}





}
?>