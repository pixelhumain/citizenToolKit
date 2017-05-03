<?php

class GetUrlsAction extends CAction {

    public function run($type, $id) { 
    	$urls = Element::getUrls($id, $type);
		return Rest::json($urls);
		Yii::app()->end();
	}
}

?>