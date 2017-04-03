<?php
class GetAction extends CAction {
    
    public function run($id = null, $format = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null) {
		$controller=$this->getController();
		// Get format
		if( $format == Translate::FORMAT_SCHEMA)
      $bindMap = (empty($id)?TranslateSchema::$dataBinding_allProject:TranslateSchema::$dataBinding_project);
    elseif ($format == Translate::FORMAT_KML) {
      $bindMap = (empty($id)?TranslateKml::$dataBinding_allProject:TranslateKml::$dataBinding_project);
    }
    elseif ($format == Translate::FORMAT_GEOJSON) {
      $bindMap = (empty($id)?TranslateGeoJson::$dataBinding_allProject:TranslateGeoJson::$dataBinding_project);
    }
    else
      $bindMap = (empty($id)?TranslateCommunecter::$dataBinding_allProject:TranslateCommunecter::$dataBinding_project);

      	$result = Api::getData($bindMap, $format, Project::COLLECTION, $id,$limit, $index, $tags, $multiTags, $key, $insee);


    if ($format == Translate::FORMAT_KML) {
      $strucKml = News::getStrucKml();    
      Rest::xml($result, $strucKml,$format);
    } else    
		  Rest::json($result);

		Yii::app()->end();
    }
}