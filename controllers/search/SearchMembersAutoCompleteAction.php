<?php
class SearchMembersAutoCompleteAction extends CAction {
	
	const PERSON_ONLY = "personOnly";
	const ORGANIZATION_ONLY = "organizationOnly";
	const MIXTE = "mixte";


	
	//$queryFullTxt = array( '$or' => array( array("name" => new MongoRegex("/.*{$searchRegExp}.*/i")),


	public function run() {
		//$search = str_replace(" ", "\\s",urldecode($_POST['search']));
		$search = trim(urldecode($_POST['search']));

		if(@$_POST['elementId'])
			$elementId=$_POST['elementId'];
		else
			$elementId=Yii::app()->session["userId"];

		$searchAccent = Api::accentToRegex($search);
		
		$query = array( '$or' => 	array( array("email" => new MongoRegex("/".$search."/i")),
									array("name" => new MongoRegex("/.*{$searchAccent}.*/i"))),
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


	/**
	* Returns a string with accent to REGEX expression to find any combinations
	* in accent insentive way
	*
	* @param string $text The text.
	* @return string The REGEX text.
	*/

	static public function accentToRegex($text)
	{

		$from = str_split(utf8_decode('ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËẼÌÍÎÏĨÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëẽìíîïĩðñòóôõöøùúûüýÿ'));
		$to   = str_split(strtolower('SOZsozYYuAAAAAAACEEEEEIIIIIDNOOOOOOUUUUYsaaaaaaaceeeeeiiiiionoooooouuuuyy'));
		//‘ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËẼÌÍÎÏĨÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëẽìíîïĩðñòóôõöøùúûüýÿaeiouçAEIOUÇ';
		//‘SOZsozYYuAAAAAAACEEEEEIIIIIDNOOOOOOUUUUYsaaaaaaaceeeeeiiiiionoooooouuuuyyaeioucAEIOUÇ';
		$text = utf8_decode($text);
		$regex = array();

		foreach ($to as $key => $value)
		{
			if (isset($regex[$value]))
				$regex[$value] .= $from[$key];
			else 
				$regex[$value] = $value;
		}

		foreach ($regex as $rg_key => $rg)
		{
			$text = preg_replace("/[$rg]/", "_{$rg_key}_", $text);
		}

		foreach ($regex as $rg_key => $rg)
		{
			$text = preg_replace("/_{$rg_key}_/", "[$rg]", $text);
		}
		return utf8_encode($text);
	}

}