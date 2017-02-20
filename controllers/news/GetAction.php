<?php
class GetAction extends CAction {
    
    public function run($id = null, $format = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null, $idElement = null, $typeElement = null) {
		$controller=$this->getController();
		// Get format
		if( $format == Translate::FORMAT_RSS)
			$bindMap = TranslateRss::$dataBinding_news;
		else
			$bindMap = TranslateCommunecter::$dataBinding_news;


	    $result = Api::getData($bindMap, $format, News::COLLECTION, $id,$limit, $index, $tags, $multiTags, $key, $insee, null, $idElement, $typeElement);

	    if ((isset($typeElement)) && (isset($idElement))) {	    	
	    
		$element = Element::getByTypeAndId($typeElement , $idElement);
		$name_element = ($element["name"]);
		$element["name"] = ' de ' . $element["name"];		
		$strucRss = News::getStrucChannelRss($element["name"]);

		} else if ((isset($tags))) {
			//$string_tag .= $tags;
			$tags = ' du/des Tags : ' . $tags;
			$strucRss = News::getStrucChannelRss($tags);
		} else {
			$default = 'de tous les éléments du site';
			$strucRss = News::getStrucChannelRss($default);
		}					
				

	    if( $format == Translate::FORMAT_RSS)
			Rest::xml($result, $strucRss);
		else 
			Rest::json($result);

		Yii::app()->end();
    }
}

/*

if( $format == Translate::FORMAT_SCHEMA)
	        $bindMap = (empty($id)?TranslateSchema::$dataBinding_allOrganization:TranslateSchema::$dataBinding_organization);
		else
	       $bindMap = (empty($id)?TranslateCommunecter::$dataBinding_allOrganization:TranslateCommunecter::$dataBinding_organization);

	       */