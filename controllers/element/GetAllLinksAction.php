<?php

class GetAllLinksAction extends CAction {
/**
* Dashboard Organization
*/
    public function run($type, $id) { 
    	//$controller=$this->getController();

		$links=@$_POST["links"];
		$contextMap = Element::getAllLinks($links,$type, $id);
		return Rest::json($contextMap);
		Yii::app()->end();
	}
}

?>