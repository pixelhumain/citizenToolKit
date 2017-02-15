<?php 
class Network {

	const COLLECTION = "network";
	const CONTROLLER = "network";
	
	/**
	 * Récupère le fichier de configuration du network et retourne en tableau json
	 * Le fichier de conf peut être en local sur le serveur ou accessible depuis une URL
	 * TODO : Vérifie le bon formatage du fichier
	 * @return json_decode array
	 */
	public static function getNetworkJson($networkParams) {
		//error_log("NETWOOOOOOOORK PARAMS : ".$networkParams);
		$configPath = "";
		/*if(@$_GET["network"]) {
            Yii::app()->params['networkParams'] = $_GET["network"];
        }*/
        
        if (empty($networkParams)) {
			$configPath = "default";
		} else {
			$configPath = $networkParams;
		}

		if ( stripos($configPath, "http") === false ) {
			error_log("chargement du fichier de config en local");
			$configPath =  Yii::app()->theme->basePath . '/views/layouts/params/'.$configPath.".json";
		}

		try {
			$json = file_get_contents($configPath, null, null, 0, 10000);
			if ($json === false) 
				throw new CHttpException(404, "Impossible to find the network configuration file.");
		} catch (Exception $e) {
    		throw new CHttpException(404, "Error Reading the network configuration file.");
		}

		return json_decode($json, true);
	}
}