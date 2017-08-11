<?php
class DatagouvAction extends CAction {

    public function run($file=null, $type=null, $url=null) {

    	$res = Convert::convertDatagouvToPh($url);

    	if (isset($res)) {
			Rest::json($res);
		}

		Yii::app()->end();
	}
}

?>