<?php
class SearchMembersAutoCompleteAction extends CAction {
	
	const PERSON_ONLY = "personOnly";
	const ORGANIZATION_ONLY = "organizationOnly";
	const MIXTE = "mixte";

	public function run() {
		//$search = str_replace(" ", "\\s",urldecode($_POST['search']));
		$search = trim(urldecode($_POST['search']));
		if(@$_POST['elementId'])
			$elementId=$_POST['elementId'];
		else
			$elementId=Yii::app()->session["userId"];
		$query = array( '$or' => 	array( array("email" => new MongoRegex("/".$search."/i")),
									array("name" => new MongoRegex("/".$search."/i"))),
						"_id" => array('$ne' => new MongoId($elementId)));

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
				$person = Person::getSimpleUserById($key);
				$allCitoyens[$key] = $person;
			}
			$all["citoyens"] = $allCitoyens;
			//Update the number of organization to search
			if ($limitSearchOrganization > 0) {
				$limitSearchOrganization = 12 - count($allCitoyens);
			}
		}
		
		if ($limitSearchOrganization > 0) {
			$queryDisabled = array("disabled" => array('$exists' => false));
			$queryOrganization = array('$and' => array($query, $queryDisabled));
			$allOrganization = PHDB::findAndSort( Organization::COLLECTION, $queryOrganization, array("name" => 1), $limitSearchOrganization, array("_id"));
			foreach ($allOrganization as $key => $value) {
				$orga = Organization::getSimpleOrganizationById($key);
				$allOrganization[$key] = $orga;
			}
			$all["organizations"] = $allOrganization;
		}

		Rest::json( $all );
		Yii::app()->end(); 
	}
}