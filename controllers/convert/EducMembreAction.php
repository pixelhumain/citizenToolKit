<?php
class EducMembreAction extends CAction {

    public function run($url = null) {

    	$res = Convert::convertEducMembreToPh($url);

  		if (isset($res)) {
			Rest::json($res);
		}

		Yii::app()->end();
	}
}

?>