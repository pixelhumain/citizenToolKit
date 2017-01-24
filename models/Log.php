<?php
/*
This Class manage define logs process
like : 
- Insert
- data analysis & statistic calculation 
- data clean ups
*/
class Log {

	const COLLECTION = "logs";				

	/**
	 * Set an array of parameters for actions we want to log
	 * @return $array : a set of actions with parameters
	*/
	public static function getActionsToLog(){
		return array(
	      "person/authenticate" => array('waitForResult' => true, "keepDuration" => 60),
	      "person/logout" => array('waitForResult' => false, "keepDuration" => 60),
	      "organization/save" => array('waitForResult' => true, "keepDuration" => 60),
	      "person/register" => array('waitForResult' => true, "keepDuration" => 60),
	      "news/save" => array('waitForResult' => true, "keepDuration" => 30),
	      "action/addaction" => array('waitForResult' => true, "keepDuration" => 60),
	    );
	}

	/**
	 * adds an entry into the logs collection
	 * @param $libAction : a set of information for a proper logs entry
	*/
	public static function setLogBeforeAction($libAction){

    	//Data by default
	    $logs =array(
			"userId" => @Yii::app()->session['userId'],
			"browser" => @$_SERVER["HTTP_USER_AGENT"],
			"ipAddress" => @$_SERVER["REMOTE_ADDR"],
			"created" => new MongoDate(time()),
			"action" => $libAction
	    );

	    //POST or GET
    	if(!empty($_REQUEST)) $logs['params'] = $_REQUEST;

    	//To avoid the clear password storage
    	if(isset($logs['params']['pwd'])) unset($logs['params']['pwd']);
	    return $logs;
	}

	/**
	 * Set the result answer of the logging action
	 * @param $id : the log id
	 * @param $result : the result information
	*/
	public static function setLogAfterAction($log, $result){

		$log['result']['result'] = @$result['result'];
		$log['result']['msg'] = @$result['msg'];
		self::save($log);

	}

	public static function save($log){
		//Update
		if(isset($log['_id'])){
			$id = $log['_id'];
			unset($log['_id']);
			PHDB::update(  self::COLLECTION, 
		     								    array("_id"=>new MongoId($id)),
		     									array('$set' => $log)
		     								);
		}//Insert
		else{
			PHDB::insert(self::COLLECTION,$log);
		}
	}


	/**
	 * List all the log in the collection logs
	*/
	public static function getAll(){
		return PHDB::find(self::COLLECTION);
	}

	/**
	 * List the log in the collection logs depends Where
	*/
	public static function getWhere($where = array()){
		return PHDB::find(self::COLLECTION, $where);
	}

	/**
	 * Give a lift of IpAdress to block
	*/
	public static function getSummaryByAction(){
		
		$c = Yii::app()->mongodb->selectCollection(self::COLLECTION);
		$result = $c->aggregate(
			array(   
			  '$group'=> 
		  		array(
			    	'_id' => array(
			    		'action' => '$action'
			    		, 'ip' => '$ipAddress'
			    		, 'result' => '$result.result'
			    		, 'msg' => '$result.msg'
			    	),
				    'count' => array ('$sum' => 1),
				    'minDate' => array ( '$min' => '$created' ),
				    'maxDate' => array ( '$max' => '$created' )
				)
			
		));
		return $result;
	}

	/**
	 * Let to clean the logs depends to the rules defined in the array logs parameters
	*/
	public static function cleanUp(){
	    $actionsToLog = Log::getActionsToLog();

	    foreach($actionsToLog as $action => $param){
	    	$dateLimit = date('Y-m-d H:i:s', time()-(3600*24*$param['keepDuration']));
	    	echo "Action en traitement : ".$action." - Durée de vie de ".$param['keepDuration']." jour(s) soit < ".$dateLimit."<br/>";
	    	PHDB::remove(self::COLLECTION, array( 
	    		"action"=> $action
	    		, "created" => array('$lt' => new MongoDate(strtotime($dateLimit)))
	    	));
	    }
	}
}

?>