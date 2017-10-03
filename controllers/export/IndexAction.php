<?php
class IndexAction extends CAction {

    public function run() {

	    $controller=$this->getController();
		$data = PHDB::findOneById($type, $id);
		$res = $data["links"]["memberOf"];
		$res = json_encode($res);
		echo $res;

		Yii::app()->end();
	}
}

?>