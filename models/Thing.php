<?php 

class Thing {
	//TODO changer collection things en datas (dans mongodb dev aussi)
	//TODO : utilisé un formulaire avec device et adresse mac pour compléter les métadatas

	const COLLECTION = "metadata";
	const CONTROLLER = "thing";
	const COLLECTION_DATA = "data";
	const URL_API_SC = "https://api.smartcitizen.me/v0"; // vérifier que l'url de l'api est à jour
	const SCK_TYPE = 'smartCitizen';
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
	    "name" => array("name"=>"name"),
	   	//"userId" => array("name"=>"userId"), 		//id for data in mongodb need if authentified user (admin or user) can update metadata
	    //"macId" => array("name" => "macId"),
	    "deviceId" 	=> array("name"=> "deviceId"), 	//id for smartcitizen.me
		"version" 	=> array("name" => "sckVersion"),
		"kit" 	=> array("name"=> "kit"),
		"sensors" 	=> array("name"=> "sensors"),
		"address"	=> array("name" => "address"),
		"geo" 	=> array("name" => "geo", "rules" => array("required","geoValid")), //poi
	    "location" 	=> array("name" => "location"),
	    "sckUpdatedAt"	=>array("name"=>"sckUpdatedAt"),
	    "url"		=> array("name" =>"url"),
	    "sckKits" 	=> array("name"=>"sckKits"), //api metadata
		"sckSensors" => array("name"=>"sckSensors"),//api metadata
		"sckMeasurements" => array("name"=>"sckMeasurements"),//api metadata
	    "status" 	=> array("name" => "status"), //in metadata updated for POI realy need?

	    "modified" 	=> array("name" => "modified"),
	    "updated" 	=> array("name" => "updated"),
	    "creator" 	=> array("name" => "creator"),
	    "created" 	=> array("name" => "created"),
	);

	private static $sckAPIPathMetadata = array("sckSensors" => "sensors", "sckMeasurements" => "measurements", "sckKits" => "kits");

	public static function updateSCKAPIMetadata($forceUpdate=true){
		
		$res=array(); //resultat de Element::save
		
		$wheremeta=array();
		$gmttime=gmdate('Y-m-d');

		$fields=array('modified','_id','updated');
		
		foreach (self::$sckAPIPathMetadata as $key => $value) {
			$wheremeta['type']=$key;
			
			$apisck = self::getSCKDeviceMdata($wheremeta,$fields);
		
			$intDayBeginning=strtotime($gmttime);

			$mdata=array();
			$mdata['collection']=self::COLLECTION;
			$mdata['type'] = $key;
			$mdata['key'] = 'thing';
			$sckAPIMetadata=json_decode(file_get_contents(self::URL_API_SC."/".$value),true);
			$mdata[$key]=$sckAPIMetadata;
			
			if($intDayBeginning >= $apisck['updated'] || $forceUpdate==true){
				if(!empty($apisck)) {
					$mdata['id']=$apisck['_id']; 
				}
				
				$res[]= Element::save($mdata);
			}
		}
		return $res;
	}

	//TODO : Prévoir l'edition forcer (ajout d'argument et de condition (passer outre la limite de temps $forceUpdate), ne pas utiliser un poi mais directement le deviceId SCK) par une post via le controller UpdateSckDevicesAction.php
	//ajouter les metadatas si le sck n'est pas en base 

	public static function setMetadata($poi=null,$atSC=null,$forceUpdate=false,$deviceId=null,$boardId=null){
		$gmttime=gmdate('Y-m-d\TH');

		/*if(!empty($deviceId) && is_string($deviceId)){ settype($deviceId, "integer"); } */
		
		if(empty($deviceId)){
			$sckurl=$poi['urls'][0];
			$deviceId = self::getSCKDeviceIdByPoiUrl($sckurl);
			$address = $poi['address'];
			$name = $poi['name'];
			$geo = $poi['geo'];
		}
		
		$wheremeta = array('type'=>'smartCitizen');
		$wheremeta['deviceId']=$deviceId;
		$deviceMetadata = self::getSCKDeviceMdata($wheremeta);
		$tLReadings=(isset($deviceMetadata['timestamp']) ? $deviceMetadata['timestamp'] : "2017-04-12" );

		if(preg_match("/".$gmttime."/i",$tLReadings)!=1 || $forceUpdate==true){

			if(empty($sckurl)){
				$sckurl = self::URL_API_SC."/".'$deviceId';
			}
		
			$partReadings = self::getLastedReadViaAPI($deviceId,$atSC);
			
			$partReadings['url']=$sckurl;

			if(!empty($boardId) && ($deviceMetadata['boardId']=='[FILTERED]' || !isset($deviceMetadata['boardId']))){
				$partReadings['$boardId']='$boardId';}
			if(!empty($partReadings) && isset($partReadings['boardId']) && isset($deviceMetadata['boardId'])){
			 if ( $deviceMetadata['boardId']!='[FILTERED]' && $partReadings['boardId']=='[FILTERED]'){
				unset($partReadings['boardId']);}
			}

			if(!isset($partReadings['name'])){
				$partReadings['name'] = (empty($name) ? '' : '$name' );}
			
			if(empty($geo)){ 
				$geo = array("@type"=>"GeoCoordinates", "latitude" 	=> $partReadings['data']['location']['latitude'], "longitude" => $partReadings['data']['location']['longitude']);
			}
			
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

	public static function updateMetadatas($pois=null,$atSC=null){
		
		$sck=array(); //contient tous les metadata à jour
		$res=array(); //resultat de Element::save
		if(empty($pois)){
			$pois = self::getSCKInPoiByCountry();
		}
		$elementAlreadyUpdate= 0;
		foreach ($pois as $poi) {
			$toUpSave = self::setMetadata($poi,$atSC,true);

			if(empty($toUpSave)){ $elementAlreadyUpdate++; }
			  else{	$res[] = Element::save($toUpSave); } 
		}

		if($elementAlreadyUpdate>0){
			$res['sckMetadata'] = "There are ".$elementAlreadyUpdate." element already up to date (in last hour)";}
		return $res;
	}

	public static function updateOneMetadata($deviceId,$boardId,$atSC=null){
		
		$res=array(); //resultat de Element::save
		
		$toUpSave = self::setMetadata(null,$atSC,true,$deviceId,$boardId);

		$res = Element::save($toUpSave);  
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

	public static function getSCKDevicesByCountryAndCP($country="RE", $cp="0", $fields=null){

		$where=array("type"=>self::SCK_TYPE);

		$where['address.addressCountry']=$country;
		//$cp="0" pas de codepostale dans where. prend tous les sck du pays
		if($cp!="0"){$where['address.postalCode']=$cp;}

		$SCKDevices = self::getSCKDevices($where,$fields);
		return $SCKDevices;
	}

	//metadata
	public static function getSCKDeviceMdata($where=array("type"=>self::SCK_TYPE), $fields=null){
		//$where=array("type"=>"smartCitizen");
		$SCKDevice = PHDB::findOne(self::COLLECTION, $where,$fields);
		return $SCKDevice;
	}

	public static function getSCKDevices($where=array("type"=>self::SCK_TYPE), $fields=null){
		//$where=array("type"=>"smartCitizen");
		$SCKDevices = PHDB::find(self::COLLECTION, $where,$fields);
		return $SCKDevices;
	}

	/*public static function getSCKDevicesBoardId(){

		$where=array("type"=>self::SCK_TYPE);
		//self::getSCKDevices
	}*/

	/* Par l'api pas de conversion à faire sur value (déjà convertis par smartcitizen.me), 
	valeur brute raw_value conversion nécessaire  */
	public static function getLastedReadViaAPI($deviceId=4162,$atSC=null){  //4162 pour test
		if(is_string($deviceId)){
			settype($deviceId, "integer");
		}
		$queryATSC = (empty($atSC))? "":"?access_token=".$atSC;
		$lastReadDevice = json_decode(file_get_contents(self::URL_API_SC."/devices/".$deviceId.$queryATSC),true);
		
		$partReadings =array();
		
		if( $lastReadDevice['id'] == $deviceId ){
			// for readings , TODO : parse pour avoir la date
			//location : latitude longitude geohash city country_code country exposure
			//sensors[] chaque item : id name description unit value raw_value prev_value prev_raw_value ancestry created_at updated_at
			$partReadings['sckUpdatedAt'] = $lastReadDevice["updated_at"];
			$partReadings['timestamp'] = $lastReadDevice["data"]["recorded_at"]; 
			$partReadings['location'] = $lastReadDevice["data"]["location"]; 
			$partReadings['sensors'] = $lastReadDevice["data"]["sensors"];
			$partReadings['boardId'] = $lastReadDevice["mac_address"];
			$partReadings['name'] = $lastReadDevice["name"];
			$partReadings['kit'] = $lastReadDevice["kit"];
			unset($partReadings['location']['ip']);
			unset($partReadings['location']['elevation']);

		}
		return $partReadings;

	}

	public static function getDistinctBoardId(){
		$where = array('boardId' => array('$exists'=>1));
		$where['type']= self::SCK_TYPE;
		
		$distinctedBoardId=PHDB::distinct(self::COLLECTION_DATA,'boardId', $where);
		return $distinctedBoardId;
	}

	public static function getDistinctDeviceId(){
		$where = array('deviceId' => array('$exists'=>1));
		$where['type']= self::SCK_TYPE;
		
		$distinctedDeviceId=PHDB::distinct(self::COLLECTION,'deviceId', $where);
		return $distinctedDeviceId;
	}

	public static function getLastestRecordsInDB($boardId=null,$where=array("type"=>self::SCK_TYPE),$sort=array("created"=>-1),$limit=1,$fields=null){
		$lastRecords = array();
		if(!empty($boardId)&&(strlen($boardId)==17)&& ($boardId!= "[FILTERED]" )){
			$where["boardId"] = $boardId;
			$lastRecords = PHDB::findAndSort(self::COLLECTION_DATA,$where,$sort,$limit,$fields);
		}
		return $lastRecords;
	}

	/* getConvertedRercord peu prendre dans la base la dernière valeur ou plusieurs enregistrement de la journées ou d'une heure particulière pour un boardId. si la date n'est pas renseigné c'est la date gmt qui est pris en compte. retourne un tableau avec les enregistements converti
	*/
	public static function getConvertedRercord($boardId,$lastest=false,$date=null,$hour=null){

		$where = array("type"=>self::SCK_TYPE);
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
		$where["timestamp"] = array('$in'=> $queryTimestamp);
		if($lastest==false){
			$sort = array("timestamp"=>1);
			$limit = null;
		} else {
			$sort = array("timestamp"=>-1);
			$limit=1;
		}

		$dataInDB = self::getLastestRecordsInDB($boardId,$where,$sort,$limit);

		$data=array();
		foreach ($dataInDB as $rawData) {
			$data[]=SCKSensorData::SCK11Convert($rawData);
		}
		return $data; 
	}
	
	public static function synthesizeSCKRecord($record, $synthA){
		
		$temp=array('min'=>(min($synthA['temp']['min'],$record['temp'])),'max'=>(max($synthA['temp']['max'],$record['temp'])));
		$hum=array('min'=>(min($synthA['hum']['min'],$record['hum'])),'max'=>(max($synthA['hum']['max'],$record['hum'])));
		$light=array('min'=>(min($synthA['light']['min'],$record['light'])),'max'=>(max($synthA['light']['max'],$record['light'])));
		$bat=array('min'=>(min($synthA['bat']['min'],$record['bat'])),'max'=>(max($synthA['bat']['max'],$record['bat'])));
		$panel=array('min'=>(min($synthA['panel']['min'],$record['panel'])),'max'=>(max($synthA['panel']['max'],$record['panel'])));
		$co=array('min'=>(min($synthA['co']['min'],$record['co'])),'max'=>(max($synthA['co']['max'],$record['co'])));
		$no2=array('min'=>(min($synthA['no2']['min'],$record['no2'])),'max'=>(max($synthA['no2']['max'],$record['no2'])));
		$noise=array('min'=>(min($synthA['noise']['min'],$record['noise'])),'max'=>(max($synthA['noise']['max'],$record['noise'])));
		$nets=array('min'=>(min($synthA['nets']['min'],$record['nets'])),'max'=>(max($synthA['nets']['max'],$record['nets'])));
		
		$synthA['temp']=$temp;
		$synthA['hum']=$hum;
		$synthA['light']=$light;
		$synthA['bat']=$bat;
		$synthA['panel']=$panel;
		$synthA['co']=$co;
		$synthA['no2']=$no2;
		$synthA['noise']=$noise;
		$synthA['nets']=$nets;

		return $synthA;
	}

	public static function getSCKAvgWithRollupPeriod($data,$rollup=60,$synthesize=false)
	{
		if($rollup>=1440){$rollup=1440;} //
		$rollupSec=$rollup*60;
		
		$avgData=array();
		$avgValue=array('temp'=>0,'hum'=>0,'light'=>0,'bat'=>0,'panel'=>0,'co' =>0,'no2' =>0,'noise' =>0, 'nets'=>0,'timestamp'=>0);
		
		if(!empty($data)){
		  $lenData=count($data);
		  $i=0;
		  $firstdata=reset($data);
		  $startRollup=strtotime($firstdata['timestamp']);  
		  $lastdata=end($data);
		  $endRollup=strtotime($lastdata['timestamp']);
		  if($synthesize==true){
		  	$synthA=array('temp'=>array('min'=>999999,'max'=>0),'hum'=>array('min'=>999999,'max'=>0),'light'=>array('min'=>999999,'max'=>0),'bat'=>array('min'=>999999,'max'=>0),'panel'=>array('min'=>999999,'max'=>0),'co' =>array('min'=>999999,'max'=>0),'no2' =>array('min'=>999999,'max'=>0),'noise' =>array('min'=>999999,'max'=>0), 'nets'=>array('min'=>999999,'max'=>0));
		  	//$mean=array_sum($data)/$lenData;
		  	$carry = function($xi){ return($xi*$xi);};
		  }

		  foreach ($data as $record) {
			$i+=1;
			
			//record['temp'], record['hum'],record['light'], record['bat'], record['panel'],record['co'],record['no2'], record['noise'], record['nets'], record['timestamp']
			$timestamp = strtotime($record['timestamp']);

			if($synthesize==true){
				$synthA=self::synthesizeSCKRecord($record, $synthA);
				if($i==1){
					$dStdDev=array('temp'=>array(),'hum'=>array(),'light'=>array(),'bat'=>array(),'panel'=>array(),'co' =>array(),'no2' =>array(),'noise' =>array(), 'nets'=>array());
				}

				$dStdDev['temp'][]	=((double) $record['temp']);
				$dStdDev['hum'][]	=((double) $record['hum']);
				$dStdDev['light'][]	=((double) $record['light']);
				$dStdDev['bat'][]	=((double) $record['bat']);
				$dStdDev['panel'][]	=((double) $record['panel']);
				$dStdDev['co'][]		=((double) $record['co']);
				$dStdDev['no2'][]	=((double) $record['no2']);
				$dStdDev['noise'][]	=((double) $record['noise']);
				$dStdDev['nets'][]	=((double) $record['nets']);
			}
			//$avgValue['timestamp']=$record['timestamp'];
			$avgValue['temp']	+=$record['temp'];
			$avgValue['hum']	+=$record['hum'];
			$avgValue['light']	+=$record['light'];
			$avgValue['bat']	+=$record['bat'];
			$avgValue['panel']	+=$record['panel'];
			$avgValue['co']		+=$record['co'];
			$avgValue['no2']	+=$record['no2'];
			$avgValue['noise']	+=$record['noise'];
			$avgValue['nets']	+=$record['nets'];

			if($timestamp>=($startRollup+$rollupSec-30) || $timestamp==$endRollup){   //à 30 seconde ou plus
				$avgValue['timestamp']=$record['timestamp'];
				$avgValue['temp'] /=($i);	
				$avgValue['hum']  /=($i);
				$avgValue['light']/=($i); 
				$avgValue['bat']  /=($i);
				$avgValue['panel']/=($i);
				$avgValue['co']   /=($i); 
				$avgValue['no2']  /=($i); 
				$avgValue['noise']/=($i);
				$avgValue['nets'] /=($i);
				
				if($synthesize==true){
				 $synthA['temp']['stddev']=sqrt((array_sum(array_map($carry,$dStdDev['temp'])))/$i-($avgValue['temp']*$avgValue['temp']));
				 $synthA['hum']['stddev']=sqrt((array_sum(array_map($carry,$dStdDev['hum'])))/$i-($avgValue['hum']*$avgValue['hum']));
				 $synthA['light']['stddev']=sqrt((array_sum(array_map($carry,$dStdDev['light'])))/$i-($avgValue['light']*$avgValue['light']));
				 $synthA['bat']['stddev']=sqrt((array_sum(array_map($carry,$dStdDev['bat'])))/$i-($avgValue['bat']*$avgValue['bat']));
				 $synthA['panel']['stddev']=sqrt((array_sum(array_map($carry,$dStdDev['panel'])))/$i-($avgValue['panel']*$avgValue['panel']));
				 $synthA['co']['stddev']=sqrt((array_sum(array_map($carry,$dStdDev['co'])))/$i-($avgValue['co']*$avgValue['co']));
				 $synthA['no2']['stddev']=sqrt((array_sum(array_map($carry,$dStdDev['no2'])))/$i-($avgValue['no2']*$avgValue['no2']));
				 $synthA['noise']['stddev']=sqrt((array_sum(array_map($carry,$dStdDev['noise'])))/$i-($avgValue['noise']*$avgValue['noise']));
	 			 $synthA['nets']['stddev']=sqrt((array_sum(array_map($carry,$dStdDev['nets'])))/$i-($avgValue['nets']*$avgValue['nets']));

					$avgValue['minmax']=$synthA;
				}

				$avgData[] = $avgValue;

				$i=0;
				$startRollup=$timestamp;
				$avgValue=array_replace($avgValue, array('temp'=>0,'hum'=>0,'light'=>0,'bat'=>0,'panel'=>0,'co' =>0,'no2' =>0,'noise' =>0, 'nets'=>0,'timestamp'=>0));
			} 
		  }
		}
		return $avgData;	
	}

	
/* note : autre approche pour synthèse à réfléchir 
	public static function getSCKAvgWithRollupPeriodV2($data,$rollup=60,$synthesize=false)
	{
		if($rollup>=1440){$rollup=1440;} //
		$rollupSec=$rollup*60;
		
		$avgData=array();
		$avgValue=array('temp'=>0,'hum'=>0,'light'=>0,'bat'=>0,'panel'=>0,'co' =>0,'no2' =>0,'noise' =>0, 'nets'=>0,'timestamp'=>0);
		
		if(!empty($data)){
		  $lenData=count($data);
		  $i=0;
		  $firstdata=reset($data);
		  $startRollup=strtotime($firstdata['timestamp']);  
		  $lastdata=end($data);
		  $endRollup=strtotime($lastdata['timestamp']);

		  $temp=array_column($data, 'temp');
		  $hum=array_column($data, 'hum');
		  $light=array_column($data, 'light');
		  $bat=array_column($data, 'bat');
		  $panel=array_column($data, 'panel');
		  $co=array_column($data, 'co');
		  $no2=array_column($data, 'no2');
		  $noise=array_column($data, 'noise');
		  $nets=array_column($data, 'nets');

		  if($synthesize==true){
		  	$synthA=array('temp'=>array('min'=>999999,'max'=>0),'hum'=>array('min'=>999999,'max'=>0),'light'=>array('min'=>999999,'max'=>0),'bat'=>array('min'=>999999,'max'=>0),'panel'=>array('min'=>999999,'max'=>0),'co' =>array('min'=>999999,'max'=>0),'no2' =>array('min'=>999999,'max'=>0),'noise' =>array('min'=>999999,'max'=>0), 'nets'=>array('min'=>999999,'max'=>0));
		  	$mean=array_sum($data)/$lenData;
		  	$carry=0.0;

		  }

		  foreach ($data as $record) {
			$i+=1;
			//record['temp'], record['hum'],record['light'], record['bat'], record['panel'],record['co'],record['no2'], record['noise'], record['nets'], record['timestamp']
			$timestamp = strtotime($record['timestamp']);

			if($synthesize==true){
				$synthA=self::synthesizeSCKRecord($record, $synthA);

			}

			//$avgValue['timestamp']=$record['timestamp'];
			$avgValue['temp']	+=$record['temp'];
			$avgValue['hum']	+=$record['hum'];
			$avgValue['light']	+=$record['light'];
			$avgValue['bat']	+=$record['bat'];
			$avgValue['panel']	+=$record['panel'];
			$avgValue['co']		+=$record['co'];
			$avgValue['no2']	+=$record['no2'];
			$avgValue['noise']	+=$record['noise'];
			$avgValue['nets']	+=$record['nets'];

			if($timestamp>=($startRollup+$rollupSec-30) || $timestamp==$endRollup){   //à 30 seconde ou plus
				$avgValue['timestamp']=$record['timestamp'];
				$avgValue['temp'] /=($i);	
				$avgValue['hum']  /=($i);
				$avgValue['light']/=($i); 
				$avgValue['bat']  /=($i);
				$avgValue['panel']/=($i);
				$avgValue['co']   /=($i); 
				$avgValue['no2']  /=($i); 
				$avgValue['noise']/=($i);
				$avgValue['nets'] /=($i);
				if($synthesize==true){$avgValue['minmax']=$synthA;}

				$avgData[] = $avgValue;


				$i=0;
				$startRollup=$timestamp;
				$avgValue=array_replace($avgValue, array('temp'=>0,'hum'=>0,'light'=>0,'bat'=>0,'panel'=>0,'co' =>0,'no2' =>0,'noise' =>0, 'nets'=>0,'timestamp'=>0));
			} 
		  }
		}
		return $avgData;	
	}*/

	public static function getDateTime($bindMap) {
		//TODO regler le probleme $datetime qui n'est pas correctement converti
		//TODO gérer fuseau horaire
		
		$datetime = getdate();
		$resDateTime = Translate::convert($datetime, $bindMap);

		return $resDateTime; 
	}
	
	public static function fillAndSaveSmartCitizenData($headers){
		
		$data = json_decode($headers["X-SmartCitizenData"],true);
		foreach ($data as $datum) {
			$dataThing = array();
			$dataThing['collection']=self::COLLECTION_DATA;        
        	$dataThing['key'] = 'thing';
        	$dataThing['type'] 	 = self::SCK_TYPE;
        	$dataThing['boardId']=$headers['X-SmartCitizenMacADDR'];
        	$dataThing['version']=$headers['X-SmartCitizenVersion'];
			$res = Element::save(array_merge($dataThing, $datum));
		}
        return $res;
	}

	public static function fillSmartCitizenMetadata($partReadings,$address,$geo){
		$mdata = array();
		$mdata['collection']=self::COLLECTION;
		$mdata['type'] = self::SCK_TYPE;
		$mdata['key'] = 'thing';
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