<?php 
class NewsTranslator {
	
	const NEWS_CONTRIBUTORS = "newsContributors";
	const NEWS_CREATE_PROJECT = "newsCreateProject";
	const NEWS_CREATE_NEED = "newsCreateNeed";
	public static function convertToNews($object, $useCase){	
		if($useCase ==  self::NEWS_CONTRIBUTORS ){
			$author=Person::getById($object["actor"]["id"]);
			if($object["object"]["objectType"]==Person::COLLECTION){
				$newContributor=Person::getById($object["object"]["id"]);
				$icon="fa-user";
			}
			else{
				$newContributor=Organization::getById($object["object"]["id"]);
				$icon="fa-users";
			}
			$newsObject= array ("_id" => $object["_id"],
								"name" => "New contributor",
								"text"=>$newContributor["name"]."</a> has been invited by ".$author["name"],
								"author"=>$object["actor"]["id"],
								//"date"=>$object["date"],
								"created"=>$object["timestamp"],
								"id"=>$object["target"]["id"],
								"type"=>$object["target"]["objectType"],
								"icon" => $icon
							);
			return $newsObject;
		}
		else if($useCase ==  self::NEWS_CREATE_NEED ){
			$need=Need::getById($object["object"]["id"]);
			$newsObject= array ("_id" => $object["_id"],
								"name" => $need["name"],
								"text"=>"has been created",
								"author"=>$object["actor"]["id"],
								//"date"=>$object["date"],
								"created"=>$object["timestamp"],
								"id"=>$object["object"]["id"],
								"type"=>$object["object"]["objectType"],
								"icon" => "fa-cubes"
							);
			return $newsObject;	
		}	
		else if($useCase ==  self::NEWS_CREATE_PROJECT ){
			$project=Project::getById($object["object"]["id"]);
			if (@$object["tags"]) $tags=$object["tags"]; else $tags="";
			$newsObject= array ("_id" => $object["_id"],
								"name" => $project["name"],
								"text"=>"has been created",
								"author"=>$object["actor"]["id"],
								"tags"=>$tags,
								"created"=>$object["timestamp"],
								"id"=>$object["object"]["id"],
								"type"=>$object["object"]["objectType"],
								"icon" => "fa-lightbulb-o"
							);
			return $newsObject;	
		}	
	}
}