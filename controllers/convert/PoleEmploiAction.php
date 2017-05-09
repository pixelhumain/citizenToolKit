<?php
class PoleEmploiAction extends CAction {

    public function run($url) {

    	$res = Convert::convertPoleEmploiToPh($url);

  		if (isset($res)) {
			Rest::json($res);
		}

		Yii::app()->end();
	}
}

?>