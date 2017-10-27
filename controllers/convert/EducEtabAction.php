<?php
class EducEtabAction extends CAction {

    public function run($url = null) {

    	$res = Convert::convertEducEtabToPh($url);

  		if (isset($res)) {
			Rest::json($res);
		}

		Yii::app()->end();
	}
}

?>