<?php 

class Thing {
	//TODO 

	const COLLECTION = "thing";
	const CONTROLLER = "thing";

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
	    "userId" => array("name"=>"userId"),
	    "version" => array("name" => "sckVersion"),
	    "latitude" => array("name" => "latitude"),
	    "longitude" => array("name" => "longitude"),

	    //"location" => array("name" => "" ),
	    
	    "modified" => array("name" => "modified"),
	    "updated" => array("name" => "updated"),
	    "creator" => array("name" => "creator"),
	    "created" => array("name" => "created"),
	);

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

	public static function countEntriesSCKDevices(){
		$numberSCKDevices = PHDB::count(self::COLLECTION, array("type"=>"smartCitizen"));
	
		return $numberSCKDevices;

	}

	public static function getLastedRead($deviceId){

			
	}

	public static function getSensorReading($sensor,$boardId){


	}

}
?>