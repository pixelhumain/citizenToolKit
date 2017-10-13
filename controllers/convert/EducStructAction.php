<?php
class EducStructAction extends CAction {

    public function run($url = null) {

    	$res = Convert::convertEducStructToPh($url);

  		if (isset($res)) {
			Rest::json($res);
		}

		Yii::app()->end();
	}
}

?>