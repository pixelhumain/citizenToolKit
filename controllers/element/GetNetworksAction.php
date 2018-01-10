<?php

class GetNetworksAction extends CAction {

    public function run($type, $id) { 
    	$networks = Network::getListNetworkByUserId($id);
		return Rest::json($networks);
		Yii::app()->end();
	}
}

?>