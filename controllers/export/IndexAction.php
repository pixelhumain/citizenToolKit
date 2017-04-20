<?php
class IndexAction extends CAction {

    public function run() {

	    $controller=$this->getController();
// <<<<<<< HEAD
// 		$data = Export::getMemberOf($_POST["id"], $_POST["type"]);
// 		$test = Export::toCSV($data, ';', '"');
// =======

		$data = PHDB::findOneById($type, $id);
		$res = $data["links"]["memberOf"];
		$res = json_encode($res);
		echo $res;

// >>>>>>> wikipedia
		Yii::app()->end();
	}
}

?>