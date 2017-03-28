<?php
class IndexAction extends CAction {

    public function run($id, $type) {

	    $controller=$this->getController();

		// var_dump($id);
		// var_dump($type);

		$data = PHDB::findOneById($type, $id);

		$res = $data["links"]["memberOf"];



		$res = json_encode($res);



		echo $res;
		// $res = implode(";", $res);

		// var_dump($res);

		// var_dump($data["links"]["memberOf"]);
		


		Yii::app()->end();
	


	}
}

?>