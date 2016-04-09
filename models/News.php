<?php 
class News {

	const COLLECTION = "news";
	/**
	 * get an project By Id
	 * @param type $id : is the mongoId of the project
	 * @return type
	 */
	public static function getById($id) {
	  	$news = PHDB::findOne( self::COLLECTION,array("_id"=>new MongoId($id)));
	  	if(@$news["type"]){
		    if($news["type"]==ActivityStream::COLLECTION){
			    if($news["object"]["objectType"]!="needs" && $news["object"]["objectType"]!="gantts")
		  			$news=NewsTranslator::convertParamsForNews($news);
		  	}
	  		if($news["type"]==Project::COLLECTION)
		  		$news["postOn"]=Project::getSimpleProjectById($news["id"]);
	  		if ($news["type"]==Organization::COLLECTION)
		  		$news["postOn"]=Organization::getSimpleOrganizationById($news["id"]);
	  		if ($news["type"]==Event::COLLECTION)
		  		$news["postOn"]=Event::getSimpleEventById($news["id"]);
	  		if ($news["type"]==Person::COLLECTION)
		  		$news["postOn"]=Person::getSimplePersonById($Person["id"]);
		  		
  		}
  		$news["author"] = Person::getSimpleUserById($news["author"]);
  		return $news;

	}

	public static function getWhere($params) {
	  	return PHDB::findAndSort( self::COLLECTION,$params);
	}
	public static function getAuthor($id){
		return PHDB::findOneById( self::COLLECTION ,$id, 
				array("author" => 1));
	}
	public static function getWhereSortLimit($params,$sort=array("created"=>-1),$limit=1) {
	  	$res = PHDB::findAndSort( self::COLLECTION,$params,$sort,$limit);

	  	foreach ($res as $key => $news) {
	  		$res[$key]["author"] = Person::getById($news["author"]);
	  	}
	  	return $res;
	}
	public static function getNewsForObjectId($param,$sort=array("created"=>-1),$type)
	{
	    $res = PHDB::findAndSort(self::COLLECTION, $param,$sort,5);
	    //print_r($res);
	    foreach ($res as $key => $news) {
		    if(@$news["type"]){
			    if($news["type"]==ActivityStream::COLLECTION){
				    if($news["object"]["objectType"]!="needs" && $news["object"]["objectType"]!="gantts")
			  			$res[$key]=NewsTranslator::convertParamsForNews($news);
			  	}
		  		if($news["type"]==Person::COLLECTION)
			  		$res[$key]["postOn"]=Person::getSimpleUserById($news["id"]);
		  		if($news["type"]==Project::COLLECTION)
			  		$res[$key]["postOn"]=Project::getSimpleProjectById($news["id"]);
		  		if ($news["type"]==Organization::COLLECTION)
			  		$res[$key]["postOn"]=Organization::getSimpleOrganizationById($news["id"]);
			  	if ($news["type"]==Event::COLLECTION){
				  	$event=Event::getSimpleEventById($news["id"]);
					  	if(!empty($event)){
				  		$res[$key]["postOn"]=$event;
				  		}
			  		}
			  		
	  		}
	  		$res[$key]["author"] = Person::getSimpleUserById($news["author"]);
	  	}
	  	return $res;
	}
	public static function save($params)
	{
		//check a user is loggued 
	 	$user = Person::getById(Yii::app()->session["userId"]);
	 	//TODO : if type is Organization check the connected user isAdmin
	 	
	 	if(empty($user))
	 		throw new CTKException("You must be loggued in to add a news entry.");

	 	if((isset($_POST["text"]) && !empty($_POST["text"])) || (isset($_POST["mediaContent"]) && !empty($_POST["mediaContent"])))
	 	{
		 	$codeInsee=$user["address"]["codeInsee"];
		 	$postalCode=$user["address"]["postalCode"];
			$news = array("text" => $_POST["text"],
						  "author" => Yii::app()->session["userId"],
						  "date"=>new MongoDate(time()),
						  "created"=>new MongoDate(time()));

			if(isset($_POST["date"])){
				$news["date"] = new MongoDate(strtotime(str_replace('/', '-', $_POST["date"])));
			}
			if (isset($_POST["mediaContent"])){
				$news["media"] = $_POST["mediaContent"];
			}
			if(isset($_POST["tags"]))
				$news["tags"] = $_POST["tags"];
		 	if(isset($_POST["typeId"]))
				$news["id"] = $_POST["typeId"];
		 	if(isset($_POST["type"]))
		 	{
				$type=$_POST["type"];
				$news["type"] = $type;
				$from="";
				if($type == Person::COLLECTION ){
					$person = Person::getById($_POST["typeId"]);
					if( isset( $person['geo'] ) )
						$from = $person['geo'];
					$codeInsee=$person["address"]["codeInsee"];
					$postalCode=$person["address"]["postalCode"];
				}else if($type == Organization::COLLECTION ){
					$organization = Organization::getById($_POST["typeId"]);
					if( isset( $organization['geo'] ) )
						$from = $organization['geo'];
					$codeInsee=$organization["address"]["codeInsee"];
					$postalCode=$organization["address"]["postalCode"];
						$organization["type"]=Organization::COLLECTION;
					Notification::actionOnPerson ( ActStr::VERB_POST, ActStr::ICON_COMMENT, null , $organization )  ;
				}
				else if($type == Event::COLLECTION ){
					$event = Event::getById($_POST["typeId"]);
					if( isset( $event['geo'] ) )
						$from = $event['geo'];
					$codeInsee=$event["address"]["codeInsee"];
					$postalCode=$event["address"]["postalCode"];
					//Notification::actionOnEvent ( ActStr::VERB_POST, ActStr::ICON_COMMENT, null , $event )  ;
				}
				else if($type == Project::COLLECTION ){
					$project = Project::getById($_POST["typeId"]);
					if( isset( $project['geo'] ) )
						$from = $project['geo'];
					$codeInsee=$project["address"]["codeInsee"];
					$postalCode=$project["address"]["postalCode"];
					$project["type"] = Project::COLLECTION; 
					Notification::actionOnPerson ( ActStr::VERB_POST, ActStr::ICON_COMMENT, null , $project )  ;
				}

				/*if( $_POST["type"] == Organization::COLLECTION && Authorisation::isOrganizationAdmin( Yii::app()->session["userId"], $_POST["typeId"]) )
	 				throw new CTKException("You must be admin of this organization to post.");*/
			}
		 	if( isset($_POST["scope"]) || $_POST["type"]=="city") {
			 	if($_POST["type"]=="city"){
			 		$news["scope"]["type"]="public";
			 		$news["scope"]["cities"][] = array("codeInsee"=>$_POST["codeInsee"]);
				}
			 	else {
			 		$scope = $_POST["scope"];
			 		if($scope== "public" && Yii::app()->session["userId"] != $news["author"]) $scope== "private";
			 		$news["scope"]["type"]= $scope;
			 		if($scope== "public")
			 		$news["scope"]["cities"][] = array("codeInsee"=>$codeInsee,
		 											"postalCode"=>$postalCode,
		 											"geo" => $from
		 										);
			 	}
		 			
			}
			PHDB::insert(self::COLLECTION,$news);
		    $news["author"] = Person::getSimpleUserById(Yii::app()->session["userId"]);
		    
		    /* Send email alert to contact@pixelhumain.com */
		  	if(@$type && $type=="pixels"){
		  		Mail::notifAdminBugMessage($news["text"]);
		  	}
		    return array("result"=>true, "msg"=>"Votre message est enregistré.", "id"=>$news["_id"],"object"=>$news);	
		} else {
			return array("result"=>false, "msg"=>"Please Fill required Fields.");	
		}
	}
	public static function delete($id) {
		return PHDB::remove(self::COLLECTION,array("_id"=>new MongoId($id)));
	}
	public static function updateField($newsId, $name, $value, $userId){
		//if (!Authorisation::canEditItem($userId, self::COLLECTION, $projectId)) {
		//	throw new CTKException(Yii::t("project", "Can not update this project : you are not authorized to update that project !"));	
		//}
		/*		A rajouter vérification auteur !!!!!! */
		//$dataFieldName = self::getCollectionFieldNameAndValidate($projectFieldName, $projectFieldValue, $projectId);
	
		//Specific case : 
		//Tags
		//if ($dataFieldName == "tags") {
		//	$projectFieldValue = Tags::filterAndSaveNewTags($projectFieldValue);
		//}

		//address

		$set = array($name => $value);	


		//update the project
		PHDB::update( self::COLLECTION, array("_id" => new MongoId($newsId)), 
		                          array('$set' => $set));
	                  
	    return array("result"=>true, "msg"=>Yii::t("common","News well updated"), "id"=>$newsId);
	}
}
?>