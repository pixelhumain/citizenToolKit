<?php

class GetAllLinksAction extends CAction {
/**
* Dashboard Organization
*/
    public function run($type, $id) { 
    	//$controller=$this->getController();
		$element = Element::getByTypeAndId($type, $id);
		$links=@$element["links"];
		$contextMap = Element::getAllLinks($links,$type, $id);
		return Rest::json($contextMap);
		Yii::app()->end();
	}
}

?>