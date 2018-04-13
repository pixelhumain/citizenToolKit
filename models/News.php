<?php
class News {

	const COLLECTION = "news";
	const CONTROLLER = "news";
	const ICON = "fa-users";
	const ICON_BIZ = "fa-industry";
	const ICON_GROUP = "fa-circle-o";
	const ICON_GOV = "fa-circle-o";
	const COLOR = "#93C020";
	/**
	 * get an news By Id
	 * @param type $id : is the mongoId of the news
	 * @return object
	 */
	public static function getById($id) {
	  	$news = PHDB::findOne( self::COLLECTION,array("_id"=>new MongoId($id)));
	  	if(@$news["type"]){
			$news=NewsTranslator::convertParamsForNews($news,true);
		}
		return $news;
	}

	/* DEAD CODE - TO TEST BEFORE DELETE
		public static function getWhere($params) {
	  	return PHDB::findAndSort( self::COLLECTION,$params);
	}*/
	public static function getAuthor($id){
		return PHDB::findOneById( self::COLLECTION ,$id,
				array("author" => 1));
	}
	/*public static function getWhereSortLimit($params,$sort=array("created"=>-1),$limit=1) {
	  	$res = PHDB::findAndSort( self::COLLECTION,$params,$sort,$limit);

	  	foreach ($res as $key => $news) {
	  		$res[$key]["author"] = Person::getById($news["author"]);
	  	}
	  	return $res;
	}*/

	/**
	 * get news
	 * @param type $params : is the condition of news generated
	 * @param type sort
	 * News limited to 15
	 */
	public static function getNewsForObjectId($param,$sort=array("created"=>-1),$type)
	{
		//$param=array();
	    $res = PHDB::findAndSort(self::COLLECTION, $param,$sort);
	    foreach ($res as $key => $news) {
		    if(@$news["type"]){
			    $newNews=NewsTranslator::convertParamsForNews($news);
			    //if(empty($newNews)){
				$res[$key]=$newNews;
				//}else{
				//	$res[$key]=array();
				//}
	  		}
	  	}
	  	return $res;
	}
	/**
	 * Insert a new news, checking if the news is well formated
	 * @param array $params Array with all fields for a project - TODO
	 * @param string $author author id doing the insertion
	 * @param object $target defined the target of the news - wall where news is created
	 * @return array as result type
	 */
	public static function save($params)
	{
		//check a user is loggued
	 	$user = Person::getById(Yii::app()->session["userId"]);
	 	//TODO : if type is Organization check the connected user isAdmin

	 	if(empty($user))
	 		return array("result"=>false, "msg"=>Yii::t("common","You must be logged in to add a news entry !"));

	 	if((isset($_POST["text"]) && !empty($_POST["text"])) || (isset($_POST["media"]) && !empty($_POST["media"])))
	 	{
		 	$codeInsee=$user["address"]["codeInsee"];
		 	$postalCode=$user["address"]["postalCode"];
		 	$typeNews=@$_POST["type"] ? $_POST["type"] : "news";
			$news = array("type" => $typeNews, //"news",
						  "text" => $_POST["text"],
						  "author" => Yii::app()->session["userId"],
						  "date"=>new MongoDate(time()),
						  "created"=>new MongoDate(time()));

			if(isset($_POST["date"])){
				$news["date"] = new MongoDate(strtotime(str_replace('/', '-', $_POST["date"])));
			}
			if (isset($_POST["media"])){
				$news["media"] = $_POST["media"];
				if(@$_POST["media"]["content"] && @$_POST["media"]["content"]["image"] && !@$_POST["media"]["content"]["imageId"]){
					$urlImage = self::uploadNewsImage($_POST["media"]["content"]["image"],$_POST["media"]["content"]["imageSize"],Yii::app()->session["userId"]);
					$news["media"]["content"]["image"]=	Yii::app()->baseUrl."/".$urlImage;
				}
			}
			if(isset($_POST["tags"]))
				$news["tags"] = $_POST["tags"];
		 	if(isset($_POST["parentId"]))
				$news["target"]["id"] = $_POST["parentId"];
			if(isset($_POST["targetIsAuthor"]))
				$news["targetIsAuthor"] = $_POST["targetIsAuthor"];
		 	if(isset($_POST["parentType"]))
		 	{

		 		$target=array(	"id"=>$_POST["parentId"],
		 						"type"=>$_POST["parentType"],
		 						"value" => $_POST["text"]);
			
				$type=$_POST["parentType"];
				$news["target"]["type"] = $type;
				$from="";
				if($type == Person::COLLECTION ){
					$person = Person::getById($_POST["parentId"]);
					$target["name"] = $person["name"];
					if( isset( $person['geo'] ) )
						$from = $person['geo'];
					if(@$person["address"]){
						$codeInsee=$person["address"]["codeInsee"];
						$postalCode=$person["address"]["postalCode"];
					}
					if($_POST["parentId"] != Yii::app()->session["userId"]){
						$person["type"]=Person::COLLECTION;
						Notification::actionOnPerson ( ActStr::VERB_POST, ActStr::ICON_RSS, null , $person )  ;
					}

				}else if($type == Organization::COLLECTION ){
					$organization = Organization::getById($_POST["parentId"]);
					$target["name"] = $organization["name"];
					if( isset( $organization['geo'] ) )
						$from = $organization['geo'];
					if(@$organization["address"]){
						$codeInsee=$organization["address"]["codeInsee"];
						$postalCode=$organization["address"]["postalCode"];
					}
					$organization["type"]=Organization::COLLECTION;
					Notification::actionOnPerson ( ActStr::VERB_POST, ActStr::ICON_RSS, null , $organization )  ;
				}
				else if($type == Event::COLLECTION ){
					$event = Event::getById($_POST["parentId"]);
					$target["name"] = $event["name"];
					if( isset( $event['geo'] ) )
						$from = $event['geo'];
					if(@$event["address"]){
						$codeInsee=$event["address"]["codeInsee"];
						$postalCode=$event["address"]["postalCode"];
					}
					$event["type"]=Event::COLLECTION;
					Notification::actionOnPerson ( ActStr::VERB_POST, ActStr::ICON_RSS, null , $event )  ;
				}
				else if($type == Project::COLLECTION ){
					$project = Project::getById($_POST["parentId"]);
					$target["name"] = $project["name"];
					if( isset( $project['geo'] ) )
						$from = $project['geo'];
					if(@$project["address"]){
						$codeInsee=$project["address"]["codeInsee"];
						$postalCode=$project["address"]["postalCode"];
					}
					$project["type"] = Project::COLLECTION;
					Notification::actionOnPerson ( ActStr::VERB_POST, ActStr::ICON_RSS, null , $project )  ;
				}
				// if( isset($_POST["scope"])) {
				// 	if(@$_POST["codeInsee"]){
				// 		$news["scope"]["type"]="public";
				// 		$address=SIG::getAdressSchemaLikeByCodeInsee($_POST["codeInsee"],$_POST["postalCode"]);

				// 		$news["scope"]["cities"][] = array("codeInsee"=>$_POST["codeInsee"], "postalCode"=>$_POST["postalCode"], "addressLocality"=>$address["addressLocality"]);
				// 	}
				// 	else {
				// 		$scope = $_POST["scope"];
				// 		$news["scope"]["type"]=$scope;
				// 		if($scope== "public"){
				// 			$address=SIG::getAdressSchemaLikeByCodeInsee($codeInsee,$postalCode);
				// 			$news["scope"]["cities"][] = array("codeInsee"=>$codeInsee,
				// 												"postalCode"=>$postalCode,
				// 												"addressLocality"=>$address["addressLocality"],
				// 												"geo" => $from
				// 											);
				// 		}
				// 	}
				// }
				if( $_POST["scope"] != "restricted" && $_POST["scope"] != "private" &&
					isset($_POST["searchLocalityCITYKEY"]) && !empty($_POST["searchLocalityCITYKEY"]) && $_POST["searchLocalityCITYKEY"] != "") {
					$news["scope"]["type"]="public";
					foreach($_POST["searchLocalityCITYKEY"] as $key => $value){ if(!empty($value)){
						$city = City::getByUnikey($value); error_log("save news searchLocalityCITYKEY ".$value);
						$scope = array( "codeInsee"=>$city["insee"],
									"addressLocality"=>$city["name"],
									"geo" => $city["geo"]
								);
						//If no cp => on the whole city
						if (!(empty($city["cp"]))) {
							$scope["postalCode"] = $city["cp"];
						}
						$news["scope"]["cities"][] = $scope;
					}}
					foreach($_POST["searchLocalityCODE_POSTAL"] as $key => $value){ if(!empty($value)){
						$cities = City::getWhere(array("postalCodes.postalCode"=>$value), array("insee", "postalCodes.postalCode", "geo"), 1);
						if(!empty($cities)){
							//$city=$city[0];
							error_log("save news searchLocalityCODE_POSTAL");
							foreach($cities as $key=>$city) //var_dump($city); return;
							$news["scope"]["cities"][] = array( "codeInsee"=>$city["insee"],
																"postalCode"=>$city["postalCodes"][0]["postalCode"],
																"addressLocality"=>"", //$city["name"],
																"geo" => $city["geo"]
															);
						}
					}}
					foreach($_POST["searchLocalityDEPARTEMENT"] as $key => $value){ if(!empty($value)){
						$news["scope"]["departements"][] = array( "name"=>$value );
					}}

					foreach($_POST["searchLocalityREGION"] as $key => $value){ if(!empty($value)){
						$news["scope"]["regions"][] = array( "name"=>$value );
					}}
				}
				else{
					$scope = $_POST["scope"];
					$news["scope"]["type"]=$scope;
					/*if($scope== "public"){
						$address=SIG::getAdressSchemaLikeByCodeInsee($codeInsee,$postalCode);
						$news["scope"]["cities"][] = array("codeInsee"=>$codeInsee,
															"postalCode"=>$postalCode,
															"addressLocality"=>$address["addressLocality"],
															"geo" => $from
														);
					}*/
				}
			}
		 	if(isset($_POST["mentions"])){
				$news["mentions"] = $_POST["mentions"];
				$target="";
				if(@$_POST["parentType"]){
					$target=array("id"=>$_POST["parentId"],"type"=>$_POST["parentType"], "name");
				}
				Notification::actionOnNews ( ActStr::VERB_MENTION, ActStr::ICON_RSS, array("id" => Yii::app()->session["userId"],"name" => Yii::app()->session["user"]["name"]) , $target, $news["mentions"] )  ;
			}


			PHDB::insert(self::COLLECTION,$news);
			$news=NewsTranslator::convertParamsForNews($news);
		    $news["author"] = Person::getSimpleUserById(Yii::app()->session["userId"]);

		    
		    if(!empty($target)){
		    	$author = array(	"id"=> (String) $user["_id"],
		 							"type"=>Person::COLLECTION,
		 							"name"=> $user["name"]);
		    	$params = Mail::createParamsMails(ActStr::VERB_POST, $target, null, $author);
		   		Mail::mailNotif($_POST["parentId"], $_POST["parentType"], $params);
		    }
		    
			
		    /* Send email alert to contact@pixelhumain.com */
		  	if(@$type && $type=="pixels"){
		  		Mail::notifAdminBugMessage($news["text"]);
		  	}
		    return array("result"=>true, "msg"=>"Votre message est enregistré.", "id"=>$news["_id"],"object"=>$news);
		} else {
			return array("result"=>false, "msg"=>"Please Fill required Fields.");
		}
	}

	/**
	 * delete a news in database and the comments on that news
	 * @param type $id : id to delete
	 * @param type $userId : the userid asking to delete the news
	 * @param bool $removeComments
	 * @return array result => bool, msg => string
	 */
	public static function delete($id, $userId, $removeComments = false) {
		$news=self::getById($id);
		$nbCommentsDeleted = 0;

		//Check if the userId can delete the news
		if (! News::canAdministrate($userId, $id)) return array("result"=>false, "msg"=>Yii::t("common","You are not allowed to delete this news"), "id" => $id);

		//Delete image
		if(@$news["media"] && @$news["media"]["content"] && @$news["media"]["content"]["image"] && !@$news["media"]["content"]["imageId"]){
			$endPath=explode(Yii::app()->params['uploadUrl'],$news["media"]["content"]["image"]);
			//print_r($endPath);
			$pathFileDelete= Yii::app()->params['uploadDir'].$endPath[1];
			unlink($pathFileDelete);
		}

		if ($removeComments) {
			$res = Comment::deleteAllContextComments($id, News::COLLECTION, $userId);
			if (!$res["result"]) return $res;
		}

		//Remove the news
		$res = PHDB::remove(self::COLLECTION,array("_id"=>new MongoId($id)));

		return array("result" => true, "msg" => "The news with id ".$id." and ".$nbCommentsDeleted." comments have been removed with succes.");
	}

	/**
	 * delete all news linked to an element
	 * @param String $elementId  : id of the element the news depends on
	 * @param String $elementType : type of the element the news depends on
	 * @param type|bool $removeComments
	 * @return array result => bool, msg => String
	 */
	public static function deleteNewsOfElement($elementId, $elementType, $userId, $removeComments = false) {

		//Check if the $userId can delete the element
		$canDelete = Authorisation::canDeleteElement($elementId, $elementType, $userId);
		if (! $canDelete) {
			return array("result" => false, "msg" => "You do not have enough credential to delete this element news.");
		}

		//get all the news
		$where = array('$and' => array(
						array("target.id" => $elementId),
						array("target.type" => $elementType)
					));
		$news2delete = PHDB::find(self::COLLECTION, $where);
		$nbNews = 0;

		foreach ($news2delete as $id => $aNews) {
			$res = self::delete($id, $userId, true);
			if ($res["result"] == false) return $res;
			$nbNews++;
		}

		return array("result" => true, "msg" => $nbNews." news of the element ".$elementId." of type ".$elementType." have been removed with succes.");
	}

	/**
	 * delete a news in database from communevent with imageId
	 * @param String $id : imageId in media.content to delete
	*/

	public static function removeNewsByImageId($imageId){
		return PHDB::remove(self::COLLECTION,array("media.content.imageId"=>$imageId));
	}
	/**
	 * update a news in database
	 * @param String $newsId :
	 * @param string $name fields to update
	 * @param String $value : new value of the field
	 * @return array of result (result => boolean, msg => string)
	 */
	public static function updateField($newsId, $name, $value, $userId){
		$set = array($name => $value);
		//update the project
		PHDB::update( self::COLLECTION, array("_id" => new MongoId($newsId)),
		                          array('$set' => $set));

	    return array("result"=>true, "msg"=>Yii::t("common","News well updated"), "id"=>$newsId);
	}
	/**
	* Get array of news order by date of creation
	* @param array $array is the array of news to return well order
	* @param array $cols is the array indicated on which column of $array it is sorted
	**/
	public static function sortNews($array, $cols){
		$colarr = array();
	    foreach ($cols as $col => $order) {
	        $colarr[$col] = array();
	        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
	    }
	    $eval = 'array_multisort(';
	    foreach ($cols as $col => $order) {
	        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
	    }
	    $eval = substr($eval,0,-1).');';
	    eval($eval);
	    $ret = array();
	    foreach ($colarr as $col => $arr) {
	        foreach ($arr as $k => $v) {
	            $k = substr($k,1);
	            if (!isset($ret[$k])) $ret[$k] = $array[$k];
	            $ret[$k][$col] = $array[$k][$col];
	        }
	    }
	    return $ret;
	}

	public static function getNewsToModerate($whereAdditional = null, $limit = 0) {

		$where = array(
          "reportAbuse"=> array('$exists'=>1)
          ,"isAnAbuse" => array('$exists'=>0)
          ,"target.id" => array('$exists'=>1)
          ,"target.type" => array('$exists'=>1)
          ,"scope.type" => array('$exists'=>1)
          //One news has to be moderated X times
          ,"reportAbuseCount" => array('$gt' => 0)
          //One moderator can't moderate 2 times a news
          ,"moderate.".Yii::app()->session["userId"] => array('$exists'=>0)
        );
        if(count($whereAdditional)){
        	$where = array_merge($where,$whereAdditional);
        }
        return PHDB::findAndSort(self::COLLECTION, $where, array("date" =>1), $limit);
	}
	/*
	* Upload image from media url content if image is not from communevent
	* Image stock in folder ph/upload/news
	* @param string $urlImage, image url to upload
	* @param string $size, defines image size for resizing
	* @param string $authorId, defines name of img
	*/
	public static function uploadNewsImage($urlImage,$size,$authorId){
		$allowed_ext = array('jpg','jpeg','png','gif');
    	$ext = strtolower(pathinfo($urlImage, PATHINFO_EXTENSION));
    	if(empty($ext))
    		$ext="png";
    	if(strstr($ext,"?")){
    		$ext = explode( "?", $ext );
    		$ext = $ext[0];
    	}
		$dir=Yii::app()->params['defaultController'];
		$folder="news";
		$upload_dir = Yii::app()->params['uploadUrl'].$dir.'/'.$folder;
		//echo $upload_dir;
		$name=time()."_".$authorId.".".$ext;
		if(!file_exists ( $upload_dir )) {
			mkdir($upload_dir, 0775);
		}
		if($size="large"){
			$maxWidth=500;
			$maxHeight=500;
		}else{
			$maxWidth=100;
			$maxHeight=100;
		}
		$quality=100;
 		$imageUtils = new ImagesUtils($urlImage);
		$destPathThumb = $upload_dir."/".$name;
		$imageUtils->resizePropertionalyImage($maxWidth,$maxHeight)->save($destPathThumb,$quality);
		return $destPathThumb;
	}

	/**
	 * Return true if the user can administrate the news. The user can administrate a news when :
	 *     - he is super admin
	 *     - he is the author of the news
	 *     - he is admin of the element the news is a target
	 * @param Strinf $userId the userId to check the credential
	 * @param String $id the news id to check
	 * @return bool : true if the user can administrate the news, false else
	 */
	public static function canAdministrate($userId, $id) {
        $news = self::getById($id, false);

        if (empty($news)) return false;
        if (@$news["author"] == $userId) return true;
        if (Authorisation::isUserSuperAdmin($userId)) return true;
        $parentId = @$news["target"]["id"];
        $parentType = @$news["target"]["type"];

        $isAdmin = Authorisation::isElementAdmin($parentId, $parentType, $userId);
        return $isAdmin;
    }

}
?>
