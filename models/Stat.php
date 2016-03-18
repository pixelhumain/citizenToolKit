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
		$stat['type'] = 'global';
		$stat['citoyens'] = self::consolidateCitoyens();
		$stat['projects'] = self::consolidateProjects();
		$stat['organizations'] = self::consolidateFromListAndCollection('organisationTypes', Organization::COLLECTION);
		$stat['events'] = self::consolidateFromListAndCollection('eventTypes', Event::COLLECTION);

		self::save($stat);
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