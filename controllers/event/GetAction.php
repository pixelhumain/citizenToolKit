<?php

class GetAction extends CAction
{
    public function run($id = null, $format = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null) {
		$controller=$this->getController();
		// Get format
		if( $format == Translate::FORMAT_SCHEMA)
	        $bindMap = TranslateSchema::$dataBinding_event;
		else
	        $bindMap = TranslateCommunecter::$dataBinding_event;

      	$result = Api::getData($bindMap, $format, Event::COLLECTION, $id,$limit, $index, $tags, $multiTags, $key, $insee);

		Rest::json($result);
		Yii::app()->end();
    }
}

?>