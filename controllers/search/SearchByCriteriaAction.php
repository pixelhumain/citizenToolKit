<?php
class SearchByCriteriaAction extends CAction
{
	private $typeAvailable = array(Person::COLLECTION, Organization::COLLECTION, Event::COLLECTION);
	
	public function run($type) {
		if (! in_array($type, $this->typeAvailable)) 
			throw new CTKException("The type ".$type." can not be managed.");

		$criterias = array();
		foreach ($_POST as $key => $value) {
			$criterias[$key] = $value;
		}

		$search = Search::findByCriterias($type, $criterias, "name", 10);

		foreach ($search as $key => $value) {
  			$profil = Document::getLastImageByKey($key, Person::COLLECTION, Document::IMG_PROFIL);
  			if($profil !="")
					$value["imagePath"]= $profil;
					$search[$key] = $value;
  		}
		return Rest::json(array("result" => true, "list" => $search));
	}
}