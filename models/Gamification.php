<?php
/*
This Class defines all rules and values applied across the plateforme 
Points a distributed to users and organizations for any activity inside the system
Things that count and give points : 
linking to someone : give both users 
creating : events, organizations, projects
answering surveys
asking questions

--- Visualisations & Graph representation ideas ---
	GIS graph + 1 criteria + 1 link compare 
		every city is a circle of sized by a criteria 
		each circle is linked to neighbour circles 
		the width of the link carries a link ponderation between 2 cities 

*/
class Gamification {

	const COLLECTION = "gamification";
	
	//platefrom actions
	const POINTS_USER_LOGIN = 5;
	const POINTS_USER_REGISTRATION = 15;

	//const POINTS_SEARCH = 1;
	const POINTS_ADD_POST = 0.5;
	const POINTS_ANSWER_POST = 0.1;
	const POINTS_POST_LIKED = 0.1;

	//links
	const POINTS_LINK_USER_2_USER = 1;
	const POINTS_LINK_USER_2_ORGANIZATION = 1;
	const POINTS_LINK_USER_2_EVENT = 0.5;
	const POINTS_LINK_USER_2_PROJECT = 0.5;

	//creation
	const POINTS_CREATE_ORGANIZATION = 10;
	const POINTS_CREATE_EVENT = 10;
	const POINTS_CREATE_PROJECT = 10;

	//person activity 
	//points if profile is filled above 80%
	//presenting a MOAC

	//organization activity
	const POINTS_ORG_ADD_MEMBER = 5;
	//points if profile is filled above 80%	

	//event activity
	const POINTS_EVENT_INVITE_ATTENDEES = 5;	

	//project activity
	const POINTS_PROJECT_ADDING_BULLET_POINTS = 5;
	const POINTS_PROJECT_ADD_CONTRIBUTOR = 5;

	const BADGE_SEED_LIMIT = 50;
	const BADGE_GERM_LIMIT = 100;
	const BADGE_PLANT_LIMIT = 200;
	const BADGE_TREE_LIMIT = 300;
	const BADGE_FOREST_LIMIT = 500;
	const BADGE_COUNTRY_LIMIT = 5000;
	const BADGE_CONTINENT_LIMIT = 20000;
	const BADGE_PLANET_LIMIT = 100000;
	//points if profile is filled above 80%

	//actions (like/dislike/abuse)
	const POINTS_VOTEUP = 0.1;
	const POINTS_VOTEDOWN = 0.1;
	const POINTS_REPORTABUSE = 0.2;

	//Moderate
	const POINTS_MODERATE = 3;

	//city actions
	//a city feed url
	//a new place to discover
	//an object for sale 
	//a news entry
	//creating a city survey
	//answering a city survey 
	//doing a common task 
	//creating a common task
	//adding a new open data source or entry (interface add/propose an open data entry)


	/*
	Calculate Gamification points based on gamifaication rules
	*/
	public static function calcPoints($userId, $filter=null) {
		
		$res = 0;
		$person = Person::getById($userId);
		if( isset( $person['links'] ) )
		{
			foreach ( $person['links'] as $type => $list) {
				if( $type == Link::person2events && ( !$filter || $filter == Link::person2events) ){
					foreach ($list as $key => $value) {
						$res += self::POINTS_LINK_USER_2_EVENT;
					}
				}
				if( $type == Link::person2projects && (!$filter || $filter == Link::person2projects) ){
					foreach ($list as $key => $value) {
						$res += self::POINTS_LINK_USER_2_PROJECT;
					}
				}
				if( $type == Link::person2organization && (!$filter || $filter == Link::person2organization) ){
					foreach ($list as $key => $value) {
						$res += self::POINTS_LINK_USER_2_ORGANIZATION;
					}
				}
				if( $type == Link::person2person && (!$filter || $filter == Link::person2person) ){
					foreach ($list as $key => $value) {
						$res += self::POINTS_LINK_USER_2_USER;
					}
				}
			}
		}

		return $res;
	}

	public static function insertGamificationUser($userId){
		$entry = array('_id' => new MongoId($userId));
		$entry[time()] = self::consolidatePoints($userId);
		self::save($entry);
	}

	public static function calcPointsAction($userId) {
		$total = array();
		$total['total'] = 0;

		//We take action to consolidate
		$actions = ActivityStream::getWhere(array("who" => (string)$userId));
		foreach ($actions as $key => $action) {
			if(isset($action['self'])){
				//if action is defined => We aggregate the points
				if(defined("self::POINTS_".strtoupper($action['self']))){
					/******* ATTENTION ********/
					//Choix prix de comptabiliser toutes les actions même s'il en y a plusieurs
					//Une regle de gestion devra être ajoutée pour éviter plusieurs like/dislike/...
					if(isset($total[$action['self']])){
						$total[$action['self']] += constant("self::POINTS_".strtoupper($action['self']));
					}
					else{
						$total[$action['self']] = constant("self::POINTS_".strtoupper($action['self']));
					}
					$total['total'] += constant("self::POINTS_".strtoupper($action['self']));
				}
			}
		}

		return $total;
	}


	public static function consolidatePoints($userId, $filter=null) {
		$points = array();
		$points['created'] = new MongoDate(time());
		$points['total'] = 0;

		/***** ACTIONS ******/
		$points['actions'] = self::calcPointsAction($userId);
		$points['total'] += $points['actions']['total'];	

		/***** LINKS ******/
		$points['links']['total'] = self::calcPoints($userId);
		$points['total'] += $points['links']['total'];

		//Format double in Integer
		// foreach ($points as $key1 => $value) {
		// 	if(is_array($value)){
		// 		foreach ($value as $key2 => $value2) {
		// 			if(is_numeric($value2)){
		// 				$points[$key1][$key2] = $value2;
		// 			}
		// 		}
		// 	}
		// 	elseif(is_numeric($value)){
		// 		$points[$key1]= $value;
		// 	}
		// }

		return $points;
	}



	public static function badge($userId) {
		
		$total = self::calcPoints($userId);
		$res = Yii::t("common","air");

		if( $total > self::BADGE_SEED_LIMIT && $total < self::BADGE_GERM_LIMIT )
			$res = Yii::t("common","seed");
		if( $total > self::BADGE_GERM_LIMIT && $total < self::BADGE_PLANT_LIMIT )
			$res = Yii::t("common","germ");
		if( $total > self::BADGE_PLANT_LIMIT && $total < self::BADGE_TREE_LIMIT )
			$res = Yii::t("common","plant");
		if( $total > self::BADGE_TREE_LIMIT && $total < self::BADGE_FOREST_LIMIT )
			$res = Yii::t("common","tree");
		if( $total > self::BADGE_FOREST_LIMIT && $total < self::BADGE_COUNTRY_LIMIT )
			$res = Yii::t("common","forest");
		if( $total > self::BADGE_COUNTRY_LIMIT && $total < self::BADGE_PLANET_LIMIT )
			$res = Yii::t("common","country");
		if( $total > self::BADGE_PLANET_LIMIT )
			$res = Yii::t("common","planet");
		
		return $res;
	}
	/**
	 * adds an entry into the cron collection
	 * @param $params : a set of information for a proper cron entry
	*/
	public static function update($params){
	    $new = array(
			"id" => $params['id'],
	  		"person" => $params['type'],
	  		"folder" => $params['folder'],
	  		"moduleId" => $params['moduleId'],
	  		"doctype" => Document::getDoctype($params['name']),	
	  		"author" => $params['author'],
	  		"name" => $params['name'],
	  		"size" => $params['size'],
	  		'created' => time()
	    );

	    PHDB::insert(self::COLLECTION,$new);
	}


	public static function save($entry){

		//Update
		if(isset($entry['_id'])){
			$id = $entry['_id'];
			unset($entry['_id']);
			PHDB::update(  self::COLLECTION, 
		     								    array("_id"=>new MongoId($id)),
		     									array('$set' => $entry)
		     								);
		}//Insert
		else{
			PHDB::insert(self::COLLECTION,$entry);
		}
	}


	/**
     * set the points of a person
     * @param type $id : is the mongoId of the person
     * @return nothing
     */
    public static function updateUser($id){
    	$gamification = self::consolidatePoints($id);
        PHDB::update(Person::COLLECTION,
                            array("_id"=>new MongoId($id)), 
                            array('$set' => array('gamification' => $gamification)),
                            array("upsert" => true));
    }

	/**
     * set the points of a person
     * @param type $id : is the mongoId of the person
     * @return nothing
     */
    public static function incrementUser($id, $action){
    	if($id != "" && $action != ""){
    		if(defined("self::POINTS_".strtoupper($action))){
				$toAdd = constant("self::POINTS_".strtoupper($action));
    		
		        PHDB::update(Person::COLLECTION,
	                            array("_id"=>new MongoId($id)), 
	                            array('$inc' => 
	                            	array(
	                            		'gamification.actions.'.$action => $toAdd,
	                            		'gamification.actions.total' => $toAdd,
	                            		'gamification.total' => $toAdd
	                            	)
	                            )
	            );
		    }
    	}
    }

    
}
?>