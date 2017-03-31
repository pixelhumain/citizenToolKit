<?php
class IndexAction extends CAction {

    public function run() {

	    $controller=$this->getController();
		$data = Export::getMemberOf($_POST["id"], $_POST["type"]);
		$test = Export::toCSV($data, ';', '"');
		Yii::app()->end();
	}
}

?>