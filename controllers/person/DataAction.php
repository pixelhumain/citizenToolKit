<?php
/**
* retreive dynamically 
*/
class DataAction extends CAction
{
    public function run() {
    	try {
			$person = PHDB::findOneById( Person::COLLECTION , Yii::app()->session['userId'] , array( "tags", "activeTags", "address", "scope" ) );

			$scopes = array();
			if( isset($person["address"]["codeInsee"]) )
				$scopes["codeInsee"] = $person["address"]["codeInsee"];
			if( isset($person["address"]["postalCode"]) )
				$scopes["codePostal"] = $person["address"]["postalCode"];
			if( isset($person["address"]["region"]) )
				$scopes["region"] = $person["address"]["region"];
			
			//TODO : fetch all ascopes in all ressources Orga, people
			//related scopes list

			$res = array("result" => true, 
						 "tags"=>$person["tags"],
						 "scopes"=>$scopes);

		} catch (CTKException $e) {
			$res = array("result" => false, "msg"=>$e->getMessage());
		}

		Rest::json($res);
		exit;
    }
}