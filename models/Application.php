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

}
?>