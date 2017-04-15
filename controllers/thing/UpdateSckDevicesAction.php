<?php

class UpdateSckDevicesAction extends CAction {
	//TODO mettre une verification de compte admin pour ce controller
	public function run($deviceId=null,$macId=null,$id=null,$atSC=null) { 

		$res = array();
		if ($_SERVER['REQUEST_METHOD'] == 'GET') {
			
			$res[] = Thing::updateMetadatas(null,$atSC);
			$res[] = Thing::updateSCKAPIMetadata();

		} else if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
			if((preg_match('/^([0-9a-f]{2}[:-]){5}([0-9a-f]{2})$/', $macId)==1) && !empty($deviceId)){
				$res = Thing::updateOneMetadata($deviceId,$macId);
			}
		}

		Rest::json($res);
		Yii::app()->end();
	}
}
?>