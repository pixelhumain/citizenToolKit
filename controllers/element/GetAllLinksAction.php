<?php

class GetAllLinksAction extends CAction {
/**
* Dashboard Organization
*/
    public function run($type, $id) { 
    	//$controller=$this->getController();

		$links=$_POST["links"];
		$contextMap = Element::getAllLinks($links,$type);
		return Rest::json($contextMap);
		Yii::app()->end();
	}
}

?>