<?php
class OSMGetNodeAction extends CAction {

    public function run($nodeID = null) {

    	$controller=$this->getController();

		require __DIR__ . '/Services_Openstreetmap/vendor/autoload.php';
		require_once 'Services/OpenStreetMap.php';

		$osm = new Services_OpenStreetMap();
		$res = $osm->getNode($nodeID);

		$myText = print_r($res,true);

		echo $myText;		

		Yii::app()->end();
	}
}

?>

