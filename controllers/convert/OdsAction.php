<?php
class OdsAction extends CAction {

    public function run($url) {

    	$res = Convert::convertOdsToPh($url);

  		if (isset($res)) {
			Rest::json($res);
		}

		Yii::app()->end();
	}
}

?>