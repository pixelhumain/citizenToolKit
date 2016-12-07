<?php
class GetAction extends CAction {
    
    public function run($id = null, $format = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null) {
		$controller=$this->getController();
		// Get format
		if( $format == Translate::FORMAT_SCHEMA)
      //$bindMap = TranslateSchema::$dataBinding_person;
      $bindMap = (empty($id)?TranslateSchema::$dataBinding_allPerson:TranslateSchema::$dataBinding_person);
    else if( $format == Translate::FORMAT_PLP )
      $bindMap = TranslatePlp::$dataBinding_person;
    else if( $format == Translate::FORMAT_AS )
      $bindMap = TranslateActivityStream::$dataBinding_person;
    else 
      $bindMap = (empty($id)?TranslateCommunecter::$dataBinding_allPerson:TranslateCommunecter::$dataBinding_person);

    $result = Api::getData($bindMap, $format, Person::COLLECTION, $id,$limit, $index, $tags, $multiTags, $key, $insee);

		Rest::json($result);
		Yii::app()->end();
    }
}