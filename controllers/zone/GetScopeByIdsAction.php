<?php

class GetScopeByIdsAction extends CAction
{
    public function run() {
		$controller=$this->getController();
		$params = Zone::getScopeByIds($_POST);
		Rest::json($params);
		Yii::app()->end();
	}
}

?>