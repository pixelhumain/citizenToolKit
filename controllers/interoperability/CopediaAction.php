<?php

// $url_wiki = $_GET['url'];
class CopediaAction extends CAction {

    public function run($url_wiki = null) {

		$controller=$this->getController();
		// $controller->layout = "//layouts/mainSearch";
		echo $controller->renderPartial("copedia", array("url_wiki" => $url_wiki), true);

		// echo "TEST COPEDIA ACTION";

		// Yii::app()->end();
	}
}

?>

