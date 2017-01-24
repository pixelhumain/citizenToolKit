<?php
class GetAction extends CAction {
    
    public function run($id = null, $format = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null) {
		$controller=$this->getController();
		// Get format
		if( $format == Translate::FORMAT_SCHEMA)
      $bindMap = (empty($id)?TranslateSchema::$dataBinding_allProject:TranslateSchema::$dataBinding_project);
    else
      $bindMap = (empty($id)?TranslateCommunecter::$dataBinding_allProject:TranslateCommunecter::$dataBinding_project);

      	$result = Api::getData($bindMap, $format, Project::COLLECTION, $id,$limit, $index, $tags, $multiTags, $key, $insee);

		Rest::json($result);
		Yii::app()->end();
    }
}