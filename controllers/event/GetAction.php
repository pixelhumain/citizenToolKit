<?php

class GetAction extends CAction
{
    public function run($id = null, $format = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null) {
		$controller=$this->getController();
		// Get format
		if( $format == Translate::FORMAT_SCHEMA){
	        $bindMap = (empty($id)?TranslateSchema::$dataBinding_allEvent:TranslateSchema::$dataBinding_event);
		}
	    elseif ($format == Translate::FORMAT_KML) {
			$bindMap = (empty($id)?TranslateKml::$dataBinding_allEvent:TranslateKml::$dataBinding_event);
		}
		elseif ($format == Translate::FORMAT_GEOJSON) {
			$bindMap = (empty($id)?TranslateGeoJson::$dataBinding_allEvent:TranslateGeoJson::$dataBinding_event);
		}
		else
	       $bindMap = (empty($id)?TranslateCommunecter::$dataBinding_allEvent:TranslateCommunecter::$dataBinding_event);



      	$result = Api::getData($bindMap, $format, Event::COLLECTION, $id,$limit, $index, $tags, $multiTags, $key, $insee);

      	if ($format == Translate::FORMAT_KML) {
			$strucKml = News::getStrucKml();		
			Rest::xml($result, $strucKml,$format);
		} else
			Rest::json($result);

		Yii::app()->end();
    }
}


?>