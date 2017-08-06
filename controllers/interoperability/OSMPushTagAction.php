<?php
class OSMPushTagAction extends CAction {

    public function run($nodeID = null) {

    	$controller=$this->getController();

		ini_set('display_errors', 1);
		require __DIR__ . '/Services_Openstreetmap/vendor/autoload_osm.php';
		require_once 'Services/OpenStreetMap.php';

		$config = array(
			'user' => $_POST['user'],
			'password' => $_POST['pwd'],
		);

		$tag_to_push = "";
		$tag_to_push2 = "";

		if (!empty($_POST['tagvalue'])) {
			$tag_to_push = $_POST['tagvalue'];
		}

		if (!empty($_POST['tagnewname'])) {
			$tag_to_push2 = $_POST['tagnewvalue'];
		}

		// var_dump($_POST);
		// var_dump($tag_to_push);
		// var_dump($tag_to_push2);

		$osm = new Services_OpenStreetMap($config);

		$changeset = $osm->createChangeset();
		$changeset->begin("Add tag " .$_POST['tagname'] . " from Communecter (www.communecter.org)");
		$node = $osm->getNode($_POST['nodeID']);

		if (!empty($tag_to_push)) {
			$node->setTag($_POST['tagname'], $tag_to_push);
		}

		if (!empty($tag_to_push2)) {
			$node->setTag($_POST['tagnewname'], $tag_to_push2);
		}

		$changeset->add($node);

		$changeset->commit();

		Rest::json(array("result"=>true, "Vous avez ajouté le tag ".$_POST['tagname'] . " avec comme valeur : " . $tag_to_push . "<br/>"));

		Yii::app()->end();
	}
}

?>

