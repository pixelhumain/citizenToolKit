<?php

class GetAction extends CAction
{
    public function run($id = null, $format = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null, $fullRepresentation = "true") {
		$controller=$this->getController();
		$result = Tags::getActiveTags();
		Rest::json($result);

		Yii::app()->end();
    }
}



?>