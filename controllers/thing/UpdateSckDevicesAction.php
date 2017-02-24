<?php

class UpdateSckDevicesAction extends CAction {
	

	public function run() { 


		if ($_SERVER['REQUEST_METHOD'] == 'GET') {//TODO : utiliser GET pour mettre à jours et utiliser les données à jours dans la vue
			//$params = $_GET;
			
			$sckdevices = Thing::updateMetadatas();


		} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') { //TODO : post pour mettre ajours avec un nouveau poi }

	}
	//echo Rest::json( $sckdevices );


	}
}
?>