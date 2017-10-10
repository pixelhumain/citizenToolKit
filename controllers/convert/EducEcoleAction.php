<?php
class EducEcoleAction extends CAction {

    public function run($url = null) {

    	$res = Convert::convertEducEcoleToPh($url);

  		if (isset($res)) {
			Rest::json($res);
		}

		Yii::app()->end();
	}
}

?>