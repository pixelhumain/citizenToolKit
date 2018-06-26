<?php

class Application {
	const COLLECTION = "applications";
	const ICON = "fa-th";
	

	public static function loadDBAppConfig($key = null) {
		if(!$key){
			$key = "devParams";
			if( stripos($_SERVER['SERVER_NAME'], "127.0.0.1") === false && 
				stripos($_SERVER['SERVER_NAME'], "localhost") === false && 
				stripos($_SERVER['SERVER_NAME'], "::1") === false && 
				stripos($_SERVER['SERVER_NAME'], "localhost:8080") === false && 
				strpos($_SERVER['SERVER_NAME'], "local.")!==0 &&
				strpos($_SERVER['SERVER_NAME'], "dev.")!==0 )
				$key = "prodParams"; // PROD
		}
		$params = PHDB::findOne( self::COLLECTION, array( "key" => $key ));
		if(!$params)
			throw new CHttpException(403,Yii::t('error','Missing Configs db.applications.key == '.$key.'exists'));
		else {
			//load into application params main config map
			Yii::app()->params["mangoPay"] = array(
				"ClientId" => $params["mangoPay"]["ClientId"],
				"ClientPassword" => $params["mangoPay"]["ClientPassword"],
				"TemporaryFolder" => $params["mangoPay"]["TemporaryFolder"]
			);
			
		}
	}


	public static function getToken($apiName) {
		//var_dump("getToken");
		$params = PHDB::findOne( 	self::COLLECTION,
									array( "key" => "devParams") ,
									//array("_id" => new MongoId(Yii::app()->params["applicationId"])), 
									array("api") );
		$res = null ;
		if(!empty($params["api"]) && !empty($params["api"][$apiName]) && !empty($params["api"][$apiName]["token"]) ) {
			$res = $params["api"][$apiName];
			//Yii::app()->params["api".$apiName] = $res;
		}
		return $res ;
	}

	public static function saveToken($apiName, $token) {
		$params = PHDB::findOne( 	self::COLLECTION,
									array( "key" => "devParams") , 
									//array("_id" => new MongoId(Yii::app()->params["applicationId"])), 
									array("api") );
		if(empty($params["api"]))
			$params["api"] = array();

		if(empty($params["api"][$apiName]))
			$params["api"][$apiName] = array();

		$res = array("result" => false);
		if(!empty($token["expires_in"])){
			$params["api"][$apiName]["token"] = $token;

			$params["api"][$apiName]["expireToken"] = time()+$token["expires_in"];
			Yii::app()->params["token".$apiName] = $token;
			$res = PHDB::update(self::COLLECTION,
							array( "key" => "devParams") ,
							//array("_id" => new MongoId(Yii::app()->params["applicationId"])) ,
							array('$set' => array("api" => $params["api"]))
						);
		}
		

		return $res ;
	}

}
?>