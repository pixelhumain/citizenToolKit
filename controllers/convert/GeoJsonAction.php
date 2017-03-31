<?php
class GeoJsonAction extends CAction {

    public function run($file=null, $type=null, $url=null) {
		
		$controller=$this->getController();

		if (isset($_FILES['file'])) {
			$file = file_get_contents($_FILES['file']['tmp_name']);
		}

		$res = Import::GetParams($file, $type, $url);

		if (isset($res)) {
			Rest::json($res);
		}
			Yii::app()->end();
	}
}

?>