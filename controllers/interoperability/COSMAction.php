<?php
class COSMAction extends CAction {
    public function run() {
		$controller=$this->getController();
		// $controller->layout = "//layouts/mainSearch";
		echo $controller->renderPartial("co-osm", array(), true);
	}
}
?>

