<?php
class GetAction extends CAction {

	public function run($id = null, $format = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null) {
		$controller=$this->getController();
		// Get format
		if( $format == Translate::FORMAT_SCHEMA)
			$bindMap = (empty($id) ? TranslateSchema::$dataBinding_allPerson : TranslateSchema::$dataBinding_person);
		else if( $format == Translate::FORMAT_PLP )
			$bindMap = TranslatePlp::$dataBinding_person;
		else if( $format == Translate::FORMAT_AS )
			$bindMap = TranslateActivityStream::$dataBinding_person;
		else if( $format == Translate::FORMAT_KML)
			$bindMap = (empty($id) ? TranslateKml::$dataBinding_allPerson : TranslateKml::$dataBinding_person);
		else if( $format == Translate::FORMAT_GEOJSON)
			$bindMap = (empty($id) ? TranslateGeoJson::$dataBinding_allPerson : TranslateGeoJson::$dataBinding_person);
		else if( $format == Translate::FORMAT_MD)
			$bindMap = "person";
		else if( $format == Translate::FORMAT_TREE)
			$bindMap = "person";
		else if ($format == Translate::FORMAT_JSONFEED)
			$bindMap = TranslateJsonFeed::$dataBinding_allPerson;
		else if ( $format == "valueflows")
              $bindMap = TranslateValueFlows::$dataBinding_agent;
		else 
			$bindMap = (empty($id) ? TranslateCommunecter::$dataBinding_allPerson : TranslateCommunecter::$dataBinding_person);

		$result = Api::getData($bindMap, $format, Person::COLLECTION, $id,$limit, $index, $tags, $multiTags, $key, $insee);


		
		
		if ($format == Translate::FORMAT_KML) {
			$strucKml = News::getStrucKml();    
			Rest::xml($result, $strucKml,$format);
		} 
		else if ($format == Translate::FORMAT_MD) {
			//header('Content-Type: text/markdown');
			echo $result;
		}
		else
			Rest::json($result);

		Yii::app()->end();
	}
}