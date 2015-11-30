<?php 
class NewsTranslator {
	
	const NEWS_CONTRIBUTORS = "newsContributors";
	const NEWS_CREATE_PROJECT = "newsCreateProject";
	const NEWS_CREATE_NEED = "newsCreateNeed";
	const NEWS_CREATE_EVENT = "newsCreateEvent";
	const NEWS_CREATE_ORGANIZATION = "newsCreateOrganization";
	const NEWS_CREATE_TASK = "newsCreateTask";
	const NEWS_JOIN_ORGANIZATION = "newsMemberJoinOrganization";
	public static function convertToNews($object, $useCase){	
		if($useCase ==  self::NEWS_CONTRIBUTORS ){
			$author=Person::getById($object["actor"]["id"]);
			if($object["object"]["objectType"]==Person::COLLECTION){
				$newContributor=Person::getById($object["object"]["id"]);
				$contributorImage = Document::getLastImageByKey((string) $object["object"]["id"], Person::COLLECTION, Document::IMG_PROFIL);
			}
			else{
				$newContributor=Organization::getById($object["object"]["id"]);
				$contributorImage = Document::getLastImageByKey((string)$object["object"]["id"], Organization::COLLECTION, Document::IMG_PROFIL);
			}
			$newsObject= array ("_id" => $object["_id"],
								"name" => $newContributor["name"]." is a new contributor",
								"text"=>"has joined the project",
								"target"=> array(
									"id" => (string)$object["object"]["id"],
									"name" => $newContributor["name"],
									"type" => Person::COLLECTION,
									"profilImageUrl" => $contributorImage
								),
								"author"=> array(
									"id" => (string)$object["actor"]["id"],
									"type" => Person::COLLECTION,
									"name" => $author["name"],
								),
								"created"=>$object["timestamp"],
								"id"=>(string)$object["object"]["id"],
								"type"=>$object["object"]["objectType"],
								"verb" => $object["verb"],
								"icon" => "fa-users"
							);
			return $newsObject;
		}
		else if($useCase ==  self::NEWS_CREATE_NEED ){
			$need=Need::getById($object["object"]["id"]);
			$author=Person::getById($object["actor"]["id"]);
			$authorImage = Document::getLastImageByKey((string) $object["actor"]["id"], Person::COLLECTION, Document::IMG_PROFIL);
			$newsObject= array ("_id" => $object["_id"],
								"name" => $need["name"],
								"text"=> isset($need["description"]) ? $need["description"] : "has been add to the project",
								"author"=> array(
									"id" => $object["actor"]["id"],
									"name" => $author["name"],
									"profilImageUrl" => $authorImage
								),
								"target" => array(
									"id" => $object["target"]["id"],
									"type" => $object["target"]["objectType"],
								//	"name" => $target["name"],
								//	"profilImageUrl" => $targetImage
								),
								"created"=>$object["timestamp"],
								"id"=>(string)$object["object"]["id"],
								"type"=>$object["object"]["objectType"],
								"verb" => $object["verb"],
								"icon" => "fa-cubes"
							);
			return $newsObject;	
		}	
else if($useCase ==  self::NEWS_CREATE_TASK ){
			$where = array(
                "_id"=>new MongoId($object["target"]["id"]),
                "tasks" =>  array('$exists' => 1));
			$tasks = Gantt::getTasks($where,$object["target"]["objectType"]);
			$task=$tasks[(string)$object["object"]["id"]];
			$author=Person::getById($object["actor"]["id"]);
			$authorImage = Document::getLastImageByKey((string) $object["actor"]["id"], Person::COLLECTION, Document::IMG_PROFIL);
			$newsObject= array ("_id" => $object["_id"],
								"name" => $task["name"],
								"text" => "ok",
								"author"=> array(
									"id" => $object["actor"]["id"],
									"name" => $author["name"],
									"profilImageUrl" => $authorImage
								),
								"target" => array(
									"id" => $object["target"]["id"],
									"type" => $object["target"]["objectType"],
								//	"name" => $target["name"],
								//	"profilImageUrl" => $targetImage
								),
								"created"=>$object["timestamp"],
								"id"=>(string)$object["object"]["id"],
								"type"=>$object["object"]["objectType"],
								"verb" => $object["verb"],
								"icon" => "fa-tasks"
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
								"verb" => $object["verb"],
								"icon" => "fa-lightbulb-o"
							);
			if (@$project["voteUp"]){
				$newsObject["voteUp"]=$project["voteUp"];
				$newsObject["voteUpCount"]=$project["voteUpCount"];
			}
			if (@$project["voteDown"]){
				$newsObject["voteDown"]=$project["voteDown"];
				$newsObject["voteDownCount"]=$project["voteDownCount"];
			}
			if (@$project["comment"]){
				$newsObject["comment"]=$project["comment"];
				$newsObject["commentCount"]=$project["commentCount"];
			}

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
								"verb" => $object["verb"],
								"icon" => "fa-calendar"
							);
			if (@$event["voteUp"]){
				$newsObject["voteUp"]=$event["voteUp"];
				$newsObject["voteUpCount"]=$event["voteUpCount"];
			}
			if (@$event["voteDown"]){
				$newsObject["voteDown"]=$event["voteDown"];
				$newsObject["voteDownCount"]=$event["voteDownCount"];
			}
			if (@$event["comment"]){
				$newsObject["comment"]=$event["comment"];
				$newsObject["commentCount"]=$event["commentCount"];
			}
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
								"verb" => $object["verb"],
								"icon" => "fa-group"
							);
			if (@$orga["voteUp"]){
				$newsObject["voteUp"]=$orga["voteUp"];
				$newsObject["voteUpCount"]=$orga["voteUpCount"];
			}
			if (@$orga["voteDown"]){
				$newsObject["voteDown"]=$orga["voteDown"];
				$newsObject["voteDownCount"]=$orga["voteDownCount"];
			}
			if (@$orga["comment"]){
				$newsObject["comment"]=$orga["comment"];
				$newsObject["commentCount"]=$orga["commentCount"];
			}
			return $newsObject;	
		}	
		else if($useCase ==  self::NEWS_JOIN_ORGANIZATION ){
			$author=Person::getById($object["actor"]["id"]);
			$memberImage = Document::getLastImageByKey((string)$object["actor"]["id"], Person::COLLECTION, Document::IMG_PROFIL);
			if($object["object"]["objectType"]==Organization::COLLECTION){
				$newMember=Organization::getById($object["object"]["id"]);	
			}
			else{
				$newMember=Person::getById($object["object"]["id"]);	
			}
				
			if (@$object["tags"]) $tags = $object["tags"]; else $tags="";
			$newsObject= array ("_id" => $object["_id"],
								"name" =>$newMember["name"]." is new member",
								"text"=> "has joined the organization",
								"target"=> array(
									"id" => (string)$object["actor"]["id"],
									"name" => $author["name"],
									"type" => Person::COLLECTION,
									"profilImageUrl" => $memberImage
								),
								"author"=> array(
									"id" => (string)$object["actor"]["id"],
									"type" => Person::COLLECTION,
									"name" => $author["name"],
								),
								"tags"=>$tags,
								"created"=>$object["timestamp"],
								"id"=> (string)$object["object"]["id"],
								"type"=> $object["object"]["objectType"],
								"verb" => $object["verb"],
								"icon" => "fa-group"
							);
			return $newsObject;	
		}	
	}
}