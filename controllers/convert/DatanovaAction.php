<?php
class DatanovaAction extends CAction {

    public function run($url) {

    	$res = Convert::convertDatanovaToPh($url);

  		if (isset($res)) {
			Rest::json($res);
		}

		Yii::app()->end();
	}
}

?>