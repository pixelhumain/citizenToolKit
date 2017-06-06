<?php
class OSMGetNodeAction extends CAction {

    public function run($nodeID = null) {

    	$controller=$this->getController();

		// $controller=$this->getController();
		// ini_set('display_errors', 1);
		// /home/damien/workspace/modules/co2/views/interoperability/Services_Openstreetmap/vendor

		require __DIR__ . '/Services_Openstreetmap/vendor/autoload.php';
		// echo "------ OSM API TEST ------<br />";
		require_once 'Services/OpenStreetMap.php';
		// $config = array(
		// 'user' => 'd.grondin@rt-iut.re',
		// 'password' => 'A8i vmIg',
		// );

		// header("Content-type: text/xml");
		$osm = new Services_OpenStreetMap();
		$res = $osm->getNode($nodeID);

		$myText = print_r($res,true);

		echo $myText;		

		// if (isset($res)) {
		// 	Rest::xmlWellFormed($res);
		// }

		// var_dump($results);

		// function objectToArray ($object) {
		//     if(!is_object($object) && !is_array($object))
		//         return $object;

		//     return array_map('objectToArray', (array) $object);
		// }

		// echo preg_replace("/\n\r/", "", $results) ;
		// echo ltrim($results);

		// $osm = new Services_OpenStreetMap($config);
		// echo $osm->getNode(1339920531);

		// var_dump($osm->getUser());
		// var_dump($osm->getUserById($_GET["userID"]));

		// $changeset = $osm->createChangeset();
		// $changeset->begin("Add tags in node");
		// $ways = $osm->getWays($wayId, $way2Id);

		// $node->setTag('amenity', 'hospital');
		// $changeset->add($node);

		// foreach ($ways as $way) {
		//     $way->setTag('highway', 'residential');
		//     $way->setTag('lit', 'yes');
		//     $changeset->add($way);
		// }
		// $changeset->commit();

		Yii::app()->end();
	}
}

?>

