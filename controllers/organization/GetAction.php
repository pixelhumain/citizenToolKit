<?php

class GetAction extends CAction
{
    public function run($id = null, $format = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null) {
		$controller=$this->getController();
		// Get format
		if( $format == Translate::FORMAT_SCHEMA)
	        $bindMap = TranslateSchema::$dataBinding_organization;
		else
	        $bindMap = TranslateCommunecter::$dataBinding_organization;

      	$result = Api::getData($bindMap, $format, Organization::COLLECTION, $id,$limit, $index, $tags, $multiTags, $key, $insee);

		Rest::json($result);
		Yii::app()->end();
    }
}

?>