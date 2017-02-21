<?php
/*
This Class manage define stats process
like : 
- data analysis & statistic calculation 
- data clean ups
*/

class Stat {

	const COLLECTION = "stats";	

	/**
	 * Consolidate all the data
	*/
	public static function createGlobalStat(){

		$cities = array();

		$stat['created'] = new MongoDate(time());
		echo date('Y-m-d H:i:s')." - Enregistrement pour récupérer l'id <br/>";
		self::save($stat);
		$stat = PHDB::findOne('stats', array('created' => $stat['created']));

		echo date('Y-m-d H:i:s')." - Calcul global<br/>";
		$stat['global']['citoyens'] = self::consolidateCitoyens();
		$stat['global']['projects'] = self::consolidateProjects();
		$stat['global']['organizations'] = self::consolidateFromListAndCollection('organisationTypes', Organization::COLLECTION);
		$stat['global']['events'] = self::consolidateFromListAndCollection('eventTypes', Event::COLLECTION);
		$stat['global']['actionRooms'] = self::consolidateFromListAndCollection('listRoomTypes', ActionRoom::COLLECTION);
		$stat['global']['links'] = self::consolidateLinksCitoyen();
		$stat['global']['survey'] = self::consolidateSurveys();
		$stat['global']['modules'] = self::consolidateModules();
		echo date('Y-m-d H:i:s')." - Enregistrement<br/>";
		self::save($stat);
		unset($stat['global']);

		echo date('Y-m-d H:i:s')." - Calcul Cities<br/>";
		$stat['cities']['citoyens'] = self::consolidateCitoyensByCity();
		$stat['cities']['organizations'] = self::consolidateOrganizationsByCity();
		$stat['cities']['events'] = self::consolidateEventsByCity();
		$stat['cities']['projects'] = self::consolidateProjectsByCity();
		$stat['cities']['links'] = self::consolidateLinksCitoyenByCity();
		$stat['cities']['modules'] = self::consolidateModulesOrgaByCity();

		echo date('Y-m-d H:i:s')." - Calcul Logs<br/>";
		$stat['logs'] = self::consolidateLogs();

		echo date('Y-m-d H:i:s')." - Enregistrement<br/>";
		$res = self::save($stat);
		
	}

	/**
	 * Consolidate all the citoyens by cities
	*/
	public static function consolidateLogs(){
		echo date('Y-m-d H:i:s')." - consolidateLogs<br/>";
		$datas = array();

		//We took the last log consolidate
		$lastLogStat = self::getWhere(array('logs' => array('$exists' => 1)),array('created'), 1);
		if(count($lastLogStat)){
			$lastLogStat = array_pop($lastLogStat);
			$where = array('created' => array('$gt' => $lastLogStat['created']));
		}
		else{
			$where = array();
		}
		
		$allLogs = Log::getWhere($where);
		if(is_array($allLogs))foreach ($allLogs as $key => $value) {

			$action = @$value['action'];

			//If result => Consolidate by result
			if(!empty($action)){
				if(isset($value['result'])){
					$res_res = @$value['result']['result'];
					if(!isset($datas[$action][$res_res])) $datas[$action][$res_res] = 0;
					$datas[$action][$res_res] += 1 ;
				} 
				else{
					if(!isset($datas[$action])) $datas[$action] = 0;
					$datas[$action] += 1;
				}
			}
		}
		ksort($datas);
		return $datas;
	}

	/**
	 * Consolidate all the citoyens by cities
	*/
	public static function consolidateCitoyensByCity(){
		echo date('Y-m-d H:i:s')." - consolidateCitoyensByCity<br/>";
		$c = Yii::app()->mongodb->selectCollection(Person::COLLECTION);
		$result = $c->aggregate(
			array('$group' => array('_id' => '$address.codeInsee', "population" => array( '$sum' => 1 ))),
		    array('$match' => array( 'population' => array( '$gt' => 0 ))),
		    array('$sort' => array('insee' => 1))
		);

		if(is_array($result['result'])) foreach ($result['result'] as $key => $value) {
			//We check if the city is well present in cities
			$insee = self::checkAndGetInsee(@$value['_id']);
			$datas[$insee] = @$datas[$insee] + $value['population'];		
		}
		ksort($datas);
		return $datas;
	}

	/**
	 * Consolidate all the organizations by cities
	*/
	public static function consolidateOrganizationsByCity(){
		echo date('Y-m-d H:i:s')." - consolidateOrganizationsByCity<br/>";
		$c = Yii::app()->mongodb->selectCollection(Organization::COLLECTION);
		$result = $c->aggregate(
			array('$group' => array('_id' => array('insee' => '$address.codeInsee', 'type' => '$type'), "organisation" => array( '$sum' => 1 ))),
		    array('$match' => array( 'organisation' => array( '$gt' => 0 ))),
		    array('$sort' => array('insee' => 1))
		);
		if(is_array($result['result'])) foreach ($result['result'] as $key => $value) {
			$insee = self::checkAndGetInsee(@$value['_id']['insee']);

			if(!isset($value['_id']['type']))$value['_id']['type'] = "Unknow";
			$datas[$insee][$value['_id']['type']] = @$datas[$insee][$value['_id']['type']] + $value['organisation'];
			// if(isset($datas[$insee]['total'])){
			// 	$datas[$insee]['total'] += $value['organisation'];
			// }else{
			// 	$datas[$insee]['total'] = $value['organisation'];
			// } 
		}
		ksort($datas);
		return $datas;
	}

	/**
	 * Consolidate all the organizations by cities
	*/
	public static function consolidateEventsByCity(){
		echo date('Y-m-d H:i:s')." - consolidateEventsByCity<br/>";

		$c = Yii::app()->mongodb->selectCollection(Event::COLLECTION);
		$result = $c->aggregate(
			array('$group' => array('_id' => array('insee' => '$address.codeInsee', 'type' => '$type'), "event" => array( '$sum' => 1 ))),
		    array('$match' => array( 'event' => array( '$gt' => 0 ))),
		    array('$sort' => array('insee' => 1))
		);
		if(is_array($result['result'])) foreach ($result['result'] as $key => $value) {
			//We check if the city is well present in cities
			$insee = self::checkAndGetInsee(@$value['_id']['insee']);
			if(!isset($value['_id']['type']))$value['_id']['type'] = "Unknow";
			$datas[$insee][$value['_id']['type']] = $value['event'];
			// if(isset($datas[$insee]['total'])){
			// 	$datas[$insee]['total'] += $value['event'];
			// }else{
			// 	$datas[$insee]['total'] = $value['event'];
			// } 
		}
		ksort($datas);
		return $datas;
	}


	/**
	 * Consolidate all the citoyens by cities
	*/
	public static function consolidateProjectsByCity(){
		echo date('Y-m-d H:i:s')." - consolidateProjectsByCity<br/>";

		$c = Yii::app()->mongodb->selectCollection(Project::COLLECTION);
		$result = $c->aggregate(
			array('$group' => array('_id' => '$address.codeInsee', "projet" => array( '$sum' => 1 ))),
		    array('$match' => array( 'projet' => array( '$gt' => 0 ))),
		    array('$sort' => array('insee' => 1))
		);

		if(is_array($result['result'])) foreach ($result['result'] as $key => $value) {
			//We check if the city is well present in cities
			$insee = self::checkAndGetInsee(@$value['_id']);
			$datas[$insee] = $value['projet'];	
		}
		ksort($datas);
		return $datas;
	}

	/**
	 * Consolidate all the links by cities
	*/
	public static function consolidateLinksCitoyenByCity(){
		echo date('Y-m-d H:i:s')." - consolidateLinksCitoyenByCity<br/>";
		$datas = array();
		$persons = PHDB::find('citoyens', array('links' => array('$exists' => 1)), array("address"=>1, 'links' => 1));
		if(is_array($persons))foreach ($persons as $key => $value) {

			$insee = self::checkAndGetInsee(@$value['address']['codeInsee']);
			// print_r($value);
			// echo $insee.'<br/>';
			if(!isset($datas[$insee]))$datas[$insee] = array();

			//total
			// if(!isset($datas[$insee]['total'])) $datas[$insee]['total'] = 0;

			//total by links type
			if(is_array($value['links']))foreach ($value['links'] as $type => $list) {
				$inc = 0;
				if(is_array($list)) $inc = count($list);
				if(!isset($datas[$insee][$type]))$datas[$insee][$type] = 0;

				$datas[$insee][$type] += $inc;
				// $datas[$insee]['total'] += $inc;
			}
		}
		ksort($datas);
		return $datas;
	}

	/**
	 * Consolidate all the modules of organizations by cities
	*/
	public static function consolidateModulesOrgaByCity(){
		echo date('Y-m-d H:i:s')." - consolidateModulesOrgaByCity<br/>";

		$result = PHDB::find(Organization::COLLECTION, array('address.codeInsee' => array('$exists' => 1), 'modules' => array('$exists' => 1)));
		$aggregate = array();
		foreach ($result as $key => $value) {
			$insee = self::checkAndGetInsee(@$value['address']['codeInsee']);
			foreach ($value['modules'] as $idModule => $moduleName) {
				$aggregate[$insee][$moduleName] = @$aggregate[$insee][$moduleName] + 1;
			}
		}
		ksort($aggregate);
		return $aggregate;
	}



	/**
	 * create data statistics for surveys
	*/
	public static function consolidateSurveys(){
		echo date('Y-m-d H:i:s')." - consolidateSurveys<br/>";
		
		//All the projects
		$countSurvey = PHDB::count(Survey::COLLECTION);
		$surveys['total'] = $countSurvey;

		return $surveys;
	}

	/**
	 * create data statistics for modules
	*/
	public static function consolidateModules(){
		echo date('Y-m-d H:i:s')." - consolidateModules<br/>";
		$datas = array();
		$datas['total'] = 0;
		$organizations = PHDB::find(organization::COLLECTION, array("modules" => array('$exists' => 1)));
		foreach ($organizations as $key => $value) {
			if(is_array($value['modules'])){
				foreach ($value['modules'] as $keyModule => $nameModule) {
					if(!isset($datas[$nameModule]))$datas[$nameModule] = 0;
					$datas[$nameModule] +=1;
					$datas['total'] += 1;
				}
			}
		}
		return $datas;
	}

	/**
	 * create data statistics for Links
	*/
	public static function consolidateLinksCitoyen(){
		echo date('Y-m-d H:i:s')." - consolidateLinksCitoyen<br/>";

		$datas['total'] = 0;
		$persons = Person::getWhere(array('links' => array('$exists' => 1)));
		if(is_array($persons))foreach ($persons as $key => $value) {
			if(is_array($value['links']))foreach ($value['links'] as $type => $list) {
				foreach ($list as $key => $value) {
					if(isset($datas[$type])){
						$datas[$type] += 1;
					}
					else{
						$datas[$type] = 1;
					}
					$datas['total'] +=1;
				}
			}
		}
		return $datas;
	}


	/**
	 * create data statistics for citoyens
	*/
	public static function consolidateCitoyens(){
		echo date('Y-m-d H:i:s')." - consolidateCitoyens<br/>";
		
		//All the citoyens
		$countCitoyen = PHDB::count(Person::COLLECTION);
		$citoyens['total'] = $countCitoyen;

		return $citoyens;
	}

	/**
	 * create data statistics for projects
	*/
	public static function consolidateProjects(){
		echo date('Y-m-d H:i:s')." - consolidateProjects<br/>";
		
		//All the projects
		$countProject = PHDB::count(Project::COLLECTION);
		$projects['total'] = $countProject;

		return $projects;
	}

	/**
	 * create data statistics from an list and collection
	*/
	public static function consolidateFromListAndCollection($list, $collection){
		echo date('Y-m-d H:i:s')." - consolidateFromListAndCollection $list<br/>";

		//Get all the organizations types
		$types = Lists::get(array($list));
		$datas['total'] = 0;
		foreach($types[$list] as $key => $val){
			$datas[$key] = PHDB::count($collection, array("type" => $key));
			$datas['total'] += $datas[$key];
		}

		return $datas;
	}

	public static function getWhere($params, $fields=null, $limit=20) 
	{
	  	$stat =PHDB::findAndSort( self::COLLECTION,$params, array("created" =>-1), $limit, $fields);
	  	return $stat;
	}


	public static function save($stat){
		//Update
		if(isset($stat['_id'])){
			$id = $stat['_id'];
			unset($stat['_id']);
			return PHDB::update(  self::COLLECTION, 
		     								    array("_id"=>new MongoId($id)),
		     									array('$set' => $stat)
		     								);
		}//Insert
		else{
			return PHDB::insert(self::COLLECTION,$stat);
		}
	}


	public static function checkAndGetInsee($insee){
		/***** MEMORY ERROR *****/
		// global $cities;
		
		// if(!isset($cities)){
		// 	echo date('Y-m-d H:i:s')." - Chargement des codes INSEE<br/>";
		// 	$res = PHDB::find('cities', array('insee' => array('$exists' => 1)), array("insee"=>1, 'name' => 1));
		// 	foreach($res as $key => $city){
		// 		$tmp[$city['insee']] = $city['name'];
		// 	}
		// 	$cities = $tmp;
		// }
		// if(array_key_exists($insee, $cities))return $insee;

		if(is_array($insee)) return 'Unknow';
		$city = PHDB::find('cities', array('insee' => $insee));
		if($city)return $insee;
		return 'Unknow';
	}
}