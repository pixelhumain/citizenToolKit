<?php

class UpdateSckDevicesAction extends CAction {
	//TODO mettre une verification de compte admin pour ce controller
	public function run($deviceId=null,$macId=null,$id=null,$atSC=null) { 

		$res = array();
		if ($_SERVER['REQUEST_METHOD'] == 'GET') {
			
			$res["devicesmetadata"] = Thing::updateMetadatas(null,$atSC);
			$res["APIMetadata"] = Thing::updateSCKAPIMetadata();

		} else if ($_SERVER['REQUEST_METHOD'] == 'POST') { 

			$res['post'] =Thing::updateMultipleMetadata($_POST['listbd']);

			//TODO : Utiliser directement l'id de la metadata pour mettre a jours, si pas de métadata deviceid et macid pour créer un nouveau document. 
			// actuellement teste si l'adress mac est bien formatté 
			/*if((preg_match('/^([0-9a-f]{2}[:]){5}([0-9a-f]{2})$/', $macId)==1) && !empty($deviceId)){
				$res = Thing::updateOneMetadata($deviceId,$macId);
			}else{


				$res["error"] = "Address mac incorrect ou vide" ;
			}*/

		}
		//var_dump($res);
		Rest::json($res);
		Yii::app()->end();
	}
}
?>