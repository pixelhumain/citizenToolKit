<?php 

class Thing {
	//TODO changer collection things en datas (dans mongodb dev aussi)
	//TODO : utilisé un formulaire avec device et adresse mac pour compléter les métadatas

	const COLLECTION = "poi";
	const COLLECTION_METADATA="metadata";
	const CONTROLLER = "thing";
	const COLLECTION_DATA = "data";
	const URL_API_SC = "https://api.smartcitizen.me/v0"; // vérifier que l'url de l'api est à jour
	const SCK_TYPE = 'smartCitizen';
	//public static $types = array ();

	public static $dataBinding = array (
		"address"	=> array("name" => "address"),
		"boardId" 	=> array("name" =>"macId"),	
		"deviceId" 	=> array("name"=> "deviceId"), 	//id for smartcitizen.me
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
	    "name" => array("name"=>"name"),
		"kit" 	=> array("name"=> "kit"),
		"geo" 	=> array("name" => "geo", "rules" => array("required","geoValid")), //poi
		"geoPosition" => array("name" => "geoPosition"),
	    "location" 	=> array("name" => "location"),
	    "addresses" => array("name" => "addresses"),
	    "description" => array("name" => "description"),
	    "media" => array("name" => "media"),
	    "medias" => array("name" => "medias"),
	    "parentId" => array("name" => "parentId"),
	    "parentType" => array("name" => "parentType"),
	    "sckKits" 	=> array("name"=>"sckKits"), //api metadata
	    "sckMeasurements" => array("name"=>"sckMeasurements"),//api metadata
		"sckSensors" => array("name"=>"sckSensors"),//api metadata
		"sckUpdatedAt"	=>array("name"=>"sckUpdatedAt"),
		"sensors" 	=> array("name"=> "sensors"),
	    "status" 	=> array("name" => "status"), //in metadata updated for POI realy need?
	   	"tags" => array("name" => "tags"),
	    "urls"		=> array("name" =>"urls"),
	    "version" 	=> array("name" => "sckVersion"),
	   
	    "modified" 	=> array("name" => "modified"),
	    "updated" 	=> array("name" => "updated"),
	    "creator" 	=> array("name" => "creator"),
	    "created" 	=> array("name" => "created")
	);

	private static $sckAPIPathMetadata = array("sckSensors" => "sensors", "sckMeasurements" => "measurements", "sckKits" => "kits");

	public static function updateSCKAPIMetadata($forceUpdate=true){
		
		$res=array(); //resultat de Element::save
		
		$wheremeta=array();
		$gmttime=gmdate('Y-m-d');

		$fields=array('modified','_id','updated');
		
		foreach (self::$sckAPIPathMetadata as $key => $value) {
			$wheremeta['type']=$key;
			
			$apisck = self::getSCKDeviceMdata(self::COLLECTION_METADATA,$wheremeta,$fields);
		
			$intDayBeginning=strtotime($gmttime);
			if($intDayBeginning >= $apisck['updated'] || $forceUpdate==true){
				$mdata=array();
				$mdata['collection']=self::COLLECTION_METADATA;
				$mdata['type'] = $key;
				$mdata['key'] = 'thing';
				$sckAPIMetadata=json_decode(file_get_contents(self::URL_API_SC."/".$value),true);
				$mdata[$key]=$sckAPIMetadata;
			
				if(!empty($apisck)) {
					$mdata['id']=$apisck['_id']; 
				}
				
				$result = Element::save($mdata);
				if($result["result"]==true){
					$res[]=$value;
				}
			}
		}
		return $res;
	}

	//TODO : Prévoir l'edition forcer (ajout d'argument et de condition (passer outre la limite de temps $forceUpdate), ne pas utiliser un poi mais directement le deviceId SCK) par une post via le controller UpdateSckDevicesAction.php
	//ajouter les metadatas si le sck n'est pas en base 

	public static function setMetadata($poi=null,$atSC=null,$forceUpdate=false,$deviceId=null,$boardId=null){
		$gmttime=gmdate('Y-m-d\TH');

		/*if(!empty($deviceId) && is_string($deviceId)){ settype($deviceId, "integer"); } */
		$name=null; 
		$geo=null; $sckurl=null; $address=null;

		if(empty($deviceId) && !empty($poi)){
			$sckurl=$poi['urls'][0];
			$deviceId = self::getSCKDeviceIdByPoiUrl($sckurl);
			if(!empty($poi['address']) ) { $address = $poi['address']; }
			//$name = $poi['name'];
			if($poi['type']!=self::SCK_TYPE){ $poi['type'] =self::SCK_TYPE;}
			if(!empty($poi['geo'])) { $geo = $poi['geo']; }
		}
		
		$wheremeta = array('type'=>'smartCitizen','deviceId'=> $deviceId );
		$deviceMetadata = self::getSCKDeviceMdata(self::COLLECTION, $wheremeta); //dans poi maintenant vide si le poi n'a pas le bon type dans ce cas update pour mettre le bon type 
		if(empty($geo) && !empty($deviceMetadata['geo'])){$geo=$deviceMetadata['geo']; }
		if(empty($address) && !empty($deviceMetadata['address'])){ $address=$deviceMetadata['address']; }

		$tLReadings=(isset($deviceMetadata['timestamp'])) ? $deviceMetadata['timestamp'] : "2017-04-23" ;

		if(preg_match("/".$gmttime."/i",$tLReadings)!=1 || $forceUpdate==true){
			$partReadings = self::getLastedReadViaAPI($deviceId,$atSC);
/*
			if(empty($sckurl)){
				$sckurl = self::URL_API_SC."/".'$deviceId';
			}
			$partReadings['url']=$sckurl;
*/
			if(!empty($boardId) && ($deviceMetadata['boardId']=='[FILTERED]' || !isset($deviceMetadata['boardId']  ))){
				$partReadings['boardId']=$boardId;}
/*
			if(!empty($partReadings) && isset($partReadings['boardId']) && isset($deviceMetadata['boardId'])){
			 if ( $deviceMetadata['boardId']!='[FILTERED]' && $partReadings['boardId']=='[FILTERED]'){
				unset($partReadings['boardId']);}
			}
*/
/*			
			if(!isset($partReadings['name'])){
				$partReadings['name'] = ((empty($name)) ? '' : '$name' );}
*/
			if(empty($geo) && !empty($partReadings['location'])){ 
				$geo = array("@type"=>"GeoCoordinates", "latitude" => strval($partReadings['location']['latitude']), "longitude" => strval($partReadings['location']['longitude']));
			}
			if(empty($address) && !empty($geo) ){
			  $partReadings['address'] = Import::getAndCheckAddressForEntity(null,$geo)['address'];
			}

			$toSave=array();
			if(!empty($poi)){
				$poi['id']=$poi['_id'];
				unset($poi['_id']);
				$toSave = array_merge($poi, $partReadings);
			} else if(empty($deviceMetadata) && empty($poi)){
				$toSave['deviceId']=$deviceId;
				$toSave['type']=self::SCK_TYPE;
				$toSave['address']=$address;
			}
			$toSave['deviceId']=$deviceId;
			$toSave['geo']=$geo;
			$toSave['collection']=self::COLLECTION;
			$toSave['key']='thing';

			return $toSave;
		}
	}

	public static function updateMetadatas($pois=null,$atSC=null){
		
		$sck=array(); //contient tous les metadata à jour
		$res=array(); //resultat de Element::save
		if(empty($pois)){
			$pois = self::getSCKInPoiByCountry();
		}
		$elementAlreadyUpdate= 0; 		$newelements= 0 ; 		$badr=0;
		foreach ($pois as $poi) {
			$toUpSave = self::setMetadata($poi,$atSC,true); // force update à mettre en false avant d'utilisé ( true pour les test en local);
	//$res['toUpSave'][]=$toUpSave; // pour voir tous les éléments qui vont dans Element::save
			if(empty($toUpSave)){ $elementAlreadyUpdate++; }
			else{	
			  	$result = Element::save($toUpSave);
			  	if($result['result']==true){
			  		$newelements++;
			  	}else{
			  		//$res['badResult'][]=$result; //décommenté pour voir les messages bad result dans la réponse 
			  		$badr++;
			  	} 	  
			}
		}
		// Pour simplifié le message resultat:
			$res['elementsAlreadyUpdate'] = $elementAlreadyUpdate;
			$res['elementsUpdated']= $newelements;
			$res['elementsBad']= $badr;
		return $res;
	}

	public static function updateOneMetadata($deviceId,$boardId,$atSC=null){
		
		return Element::save(self::setMetadata(null,$atSC,true,$deviceId,$boardId));  
	}

	public static function updateMultipleMetadata($listbd){

		$res=array(); //resultat de Element::save
		
		foreach ($listbd as $bd) {
			//TODO (optionnel) : vérifier le deviceID est un nombre
			if(preg_match('/^([0-9a-f]{2}[:]){5}([0-9a-f]{2})$/', $bd['boardId'])==1){
				$res[] = Element::save(self::setMetadata(null,null,true,$bd['deviceId'],$bd['boardId']));
			}
		}
		return $res;
	}

	//chercher les sck enregistrer dans les pois dans la base de données CO
	public static function getSCKInPoiByCountry($country="RE",$fields=null){
		$where = array(	'type' => array('$exists'=>1) ); //, 'address.addressCountry' =>$country );
		//poi : addressCountry
		//mongo regex sur l'url
		$queryUrls[] = new MongoRegex("/".self::SCK_TYPE."/i");
		$where['urls'] = array('$in'=> $queryUrls);
		return PHDB::find(Poi::COLLECTION, $where, $fields);
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

	public static function getSCKDevicesByCountryAndCP($country="RE", $cp=null, $fields=null){

		$where=array("type"=>self::SCK_TYPE);
		$where['address.addressCountry']=$country;
		//$cp=null pas de codepostale dans where. prend tous les sck du pays
		if(!empty($cp)){ $where['address.postalCode']=$cp; }

		return self::getSCKDevices($where,$fields);
	}

	public static function getSCKDevicesByLocality($country="RE", $regionName=null, $depName=null, $cityName=null, $cp=null, $insee=null, $fields=null){

		$where=array("type"=>self::SCK_TYPE);
		if(!empty($country)){ 	$where["address.addressCountry"]=$country; }
		if(!empty($insee)){ 	$where["address.codeInsee"] = $insee; }
		if(!empty($regionName)){$where["address.regionName"]=$regionName; }
		if(!empty($depName)){ 	$where["address.depName"]	=$depName; }
		if(!empty($cityName)){ 	$where["address.addressLocality"]=$cityName; }
		if(!empty($cp)){ 		$where['address.postalCode']	=$cp; }
		
		$SCKDevices = self::getSCKDevices($where,$fields);
		return $SCKDevices;
	}

	//metadata
	public static function getSCKDeviceMdata($collection=self::COLLECTION,$where=array("type"=>self::SCK_TYPE), $fields=null){
		return PHDB::findOne($collection, $where,$fields);
	}

	public static function getSCKDevices($where=array("type"=>self::SCK_TYPE), $fields=null ){
		return PHDB::find(self::COLLECTION, $where, $fields);
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
		$queryATSC = (empty($atSC)|| $atSC=="" )? "" : "?access_token=".$atSC;
		//echo "atSC : ".$queryATSC;
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
	//Evolution possible modifier pour prendre une periode plus grand qu'une journée, au lieu de faire jour par jour
	public static function getConvertedRercord($boardId,$lastest=false,$strdateD=null,$hour=null){

		$where = array("type"=>self::SCK_TYPE);
		//date example :"2017-02-27"
		if(empty($strdateD)){
			$regextime = "/".gmdate('Y-m-d')."/i";
		} else { 
			$time=$strdateD;
			$time2 = gmdate('Y-n-d',strtotime($strdateD)); // 2017-4-20
			if(!empty($hour)&&($hour>=0 && $hour<=23)){ 
				$time=$time." ".$hour;
				$time2=$time2." ".$hour;
			}
			$regextime = "/(".$time."|".$time2.")/i";
		}
		
		$queryTimestamp[] = new MongoRegex($regextime);
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
			if((!isset($rawData['converted']) || @$rawData['converted']==false )||
				(!isset($rawData['synthetized']) || @$rawData['synthetized']!=false )){
				$data[]= SCKSensorData::SCK11Convert($rawData);
			}
		}
		return $data; 
	}

//TODO gérer les données manquantes avec des grand saut pb de synthétisation
	public static function getSCKAvgWithRollupPeriod($data,$rollup=60,$synthesize=false)
	{
		if($rollup>=1439){$rollup=1439;} //
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

			$timestamp = strtotime($record['timestamp']);

			if($synthesize==true){
				$synthA=self::minmaxSCKRecord($record, $synthA);
				if($i==1){
					$dStdDev=array('temp'=>array(null),'hum'=>array(null),'light'=>array(null),'bat'=>array(null),'panel'=>array(null),'co' =>array(null),'no2' =>array(null),'noise' =>array(null), 'nets'=>array(null));
				}

				$dStdDev['temp'][]	=((double) $record['temp']);
				$dStdDev['hum'][]	=((double) $record['hum']);
				$dStdDev['light'][]	=((double) $record['light']);
				$dStdDev['bat'][]	=((double) $record['bat']);
				$dStdDev['panel'][]	=((double) $record['panel']);
				$dStdDev['co'][]	=((double) $record['co']);
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

					$avgValue['minmaxstddev']=$synthA;
				}
				//unset pour enlever les data brut avant le merge
				unset($record['temp'],$record['hum'],$record['noise'],$record['co']);
            	unset($record['light'],$record['no2'],$record['bat'],$record['panel']);
            	unset($record['nets'],$record['timestamp']);

				$avgData[] = array_merge($record,$avgValue);

				$i=0;
				$startRollup=$timestamp;
				$avgValue=array_replace($avgValue, array('temp'=>0,'hum'=>0,'light'=>0,'bat'=>0,'panel'=>0,'co' =>0,'no2' =>0,'noise' =>0, 'nets'=>0,'timestamp'=>0));
			} 
		  }
		}
		return $avgData;	
	}

	public static function synthetizeSCKRecordInDB($boardId,$date,$rollupMin,$converted=false){
		$where=array("type"=>self::SCK_TYPE);
		$time=	$date->format('Y-m-');
		$time2 = $date->format('Y-n-');
		$regextime = "/(".$time."|".$time2.")/i";
		$queryTimestamp[] = new MongoRegex($regextime);
		$where["timestamp"] = array('$in'=> $queryTimestamp);
		$sort = array("timestamp"=>1);
		$limit = null;
		
		$dataR = self::getLastestRecordsInDB($boardId,$where,$sort,$limit);
		if($converted==true){
			$dataC=array();
			foreach ($dataR as $dataRaw) {
				$dataRaw["converted"]=$converted;
				$dataC[]=SCKSensorData::SCK11Convert($dataRaw);
			}
			$data=$dataC;
		}else{$data=$dataR;}

		$dataS = self::getSCKAvgWithRollupPeriod($data,$rollupMin,true);
		
		$dataThing= array('collection' => self::COLLECTION_DATA, 'key'=>'thing','type'=>self::SCK_TYPE,'boardId'=>$boardId,'synthetized'=>true );

		$nbSavedElements=0;
		
		foreach ($dataS as $dataToSave) {
			$res = Element::save(array_merge($dataThing, $dataToSave));
			$res1[]=$res;
			if($res['result']==true)
				$nbSavedElements++;
		}
		
		if($nbSavedElements>=(count($dataS)-1)){
			$resSuppr = self::deleteSCKRecords($boardId,$date);
		}
		

		//TODO faire un tableau recap des différentes etapes
		return $res1;
	}

	public static function getDateTime($bindMap) {
		//TODO regler le probleme $datetime qui n'est pas correctement converti
		//TODO gérer fuseau horaire
		
		$datetime = getdate();
		$resDateTime = Translate::convert($datetime, $bindMap);

		return $resDateTime; 
	}
	
	//TODO faire une vérification du timestamp pour éviter les '#' ou timestamps vide ou doublons
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

	private static function getSCKDeviceIdByPoiUrl($sckUrl){
	
		$eUrl= explode("/",$sckUrl);
		if( ($eUrl[(count($eUrl)-2)]=='kits' || $eUrl[(count($eUrl)-2)]=='devices') && !empty($eUrl[(count($eUrl)-1)]) ) { 
			$deviceid = $eUrl[(count($eUrl)-1)];
			return $deviceid; 
		}
	}

	private static function minmaxSCKRecord($record, $synthA){
		
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

	private static function deleteSCKRecords($boardId,$date){

		$dateM = $date->format('Y-m-'); //'Y-m-d' pour supression par jours (Faut changer l'interval aussi)
		$dateN = $date->format('Y-n-');
		$where = array("type"=>self::SCK_TYPE,"boardId"=>$boardId);
		$queryTimestamp[] = new MongoRegex("/^(".$dateM."|".$dateN.")/i");
		$where["timestamp"] = array('$in'=> $queryTimestamp);
		$where["synthetized"] = array('exists'=>0); 

		$result = PHDB::remove('data_copy', $where); // collection : self::COLLECTION_DATA , data_copy pour les tests
		return $result;
	}




}

?>