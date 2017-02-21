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
		//echo "adding Cron entry";
	    $new = array(
			"userId" => Yii::app()->session['userId'],
			"status" => self::STATUS_PENDING,
	  		"type"   => $params['type'],
	  		//contextType
	  		//contextId
	  		//just in case can help us out 
	    );
	    
	    if( isset( $params['execTS'] ) ) 
	    	$new['execTS'] = $params['execTS'];

	    if( $params['type'] == self::TYPE_MAIL )
	    	$new = array_merge($new , self::addMailParams($params) );

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
	    $forceMail = Yii::app()->params['forceMailSend'];
	    try{
	    	return Mail::send(array("tpl"=>$params['tpl'],
		         "subject" => $params['subject'],
		         "from"=>$params['from'],																																								
		         "to" => $params['to'],
		         "tplParams" => $params['tplParams']
		    ), $forceMail);																																																																						
	    }catch (Exception $e) {
	    	//throw new CTKException("Problem sending Email : ".$e->getMessage());
			return array( "result"=> false, "msg" => "Problem sending Email : ".$e->getMessage() );
	    }
	    
	}									
	
	public static function processEntry($params){
		//echo "<br/>processing entry ".$params["type"].", id".$params["_id"];
	    if($params["type"] == self::TYPE_MAIL){
			$res = self::processMail( $params );
			//echo "<br/>sendmail : ".$params["subject"].", <br/>result :".((is_array($res)) ? $res["msg"]  : $res);
		}
		if(!is_array($res) && $res){
			//echo "<br/>processing entry ".$params["type"];
			PHDB::remove(self::COLLECTION, array("_id" => new MongoId($params["_id"])));
		}
		else
		{
			//something went wrong with the process
			$msg = ( is_array($res) && isset($res["msg"])) ? $res["msg"] : "";
			PHDB::update(self::COLLECTION, 
    	        		 array("_id" => new MongoId($params["_id"])), 
    	        		 array('$set' => array( "status" =>self::STATUS_FAIL,
    	        								"executedTS" => new MongoDate(),
    	        								"errorMsg" => $msg
    	        								)
    	        		 ));
			//TODO : add notification to system admin
			//explaining the fail
		}

	}
    
	/**
	 * Retreive a limited list of pending cron jobs 
	 * and execute them 
	 * @param $params : a set of information for the document (?to define)
	*/
	public static function processCron($count=5){
		$where = array( "status" => self::STATUS_PENDING, 
						/*'$or' => array( array( "execTS" => array( '$gt' => time())),
										array( "execTS" => array( '$exists'=>-1 ) ) )*/
					);
		$jobs = PHDB::findAndSort( self::COLLECTION, $where, array('execDate' => 1), self::EXEC_COUNT);
		//var_dump($jobs);
		foreach ($jobs as $key => $value) {
			//TODO : cumulé plusieur message au meme email 
			self::processEntry($value);
		}
	}

}
?>