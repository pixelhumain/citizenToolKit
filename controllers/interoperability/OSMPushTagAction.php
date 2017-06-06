<?php
class OSMPushTagAction extends CAction {

    public function run($nodeID = null) {

    	$controller=$this->getController();

		// $version = '@package_version@';
		// if (strstr($version, 'package_version')) {
		//     set_include_path(dirname(dirname(__FILE__)) . PATH_SEPARATOR . get_include_path());
		// }

		// require_once 'Services/OpenStreetMap.php';

		$osm = new Services_OpenStreetMap();

		ini_set('display_errors', 1);
		require __DIR__ . '/../vendor/autoload.php';
		require_once 'Services/OpenStreetMap.php';

		// $config = array(
		// 'user' => 'd.grondin@rt-iut.re',
		// 'password' => 'A8i vmIg',
		// );

		$config = array(
			'user' => $_POST['user'],
			'password' => $_POST['pwd'],
		);

		$osm = new Services_OpenStreetMap($config);

		$changeset = $osm->createChangeset();
		$changeset->begin("Add tag " .$_POST['tagname'] . " with value : " . $_POST['tagvalue'] . "from COMMUNECTER");

		$node = $osm->getNode($_POST['nodeID']);
		echo $node;
		$node->setTag($_POST['tagname'], $_POST['tagvalue']);
		$changeset->add($node);
		$changeset->commit();

		echo "Vous avez ajouté le tag".$_POST['tagname'] . "avec comme valeur : " . $_POST['tagname'] . " from COMMUNECTER<br/>";
		echo "Merci d'avoir contribuez à enrichir l'Open Common Database !!! ";

		Yii::app()->end();
	}
}

?>

