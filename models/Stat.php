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



		$stat['created'] = new MongoDate(time());

		echo date('Y-m-d H:i:s')." - Calcul global<br/>";
		$stat['global']['citoyens'] = self::consolidateCitoyens();
		$stat['global']['projects'] = self::consolidateProjects();
		$stat['global']['organizations'] = self::consolidateFromListAndCollection('organisationTypes', Organization::COLLECTION);
		$stat['global']['events'] = self::consolidateFromListAndCollection('eventTypes', Event::COLLECTION);

		echo date('Y-m-d H:i:s')." - Calcul Cities<br/>";
		$stat['cities']['citoyens'] = self::consolidateCitoyensByCity();
		$stat['cities']['organizations'] = self::consolidateOrganizationsByCity();
		$stat['cities']['events'] = self::consolidateEventsByCity();
		$stat['cities']['projects'] = self::consolidateProjectsByCity();

		self::save($stat);
	}

	/**
	 * Consolidate all the citoyens by cities
	*/
	public static function consolidateCitoyensByCity(){
		$c = Yii::app()->mongodb->selectCollection(Person::COLLECTION);
		$result = $c->aggregate(
			array('$group' => array('_id' => '$address.codeInsee', "population" => array( '$sum' => 1 ))),
		    array('$match' => array( 'population' => array( '$gt' => 0 ))),
		    array('$sort' => array('insee' => 1))
		);

		if(is_array($result['result'])) foreach ($result['result'] as $key => $value) {
			//We check if the city is well present in cities
			$city = City::getIdByInsee($value['_id']);
			if($city){
				$datas[$value['_id']] = $value['population'];	
			}
			else{
				echo "Attention - Code Insee non connu en base : ".$value['_id']." avec ". $value['population']." citoyens</br>";
			}
			
		}
		ksort($datas);
		return $datas;
	}

	/**
	 * Consolidate all the organizations by cities
	*/
	public static function consolidateOrganizationsByCity(){
		$c = Yii::app()->mongodb->selectCollection(Organization::COLLECTION);
		$result = $c->aggregate(
			array('$group' => array('_id' => array('insee' => '$address.codeInsee', 'type' => '$type'), "organisation" => array( '$sum' => 1 ))),
		    array('$match' => array( 'organisation' => array( '$gt' => 0 ))),
		    array('$sort' => array('insee' => 1))
		);
		if(is_array($result['result'])) foreach ($result['result'] as $key => $value) {

			if(isset($value['_id']['insee'])){
				//We check if the city is well present in cities
				$city = City::getIdByInsee($value['_id']['insee']);
				if($city){
					if(isset($value['_id']['type'])){
						//We check if the type is well present
						$datas[$value['_id']['insee']][$value['_id']['type']] = $value['organisation'];
						if(isset($datas[$value['_id']['insee']]['total'])){
							$datas[$value['_id']['insee']]['total'] += $value['organisation'];
						}else{
							$datas[$value['_id']['insee']]['total'] = $value['organisation'];
						} 
					}
					else{
						echo "Attention -Pas de type pour ".$value['_id']['insee']." avec ". $value['organisation']." organisations</br>";
					}
				}
				else{
					if(!is_array($value['_id']['insee']))echo "Attention - Code Insee non connu en base : ".$value['_id']['insee']." avec ".@$value['organisation']." organisations</br>";
				}
			}
		}
		ksort($datas);
		return $datas;
	}

	/**
	 * Consolidate all the organizations by cities
	*/
	public static function consolidateEventsByCity(){
		$c = Yii::app()->mongodb->selectCollection(Event::COLLECTION);
		$result = $c->aggregate(
			array('$group' => array('_id' => array('insee' => '$address.codeInsee', 'type' => '$type'), "event" => array( '$sum' => 1 ))),
		    array('$match' => array( 'event' => array( '$gt' => 0 ))),
		    array('$sort' => array('insee' => 1))
		);
		if(is_array($result['result'])) foreach ($result['result'] as $key => $value) {

			if(isset($value['_id']['insee'])){
				//We check if the city is well present in cities
				$city = City::getIdByInsee($value['_id']['insee']);
				if($city){
					if(isset($value['_id']['type'])){
						//We check if the type is well present
						$datas[$value['_id']['insee']][$value['_id']['type']] = $value['event'];
						if(isset($datas[$value['_id']['insee']]['total'])){
							$datas[$value['_id']['insee']]['total'] += $value['event'];
						}else{
							$datas[$value['_id']['insee']]['total'] = $value['event'];
						} 
					}
					else{
						echo "Attention -Pas de type pour ".$value['_id']['insee']." avec ". $value['event']." événements</br>";
					}
				}
				else{
					if(!is_array($value['_id']['insee']))echo "Attention - Code Insee non connu en base : ".$value['_id']['insee']." avec ".@$value['event']." événements</br>";
				}
			}
		}
		ksort($datas);
		return $datas;
	}


	/**
	 * Consolidate all the citoyens by cities
	*/
	public static function consolidateProjectsByCity(){
		$c = Yii::app()->mongodb->selectCollection(Project::COLLECTION);
		$result = $c->aggregate(
			array('$group' => array('_id' => '$address.codeInsee', "projet" => array( '$sum' => 1 ))),
		    array('$match' => array( 'projet' => array( '$gt' => 0 ))),
		    array('$sort' => array('insee' => 1))
		);

		if(is_array($result['result'])) foreach ($result['result'] as $key => $value) {
			//We check if the city is well present in cities
			$city = City::getIdByInsee($value['_id']);
			if($city){
				$datas[$value['_id']] = $value['projet'];	
			}
			else{
				echo "Attention - Code Insee non connu en base : ".$value['_id']." avec ". $value['projet']." projets</br>";
			}
		}
		ksort($datas);
		return $datas;
	}


	/**
	 * create data statistics for citoyens
	*/
	public static function consolidateCitoyens(){
		
		//All the citoyens
		$countCitoyen = PHDB::count(Person::COLLECTION);
		$citoyens['total'] = $countCitoyen;

		return $citoyens;
	}

	/**
	 * create data statistics for projects
	*/
	public static function consolidateProjects(){
		
		//All the projects
		$countProject = PHDB::count(Project::COLLECTION);
		$projects['total'] = $countProject;

		return $projects;
	}

	/**
	 * create data statistics from an list and collection
	*/
	public static function consolidateFromListAndCollection($list, $collection){

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
			PHDB::update(  self::COLLECTION, 
		     								    array("_id"=>new MongoId($id)),
		     									array('$set' => $stat)
		     								);
		}//Insert
		else{
			PHDB::insert(self::COLLECTION,$stat);
		}
	}
}