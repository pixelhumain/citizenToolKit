<?php
/**
 * Exception for CTK business Error
 */
class Utils {
	
	public static function getServerInformation() {
		$logoColor = "";
		$platform = "";
		
		if( $_SERVER['SERVER_NAME'] == "127.0.0.1" || $_SERVER['SERVER_NAME'] == "localhost" ){
			$logoColor = "#04b8ec";
			$platform = "LOCAL DEV";
		} else if( $_SERVER['SERVER_NAME'] == "test.communecter.org" ){
			$logoColor = "#e4334b";
			$platform = "TEST";
		} else if( $_SERVER['SERVER_NAME'] == "qa.communecter.org" ){
			$logoColor = "#92be1f";
			$platform = "QA";
		} else if( $_SERVER['SERVER_NAME'] == "communecter.org" ){
			$logoColor = "white";
			$platform = "PROD";
		}

		return array("logoColor" => $logoColor, "platform" => $platform);
	}

	public static function getServerName() {
		$serverInfo = self::getServerInformation();
		$serverName = "";
		if ($serverInfo["platform"] != "PROD") {
			$serverName = $serverInfo["platform"];
		}

		return $serverName;
	}

}