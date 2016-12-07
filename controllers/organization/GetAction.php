<?php

class GetAction extends CAction
{
    public function run($id = null, $format = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null) {
		$controller=$this->getController();
		// Get format
		if( $format == Translate::FORMAT_SCHEMA)
	        $bindMap = (empty($id)?TranslateSchema::$dataBinding_allOrganization:TranslateSchema::$dataBinding_organization);
		else
	       $bindMap = (empty($id)?TranslateCommunecter::$dataBinding_allOrganization:TranslateCommunecter::$dataBinding_organization);

      	$result = Api::getData($bindMap, $format, Organization::COLLECTION, $id,$limit, $index, $tags, $multiTags, $key, $insee);

		Rest::json($result);
		Yii::app()->end();
    }
}

?>