<?php 
class Network {

	const COLLECTION = "network";
	const CONTROLLER = "network";
	
	public static function getNetworkJson($networkParams) {
		$paramsAttr = $networkParams.'.json';
		$pathParams =  Yii::app()->getRequest()->getBaseUrl(true)."/themes/network/views/layouts/params/".$paramsAttr;
        $json = file_get_contents($pathParams);
        $params = json_decode($json, true);	
		return $params;
	}
}