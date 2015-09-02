<?php
class SearchMembersAutoCompleteAction extends CAction {
	
	const PERSON_ONLY = "personOnly";
	const ORGANIZATION_ONLY = "organizationOnly";
	const MIXTE = "mixte";

	public function run() {
		//$search = str_replace(" ", "\\s",urldecode($_POST['search']));
		$search = trim(urldecode($_POST['search']));
		$query = array( '$or' => 	array( array("email" => new MongoRegex("/".$search."/i")),
									array("name" => new MongoRegex("/".$search."/i"))));

		$limitSearchPerson = 0;
		$limitSearchOrganization = 0;
		$all = array();

		if (@$_POST['searchMode'] == self::PERSON_ONLY) {
			$limitSearchPerson = 12;
		} else if (@$_POST['searchMode'] == self::ORGANIZATION_ONLY) {
			$limitSearchOrganization = 12;
		} else {
			$limitSearchPerson = 6;
			$limitSearchOrganization = 6;
		}
		
		if ($limitSearchPerson > 0) {
			$allCitoyens = PHDB::findAndSort( Person::COLLECTION , $query, array("name" => 1), $limitSearchPerson);
			foreach ($allCitoyens as $key => $value) {
				$person = Person::getById($key);
				$allCitoyens[$key] = $person;
			}
			$all["citoyens"] = $allCitoyens;
			//Update the number of organization to search
			if ($limitSearchOrganization > 0) {
				$limitSearchOrganization = 12 - count($allCitoyens);
			}
		}
		
		if ($limitSearchOrganization > 0) {
			$allOrganization = PHDB::findAndSort( Organization::COLLECTION, $query, array("name" => 1), $limitSearchOrganization, array("_id", "name", "type", "address", "email", "links"));
			foreach ($allOrganization as $key => $value) {
			$logo = Document::getLastImageByKey($key, Organization::COLLECTION, Document::IMG_LOGO);
			if($logo !="")
				$value["logo"]= $logo;
				$allOrganization[$key] = $value;
			}
			$all["organizations"] = $allOrganization;
		}

		Rest::json( $all );
		Yii::app()->end(); 
	}
}