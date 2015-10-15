<?php 
class NewsTranslator {
	
	const NEWS_CONTRIBUTORS = "newsContributors";
	const NEWS_CREATE_PROJECT = "newsCreateProject";
	const NEWS_CREATE_NEED = "newsCreateNeed";
	const NEWS_CREATE_EVENT = "newsCreateEvent";
	const NEWS_CREATE_ORGANIZATION = "newsCreateOrganization";
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
								"text"=>"has been invited by ",
								"author"=>array("id" => $object["actor"]["id"]), 
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
								"author"=>array("id" => $object["actor"]["id"]),
								//"date"=>$object["date"],
								"created"=>$object["timestamp"],
								"id"=>$object["object"]["id"],
								"type"=>$object["object"]["objectType"],
								"icon" => "fa-cubes"
							);
			return $newsObject;	
		}	
		else if($useCase ==  self::NEWS_CREATE_PROJECT ){
			$project=Project::getById((string)$object["object"]["id"]);
			$image = Document::getLastImageByKey((string)$object["object"]["id"], Project::COLLECTION, Document::IMG_SLIDER);
			$author=Person::getById($object["actor"]["id"]);
			$authorImage = Document::getLastImageByKey((string) $object["actor"]["id"], Person::COLLECTION, Document::IMG_PROFIL);
			if ($object["target"]["objectType"] == Organization::COLLECTION){
				$target=Organization::getById($object["target"]["id"]);
				$targetImage = Document::getLastImageByKey((string) $object["target"]["id"], Organization::COLLECTION, Document::IMG_PROFIL);
			} else {
				$target=$author;
				$targetImage=$authorImage;
			}
			if (@$object["tags"]) $tags=$object["tags"]; else $tags="";
			$newsObject= array ("_id" => $object["_id"],
								"name" => $project["name"],
								"text"=> trim(preg_replace('/<[^>]*>/', ' ',(substr($project["description"],0 ,100 )))),
								"author"=> array(
									"id" => $object["actor"]["id"],
									"name" => $author["name"],
									"profilImageUrl" => $authorImage
								),
								"target" => array(
									"id" => $object["target"]["id"],
									"type" => $object["target"]["objectType"],
									"name" => $target["name"],
									"profilImageUrl" => $targetImage
								),
								"address" => $project["address"],
								"tags"=>$tags,
								"created"=>$object["timestamp"],
								"id"=>(string)$object["object"]["id"],
								"type"=>$object["object"]["objectType"],
								"imageBackground" => $image,
								"icon" => "fa-lightbulb-o"
							);
			return $newsObject;	
		}	
		else if($useCase ==  self::NEWS_CREATE_EVENT ){
			$event=Event::getById((string)$object["object"]["id"]);
			$image = Document::getLastImageByKey((string) $object["object"]["id"], Event::COLLECTION, Document::IMG_PROFIL);
			$author=Person::getById($object["actor"]["id"]);
			$authorImage = Document::getLastImageByKey((string) $object["actor"]["id"], Person::COLLECTION, Document::IMG_PROFIL);
			if ($object["target"]["objectType"] == Organization::COLLECTION){
				$target=Organization::getById($object["target"]["id"]);
				$targetImage = Document::getLastImageByKey((string) $object["target"]["id"], Organization::COLLECTION, Document::IMG_PROFIL);

			}
			else if ($object["target"]["objectType"]== Project::COLLECTION){
				$target=Project::getById($object["target"]["id"]);
				$targetImage = Document::getLastImageByKey((string) $object["target"]["id"], Project::COLLECTION, Document::IMG_SLIDER);
			}
			else {
				$target=$author;
				$targetImage=$authorImage;
			}
			if (@$object["tags"]) $tags=$object["tags"]; else $tags="";
			$newsObject= array ("_id" => $object["_id"],
								"name" => $event["name"],
								"text"=> substr(isset($event["description"]) ? $event["description"] : "",0 ,100 ),
								"author"=> array(
									"id" => $object["actor"]["id"],
									"name" => $author["name"],
									"profilImageUrl" => $authorImage
								),
								"target" => array(
									"id" => $object["target"]["id"],
									"type" => $object["target"]["objectType"],
									"name" => $target["name"],
									"profilImageUrl" => $targetImage
								),
								"address" => $event["address"],
								"tags"=>$tags,
								"created"=>$object["timestamp"],
								"id"=>(string) $object["object"]["id"],
								"imageBackground" => $image,
								"type"=>$object["object"]["objectType"],
								"icon" => "fa-calendar"
							);
			return $newsObject;	
		}	
		else if($useCase ==  self::NEWS_CREATE_ORGANIZATION ){
			$orga=Organization::getById($object["object"]["id"]);
			$image = Document::getLastImageByKey((string)$object["object"]["id"], Organization::COLLECTION, Document::IMG_PROFIL);
			$author=Person::getById($object["actor"]["id"]);
			$authorImage = Document::getLastImageByKey((string) $object["actor"]["id"], Person::COLLECTION, Document::IMG_PROFIL);
			if (@$object["tags"]) $tags = $object["tags"]; else $tags="";
			$newsObject= array ("_id" => $object["_id"],
								"name" => $orga["name"],
								"text"=>trim(preg_replace('/<[^>]*>/', ' ',(substr($orga["description"],0 ,100 )))),
								"author"=> array(
									"id" => $object["actor"]["id"],
									"name" => $author["name"],
									"profilImageUrl" => $authorImage
								),
								"tags"=>$tags,
								"created"=>$object["timestamp"],
								"id"=>$object["object"]["id"],
								"imageBackground" => $image,
								"type"=>$object["object"]["objectType"],
								"icon" => "fa-group"
							);
			return $newsObject;	
		}	

	}
}