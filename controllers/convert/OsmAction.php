<?php
class OsmAction extends CAction {

    public function run($url) {

    	$res = Convert::convertOsmToPh($url);

    	if (isset($res)) {
			Rest::json($res);
		}

		Yii::app()->end();
	}
}

?>