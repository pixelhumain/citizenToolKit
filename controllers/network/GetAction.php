<?php

class GetAction extends CAction
{
    public function run($id = null) {
		$controller=$this->getController();
		// Get format
		header("Access-Control-Allow-Origin: *");
		$result = Network::getById($id);
		Rest::json($result);
		Yii::app()->end();
    }
}



?>