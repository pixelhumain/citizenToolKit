<?php

class NetworkAction extends CAction
{
    public function run($id, $type) {
		$controller=$this->getController();
		// Get format
		$result = Element::myNetwork($id, $type);

		Rest::json($result);
		Yii::app()->end();
    }
}

?>