<?php 
class NewsTranslator {
	
	public static function convertToNews($object, $useCase){	
		if($useCase=="newsContributors")
		$newsObject= array ("_id" => $object["_id"],
							"name" => "New contributor",
							"text"=>"has been invited",
							"author"=>$object["actor"]["id"],
							//"date"=>$object["date"],
							"created"=>$object["timestamp"],
							"id"=>$object["target"]["id"],
							"type"=>$object["target"]["objectType"],
							"icon" => "fa-user"
						);
		return $newsObject;

	}
}