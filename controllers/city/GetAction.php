<?php

class GetAction extends CAction
{
    public function run($id = null, $format = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null) {
		$controller=$this->getController();
		// Get format
		/*if( $format == Translate::FORMAT_SCHEMA)
	        $bindMap = TranslateSchema::$dataBinding_city;
		else*/
	        $bindMap = TranslateCommunecter::$dataBinding_city;
      	$result = Api::getData($bindMap, $format, City::COLLECTION, $id,$limit, $index, $tags, $multiTags, $key, $insee);

		Rest::json($result);
		Yii::app()->end();
    }
}

?>