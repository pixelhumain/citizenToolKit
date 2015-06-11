<?php
/*
This Class defines asynchronous action to be executed by a recurent Cron Process
things like : 
- sending email 
- background batch jobs 
- data analysis & statistic calculation 
- data clean ups
- background reminders and notifications
*/
class Cron {

	const COLLECTION = "cron";
	
	const TYPE_MAIL = "mail";

	const STATUS_PENDING = "pending";
	const STATUS_FAIL = "fail";
	const STATUS_DONE = "done";

	const EXEC_COUNT = 5;
	/**
	 * adds an entry into the cron collection
	 * @param $params : a set of information for a proper cron entry
	*/
	public static function save($params){
	    $new = array(
			"userId" => Yii::app()->session['userId'],
			"status" =>self::STATUS_PENDING,
	  		"type" => $params['type'],
	    );
	    
	    if( isset( $params['execTS'] ) ) 
	    	$new['execTS'] = $params['execTS'];

	    if( $params['type'] == self::TYPE_MAIL )
	    	array_merge($new , self::addMailParams($params) );

	    PHDB::insert(self::COLLECTION,$new);
	}
	
    /**
	 * generic mail fields 
	*/
	private static function addMailParams($params){
	    return array(
			//mail specific parameters
	  		"tpl" => $params['tpl'],
	  		"subject" => $params['subject'],
	  		"from" => $params['from'],
	  		"to" => $params['to'],
	  		"tplParams" => $params['tplParams']
	    );
	}

	//TODO return result 
	public static function processMail($params){
	    Mail::send(array("tpl"=>$params['tpl'],
	         "subject" => $params['subject'],
	         "from"=>$params['from'],
	         "to" => $params['to'],
	         "tplParams" => $params['tplParams']
	    ));
	}
	
	public static function processEntry($params){
	    if($value["type"] == self::TYPE_MAIL){
			echo "sendmail : ".$value["name"];
			$res = self::processMail( $value );
		}
		if($res){
			PHDB::update(self::COLLECTION, 
    	        		 array("_id" => new MongoId($params["_id"])), 
    	        		 array('$set' => array( "status" =>self::STATUS_DONE,
    	        								"executedTS" =>time(),
    	        								)
    	        		 ));
		}
		else
		{
			//something went wrong with the process
			PHDB::update(self::COLLECTION, 
    	        		 array("_id" => new MongoId($params["_id"])), 
    	        		 array('$set' => array( "status" =>self::STATUS_FAIL,
    	        								"executedTS" =>time(),
    	        								)
    	        		 ));
			//TODO : add notification to system admin
		}

	}
    
	/**
	 * Retreive a limited list of pending cron jobs 
	 * and execute them 
	 * @param $params : a set of information for the document (?to define)
	*/
	public static function processCron($count=5){
		$where = array( "status" => self::STATUS_PENDING, 
						"execTS" => array( "$gt" => time() )
					);
		$jobs = PHDB::findAndSort( self::COLLECTION, $where, array('execDate' => 1), self::EXEC_COUNT);
		foreach ($jobs as $key => $value) {
			self::processEntry($value);
		}
	}

}
?>