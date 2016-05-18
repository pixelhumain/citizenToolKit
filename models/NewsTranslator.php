<?php 
class NewsTranslator {
	
	const NEWS_CONTRIBUTORS = "newsContributors";
	const NEWS_CREATE_PROJECT = "newsCreateProject";
	const NEWS_CREATE_NEED = "newsCreateNeed";
	const NEWS_CREATE_EVENT = "newsCreateEvent";
	const NEWS_CREATE_ORGANIZATION = "newsCreateOrganization";
	const NEWS_CREATE_TASK = "newsCreateTask";
	const NEWS_JOIN_ORGANIZATION = "newsMemberJoinOrganization";
	/**
		*get news well formated 
		*	@param string author become an object, 
		*	@param object object (if type=="activityStream") become object with informations, 
		*	@param object target, 
		*	@param object media (if type=="gallery_images") become of object with document,
	**/
	public static function convertParamsForNews($params,$readOne=false){
		if(@$params["object"]){
			$docImg="";
			if($params["object"]["objectType"]==Event::COLLECTION){
				$object=Event::getSimpleEventById((string)$params["object"]["id"]);
				$docImg = Document::IMG_PROFIL;
				$params["icon"] = "fa-calendar";
			} 
			else if ($params["object"]["objectType"]==Organization::COLLECTION){
				$object=Organization::getSimpleOrganizationById((string)$params["object"]["id"]);
				$docImg = Document::IMG_PROFIL;
				$params["icon"] = "fa-group";
			} 
			else if ($params["object"]["objectType"]==Project::COLLECTION){
				$object=Project::getSimpleProjectById((string)$params["object"]["id"]);
				$docImg = Document::IMG_SLIDER;
				$params["icon"]="fa-lightbulb-o";
			}
			if(!empty($object)){
			$params["imageBackground"] = Document::getLastImageByKey((string) $params["object"]["id"],$params["object"]["objectType"] , $docImg);
			$params["name"] = $object["name"];
			echo @$object["description"];
			echo "<br/> après transfo";
			//trim(preg_replace('/<[^>]*>/', ' ',(substr(isset($object["description"]) ? $object["description"] : "",0 ,100 )));
			$params["text"] = $object["description"] ;
			echo $params["text"];
			$params["scope"]["address"]=$object["address"];
			print_r($params);
			}
			else {
				
			}
		}
		if(@$params["target"]["type"]){
			if ($params["target"]["type"] == Organization::COLLECTION){
				$params["target"]=Organization::getSimpleOrganizationById($params["target"]["id"]);
				$params["target"]["type"]=Organization::COLLECTION;
			}
			else if ($params["target"]["type"]== Project::COLLECTION){
				$params["target"] = Project::getSimpleProjectById($params["target"]["id"]);
				$params["target"]["type"]=Project::COLLECTION;

			}
			else if($params["target"]["type"]==Person::COLLECTION){
				$params["target"] =Person::getSimpleUserById($params["target"]["id"]);
				$params["target"]["type"]=Person::COLLECTION;
			}
			else if ($params["target"]["type"]==Event::COLLECTION){
				
				//if(!empty($event)){
					$params["target"] = Event::getSimpleEventById($params["target"]["id"]);
					$params["target"]["type"]=Event::COLLECTION;
				//}
			}
				
		}
		if($params["type"]=="news" && $readOne==false){
			if(@$params["text"] && strlen ($params["text"]) > 500){
			  		$params["text"]=trim(preg_replace('/<[^>]*>/', ' ',(substr(isset($params["text"]) ? $params["text"] : "",0 ,500 ))))."<span class='removeReadNews'> ...<br><a href='javascript:;' onclick='blankNews(\"".(string) $params["_id"]."\")'>Lire la suite</a></span>";
		  	}
		}
		//print_r($params);
		if(@$params["media"] && !is_string(@$params["media"]) && $params["media"]["type"]=="gallery_images"){
			$images=array();
			$limit=5;
			$i=0;
			foreach($params["media"]["images"] as $data){
				if($i<$limit){
					if(@$data && !empty($data)){
						$image=Document::getById($data);
						if(@$image){
							array_push($images,$image);
						}else{
							$countImages=intval($params["media"]["countImages"]);
							$countImages--;
							$params["media"]["countImages"]=$countImages;
						}
					}else{
						$countImages=intval($params["media"]["countImages"]);
						$countImages--;
						$params["media"]["countImages"]=$countImages;
					}
				} else {
					exit;
				}
			}
			$params["media"]["images"]=$images;
		}
	  	$params["author"] = Person::getSimpleUserById($params["author"]);
		return $params;
	}
	
}