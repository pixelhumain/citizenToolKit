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
	public static function convertParamsForNews($params,$readOne=false, $followsArrayIds=null){
		if(@$params["object"]){
			$docImg="";
			if(@$params["object"]["type"]==Event::COLLECTION){
				$object=Event::getSimpleEventById((string)$params["object"]["id"]);
				$params["icon"] = "fa-calendar";
				//$object["typeEvent"] = @$object["type"];
			} 
			else if (@$params["object"]["type"]==Organization::COLLECTION){
				$object=Organization::getSimpleOrganizationById((string)$params["object"]["id"]);
				$params["icon"] = "fa-group";
			} 
			else if (@$params["object"]["type"]==Project::COLLECTION){
				$object=Project::getSimpleProjectById((string)$params["object"]["id"]);
				$params["icon"]="fa-lightbulb-o";
			}
			else if (@$params["object"]["type"]==Need::COLLECTION){
				$object=Need::getSimpleNeedById((string)$params["object"]["id"]);
				$params["icon"]="fa-cubes";
			}
			else if (@$params["object"]["type"]==News::COLLECTION){
				$object=News::getById((string)$params["object"]["id"]);
				$params["icon"]="fa-newspaper-o";
			}
			else if (@$params["object"]["type"]==Classified::COLLECTION){
				$object=Classified::getById((string)$params["object"]["id"]);
				$params["icon"]="fa-newspaper-o";
			}
			else if (@$params["object"]["type"]==Proposal::COLLECTION){
				$object=Proposal::getById((string)$params["object"]["id"]);
				$params["icon"]="fa-gavel";
			}
			else if (@$params["object"]["type"]==Resolution::COLLECTION){
				$object=Resolution::getById((string)$params["object"]["id"]);
				$params["icon"]="fa-gavel";
			}
			else if (@$params["object"]["type"]==Action::COLLECTION){
				$object=Action::getById((string)$params["object"]["id"]);
				$params["icon"]="fa-ticket";
			}
			else if (@$params["object"]["type"]==Room::COLLECTION){
				$object=Room::getById((string)$params["object"]["id"]);
				$params["icon"]="fa-inbox";
			}
			else if (@$params["object"]["type"]==Classified::COLLECTION){
				$object=Classified::getById((string)$params["object"]["id"]);
				$params["icon"]="fa-bullhorn";
			}
			else if (@$params["object"]["type"]==Ressource::COLLECTION){
				$object=Ressource::getById((string)$params["object"]["id"]);
				$params["icon"]="fa-cubes";
			}
			else if (@$params["object"]["type"]==Place::COLLECTION){
				$object=Place::getById((string)$params["object"]["id"]);
				$params["icon"]="fa-marker-map";
			}
			//var_dump($object);
			//exit;
			if(!empty($object)){
				$thisType = $params["object"]["type"];
				$params["object"] = array_merge($params["object"], $object);
				$params["object"]["type"] = $thisType;

				if(@$params["object"]["type"]!=Need::COLLECTION)
					$params["imageBackground"] = @$object["profilImageUrl"];

				$params["name"] = @$object["name"] ? $object["name"] : @$object["title"];
				
				//if(@$params["object"]["type"]==ActivityStream::COLLECTION)
				//$params["text"] = preg_replace('/<[^>]*>/', '', @$object["text"]);

				//if (empty($params["text"]))
				//	$params["text"] =(isset($object["description"]) ? preg_replace('/<[^>]*>/', '',$object["description"]) : "");
				
				if(@$params["object"]["type"]==Event::COLLECTION || $params["object"]["type"]==Need::COLLECTION){
					$params["startDate"]=@$object["startDate"];
					$params["endDate"]=@$object["endDate"];
					$params["startDateSec"]=@$object["startDateSec"];
					$params["endDateSec"]=@$object["endDateSec"];
					
				}

				if(@$params["object"]["type"]==News::COLLECTION){
					//$params["text"] = $params["object"][""]
					//var_dump($params["object"]); exit;
					$params["object"] = array_merge($params["object"], $object);
					
				}

				if(@$object["address"])
					$params["scope"]["address"]=$object["address"];


			}else{
				$params=array();
				return $params;
			}
		}
		if(@$params["target"]["type"]){
			//$params["target"]["type"]=$params["target"]["type"];
			$fields=array("name","profilThumbImageUrl");
			$target=Element::getElementSimpleById($params["target"]["id"], $params["target"]["type"],null, $fields);
			/*if ($params["target"]["type"] == Organization::COLLECTION){
				$params["target"]["type"]=Organization::COLLECTION;
				$params["target"]=Organization::getSimpleOrganizationById($params["target"]["id"]);
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
				$params["target"] = Event::getSimpleEventById($params["target"]["id"]);
				$params["target"]["type"]=Event::COLLECTION;
			}*/

			$params["target"] = array("id"=>@$params["target"]["id"],
									 "name"=>@$target["name"],
									 "type"=>@$params["target"]["type"],
									 "profilThumbImageUrl"=>@$target["profilThumbImageUrl"]);
				
		}
		// if(@$params["type"]=="news"){
		// 	if(@$params["text"]){
		// 		$params["text"]=preg_replace('/<[^>]*>/', '',(isset($params["text"]) ? $params["text"] : ""));
		// 	}
			// if($params["scope"]["type"]=="public" && !@$params["scope"]["cities"][0]["addressLocality"] && @$params["scope"]["cities"][0]["postalCode"]){
			// 	$address=SIG::getAdressSchemaLikeByCodeInsee($params["scope"]["cities"][0]["codeInsee"],$params["scope"]["cities"][0]["postalCode"]);
			// 	if(empty($address)){
			// 		$params=array("created"=>$params["created"]);
			// 		return $params;
			// 	}
			// 	$params["scope"]["cities"][0]["addressLocality"]=$address["addressLocality"];
			// }
		//}
		if(@$params["media"] && $params["media"]["type"]=="gallery_images"){
			$images=array();
			$limit=5;
			$i=0;
			foreach($params["media"]["images"] as $data){
				if($i<$limit){
					if(@$data && !empty($data)){
						$image=Document::getById($data);
						if(@$image){
							$image["imagePath"]=Document::getDocumentPath($image, true);
							$image["imageThumbPath"]=Document::getDocumentPath($image, true, Document::GENERATED_IMAGES_FOLDER."/");
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
		else if(@$params["media"] && $params["media"]["type"]=="gallery_files"){
			$files=array();
			$limit=5;
			$i=0;
			foreach($params["media"]["files"] as $data){
				if($i<$limit){
					if(@$data && !empty($data)){
						$file=Document::getById($data);
						if(@$file){
							$file["docPath"]=Document::getDocumentPath($file, true);
							array_push($files,$file);
						}else{
							$countFiles=intval($params["media"]["countFiles"]);
							$countFiles--;
							$params["media"]["countFiles"]=$countFiles;
						}
					}else{
						$countfiles=intval($params["media"]["countFiles"]);
						$countFiles--;
						$params["media"]["countFiles"]=$countFiles;
					}
				} else {
					exit;
				}
			}
			$params["media"]["files"]=$files;
		}
		else if(@$params["media"] && $params["media"]["type"]=="activityStream"){
			$element=Element::getSimpleByTypeAndId($params["media"]["object"]["type"], $params["media"]["object"]["id"]);
			$element["type"]=$params["media"]["object"]["type"];
			if($params["media"]["object"]["type"]==Event::COLLECTION)
				$element["typeEvent"] = @$element["type"];
			$params["media"]["object"] = $element;
		}

		if(!isset($params["author"]["id"]) || @$params["verb"] == "create"){ 
			$authorId=@$params["author"];
			$authorType=Person::COLLECTION;
			$fields=array("name","profilThumbImageUrl", "geo");
			if(@$params["targetIsAuthor"]==true || @$params["verb"] == "create"){
				$authorId=$params["target"]["id"];
				$authorType=$params["target"]["type"];
				$author=Element::getElementSimpleById( $params["target"]["id"],$params["target"]["type"],null, $fields);
	  			$params["authorName"] = @$author["name"];
	  			$params["authorId"] = @$params["target"]["id"];
	  			$params["authorType"] = @$params["target"]["type"];
	  			$params["updated"] = $params["created"];					    
	  			$params["sharedBy"] = array();
			}else{
				$author=Element::getElementSimpleById( $params["author"],Person::COLLECTION,null, $fields);
  			}
	  	}else{
	  		$author = $params["author"];
	  	}

	  	$author = array("id"=>@$authorId,
					    "geo"=>@$author["geo"],
					    "name"=>@$author["name"],
					    "type"=>@$authorType,
					    "profilThumbImageUrl"=>@$author["profilThumbImageUrl"]);

	  	if(@$author["geo"]==null) unset($author["geo"]);

	  	//var_dump($params["author"]); //exit;
  		if (!empty($author)) $params["author"] = $author;
	  	else return array("created"=>$params["created"]);
		
		if(isset($params["sharedBy"])){
			$sharedBy = array(); 
			$dateUpdated = @$params["updated"];
			$lastComment = "";
			$lastAuthorShare = array();
			$lastKey = null;

			$count=0;
			foreach($params["sharedBy"] as $key => $value){
				 //on commence par prendre la date du premier partage (date de création si news)
				if($count==0) $dateUpdated = @$value["updated"];
				$count++;

				$lastKey = null;
				$fields=array("name","profilThumbImageUrl");
				$share=Element::getElementSimpleById($value["id"],$value["type"],null, $fields);
			
				//$share =  Element::getSimpleByTypeAndId($value["type"], $value["id"]);
				if (!empty($share)){	
					$clearShare = array("id"=>@$value["id"],
										"name"=>@$share["name"],
										"type"=>@$value["type"],
										"updated" => @$value["updated"],
										"comment" => @$value["comment"],
										"profilThumbImageUrl"=>@$share["profilThumbImageUrl"]);	
					
					if(@$followsArrayIds){ //si j'ai la liste des follows de l'element
						//et que l'id du sharedBy est dans la liste des follows
						//ou que l'id du sharedBy est mon id
						if( in_array(@$value["id"], $followsArrayIds) || 
									 @$value["id"] == Yii::app()->session["userId"]){ 
							$dateUpdated = $value["updated"]; //memorise la date du share
							$lastComment = @$value["comment"]; //memorise la date du share
							$lastAuthorShare = $clearShare;	  //memorise l'auteur du share   
							$lastKey = count($sharedBy);
						}
					}else{//si j'ai pas la liste des follows de l'element => journal
						if( @$value["id"] == Yii::app()->session["userId"]){ 
							$dateUpdated = $value["updated"]; //memorise la date du share
							$lastComment = @$value["comment"]; //memorise la date du share
							$lastAuthorShare = $clearShare;	  //memorise l'auteur du share   
							$lastKey = count($sharedBy);
						}
					}

					//ajoute le share dans la liste
					$sharedBy[] = $clearShare; 	 
					//memorise la clé pour pouvoir supprimer le dernier share de la liste
					//(permet d'afficher le bon nombre de partage)				
				}
			}

			//efface le lastAuthorShared de la liste des sharedBy
			//echo $lastKey;
			//if($lastKey!=null && @$sharedBy[$lastKey]){ unset($sharedBy[$lastKey]); }

			$params["updated"] = @$dateUpdated;
			$params["comment"] = @$lastComment;
			$params["typeSig"] = "news";

		  	if(!empty($lastAuthorShare)){
		  		$params["lastAuthorShare"] = @$lastAuthorShare;
		  	}
		  	else if(!@$followsArrayIds){ 
		  		$params["lastAuthorShare"] = @$author;
		  	}


		  	$params["sharedBy"] = $sharedBy;
				
		}
		return $params;
	}
}
