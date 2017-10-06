<?php
class OrgancityAction extends CAction {

    public function run($url=null) {

		$res = Convert::ConvertOrgancityToPh($url);

  		if (isset($res)) {
			Rest::json($res);
		}

		Yii::app()->end();
	}
}

?>