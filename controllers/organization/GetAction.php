<?php

class GetAction extends CAction
{
    public function run($id = null, $format = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null) {
		$controller=$this->getController();
		// Get format

		if( $format == Translate::FORMAT_SCHEMA)
	        $bindMap = (empty($id) ? TranslateSchema::$dataBinding_allOrganization : TranslateSchema::$dataBinding_organization);
		else if ($format == Translate::FORMAT_KML)
			$bindMap = (empty($id) ? TranslateKml::$dataBinding_allOrganization : TranslateKml::$dataBinding_organization);
		else if ($format == Translate::FORMAT_GEOJSON) 
			$bindMap = (empty($id) ? TranslateGeoJson::$dataBinding_allOrganization : TranslateGeoJson::$dataBinding_organization);
		else if ($format == Translate::FORMAT_JSONFEED)
			$bindMap = TranslateJsonFeed::$dataBinding_allOrganization;
			// $bindMap = (empty($id) ? TranslateJsonFeed::$dataBinding_allOrganization : TranslateGeoJson::$dataBinding_organization);
		else
	       $bindMap = (empty($id) ? TranslateCommunecter::$dataBinding_allOrganization : TranslateCommunecter::$dataBinding_organization);

      	$result = Api::getData($bindMap, $format, Organization::COLLECTION, $id,$limit, $index, $tags, $multiTags, $key, $insee);

      	if ($format == Translate::FORMAT_KML) {
			$strucKml = News::getStrucKml();		
			Rest::xml($result, $strucKml,$format);
		} else if ($format == "csv") {
			$res = $result["entities"];
			$head = Export::toCSV($res, ";", "'");
		} else
			Rest::json($result);

		Yii::app()->end();
    }
}



?>