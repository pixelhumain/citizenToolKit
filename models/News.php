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
	public static function getById($id,$followsArrayIds=null) {
	  	$news = PHDB::findOne( self::COLLECTION,array("_id"=>new MongoId($id)));
	  	if(@$news["type"]){
			$news=NewsTranslator::convertParamsForNews($news,true,$followsArrayIds);
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
	  	$res = PHDB::findAndSort( self::COLLECTION,$params,$sort,$limit);f

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
	public static function getNewsForObjectId($param,$sort=array("created"=>-1),$type, $followsArrayIds=null)
	{
		//$param=array();
	    $res = PHDB::findAndSort(self::COLLECTION, $param,$sort,6);
	    foreach ($res as $key => $news) {
		    if(@$news["type"]){
			    $newNews=NewsTranslator::convertParamsForNews($news, false, $followsArrayIds);
			    if(!empty($newNews))			  		
					$res[$key]=$newNews;
				else
					unset($res[$key]);
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
		 	$keyLocality=$user["address"]["codeInsee"];
		 	$postalCode=$user["address"]["postalCode"];
		 	$typeNews=@$_POST["type"] ? $_POST["type"] : "news";
			$news = array("type" => $typeNews, //"news",
						  "text" => $_POST["text"],
						  "author" => Yii::app()->session["userId"],
						  "date"=>new MongoDate(time()),
						  "sharedBy"=> array(array("id"=>Yii::app()->session["userId"],
						  					 "type"=>Person::COLLECTION,
						  					 "updated"=>new MongoDate(time()),
						  					)),

						  //"updated"=>new MongoDate(time()),
						  "created"=>new MongoDate(time()));

			if(@$_POST["targetIsAuthor"]==true){
				$news["sharedBy"] = array(array("id"=>$_POST["parentId"],
						  					 "type"=>$_POST["parentType"],
						  					 "updated"=>new MongoDate(time()),
						  					));
			}

			if(isset($_POST["date"])){
				$news["date"] = new MongoDate(strtotime(str_replace('/', '-', $_POST["date"])));
			}
			if (isset($_POST["media"])){
				$news["media"] = $_POST["media"];
				if(@$_POST["media"]["content"] && @$_POST["media"]["content"]["image"] && !@$_POST["media"]["content"]["imageId"]){
					$urlImage = self::uploadNewsImage($_POST["media"]["content"]["image"],$_POST["media"]["content"]["imageSize"],Yii::app()->session["userId"]);
					$news["media"]["content"]["image"]=	 Yii::app()->baseUrl."/".$urlImage;
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
				$type=$_POST["parentType"];
				$news["target"]["type"] = $type;
				$from="";
				$parent = Element::getByTypeAndId($type, $_POST["parentId"]);
				if( isset( $parent['geo'] ) )
					$from = $parent['geo'];
				if(@$parent["address"]){
					$codeInsee=$parent["address"]["codeInsee"];
					$postalCode=$parent["address"]["postalCode"];
				}
				if( $_POST["scope"] != "restricted" && $_POST["scope"] != "private" && !empty($_POST["localities"]) ) {
					$news["scope"]["type"]="public";
					$localities = $_POST["localities"] ;
			  		if(!empty($localities)){
			  			foreach ($localities as $key => $locality){

							if(!empty($locality)){
								if($locality["type"] == City::CONTROLLER){
									$city = City::getById($key);
									//$city = City::getByUnikey($value);
									$scope = array( "parentId"=>(String) $city["_id"],
													"parentType"=>City::COLLECTION,
													"name"=>$city["name"],
													"geo" => $city["geo"]
												);
									if (!(empty($city["cp"]))) {
										$scope["postalCode"] = $city["cp"];
									}else if (!(empty($city["postalCode"]))) {
										$scope["postalCode"] = $city["postalCode"];
									}

									$scope = array_merge($scope, Zone::getLevelIdById((String) $city["_id"], $city, City::COLLECTION) ) ;
									$news["scope"]["localities"][] = $scope;
								}
								else if($locality["type"] == "cp"){

									$where = array( "postalCodes.postalCode"=>strval($key), 
													"country"=> $locality["countryCode"]) ;
									//var_dump($where);
									$cities = City::getWhere($where);
									if(!empty($cities)){
										//$city=$city[0];
										$scope = array("postalCode"=>strval($key));
										$news["scope"]["localities"][] = $scope;

										foreach($cities as $keyC=>$city){
											$id = (String) $city["_id"];
											$scope = array( "parentId"=>(String) $city["_id"],
															"parentType"=>City::COLLECTION,
															"geo" => $city["geo"] );
											$scope = array_merge($scope, Zone::getLevelIdById((String) $city["_id"], $city, City::COLLECTION) ) ;
											$news["scope"]["localities"][] = $scope;
										}
										
									}
								}
								else{
									$zone = Zone::getById($key);
									$scope = array( "parentId"=>(String) $zone["_id"],
													"parentType"=>Zone::COLLECTION,
													"name"=>$zone["name"],
													"geo" => $zone["geo"]
												);
									$scope = array_merge($scope, Zone::getLevelIdById((String) $zone["_id"], $zone, Zone::COLLECTION) ) ;

									$news["scope"]["localities"][] = $scope;
								}
							}
						}
			  		}
				}
				else{
					$scope = $_POST["scope"];
					$news["scope"]["type"]=$scope;
					if($scope== "public"){

						if(!empty($localities)){
							$city = City::getById($key);
							//$city = City::getByUnikey($value);
							$scope = array( "parentId"=>(String) $city["_id"],
											"parentType"=>City::COLLECTION,
											"name"=>$city["name"],
											"geo" => $city["geo"]
										);
							if (!(empty($postalCode))) {
								$scope["postalCode"] = $postalCode;
							}
							$news["scope"]["localities"][] = $scope;
						}
					}
				}		
			}
		 	if(isset($_POST["mentions"]))
				$news["mentions"] = $_POST["mentions"];

			PHDB::insert(self::COLLECTION,$news);

			//NOTIFICATION MENTIONS
			if(isset($news["mentions"])){
				$target=array("id"=>(string)$news["_id"],"type"=>self::COLLECTION);
				//if(@$_POST["parentType"]){
				//	$target["parent"]=array("id"=>$_POST["parentId"],"type"=>$_POST["parentType"]);		
				//}
				//$target=array("id"=>$_POST["parentId"],"type"=>$_POST["parentType"]);
				if(@$_POST["targetIsAuthor"] && @$_POST["parentType"]){
					//if($targetIsAuthor){
					$authorName=Element::getElementSimpleById($_POST["parentId"], $_POST["parentType"]);
					$author=array("id"=>$_POST["parentId"], "type"=>$_POST["parentType"],"name"=>$authorName["name"]);
					//	$authorName=$authorName["name"];
					//} else{
					//	$authorName=$author["name"];
					//}
				}else{
					$author=array("id" => Yii::app()->session["userId"],"type"=>Person::COLLECTION, "name" => Yii::app()->session["user"]["name"]);
				}
				Notification::notifyMentionOn($author , $target, $news["mentions"], null, $_POST["scope"]);
			}

			//NOTIFICATION POST
			$target=array("id"=>$_POST["parentId"],"type"=>$_POST["parentType"]);
			if(@$news["targetIsAuthor"])
				$target["targetIsAuthor"]=true;
			else if($_POST["parentType"]==Person::COLLECTION && $_POST["parentId"] != Yii::app()->session["userId"])
				$target["userWall"]=true;
			if($_POST["parentType"] != Person::COLLECTION || $_POST["parentId"] != Yii::app()->session["userId"])
        		Notification::constructNotification(ActStr::VERB_POST, array("id" => Yii::app()->session["userId"],"name" => Yii::app()->session["user"]["name"]) , $target, null, null);
			$news=NewsTranslator::convertParamsForNews($news);			  		
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

	/**
	 * delete a news in database and the comments on that news
	 * @param type $id : id to delete
	 * @param type $userId : the userid asking to delete the news
	 * @param bool $removeComments 
	 * @return array result => bool, msg => string
	 */
	public static function delete($id, $userId, $removeComments = false,$deleteProcess=false) {
		$news=self::getById($id);
		$nbCommentsDeleted = 0;

		//Check if the userId can delete the news
		$authorization=self::canAdministrate($userId, $id,$deleteProcess);
		if (!$authorization) return array("result"=>false, "userId"=>$userId, "msg"=>Yii::t("common","You are not allowed to delete this news"), "id" => $id);
		if($authorization=="share")
			$countShare=count($news["sharedBy"]);
		if($authorization===true || (@$countShare && $countShare==1)){
			//Delete image
			if(@$news["media"] && @$news["media"]["content"] && @$news["media"]["content"]["image"] && !@$news["media"]["content"]["imageId"]){
				$endPath=explode(Yii::app()->params['uploadUrl'],$news["media"]["content"]["image"]);
				$pathFileDelete= Yii::app()->params['uploadDir'].$endPath[1];
				if(file_exists ( $pathFileDelete )) 
					unlink($pathFileDelete);
			}
		
			//récupère les activityStream liés à la news
			$actStream = PHDB::find(self::COLLECTION,array("type"=>"activityStream",
															"verb"=>ActStr::TYPE_ACTIVITY_SHARE,
															"object.type"=>"news",
															"object.id"=>$id));
			//var_dump($id); var_dump($actStream); exit;
			//efface les commentaires des activityStream liés à la news
			if(!empty($actStream))
				foreach ($actStream as $key => $value) { //var_dump($key); exit;
					//error_log("try to delete comments where contextId=".$key);
					PHDB::remove(Comment::COLLECTION,array( "contextType"=>"news",
															"contextId"=>$key));
				}
			//efface les activityStream lié à la news
			PHDB::remove(self::COLLECTION,array("type"=>"activityStream",
												"verb"=>ActStr::TYPE_ACTIVITY_SHARE,
												"object.type"=>"news",
												"object.id"=>$id));

			if ($removeComments) {
				$res = Comment::deleteAllContextComments($id, News::COLLECTION, $userId,$deleteProcess);
				if (!$res["result"]) return $res;
			}

			//Remove the news
			$res = PHDB::remove(self::COLLECTION,array("_id"=>new MongoId($id)));
		} else if($authorization=="share" && $countShare > 1){
			$key=array_search($userId,array_column($news["sharedBy"],"id"));
			//unset($news["sharedBy"][$key]);
			$shareUpdate=true;
			$res = PHDB::update(self::COLLECTION, array("_id"  => new MongoId($id) ), array('$pull'=>array("sharedBy"=>array("id"=>$userId))));
		}
		$res=array("result" => true, 
					"msg" => "The news with id ".$id." and ".$nbCommentsDeleted." comments have been removed with succes.",
					"type"=>$news["type"],
					"commentsDeleted" => $nbCommentsDeleted
					);
		if(@$shareUpdate){
			$followsArrayIds=[];
			$parent=Element::getElementSimpleById(Yii::app()->session["userId"],Person::COLLECTION,null, array("links"));
			if(@$parent["links"]["follows"] && !empty($parent["links"]["follows"])){
				foreach ($parent["links"]["follows"] as $key => $data){
					array_push($followsArrayIds,$key);
				}
			}
			$res["newsUp"]=News::getById($id, $followsArrayIds);
		}
		return $res;
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
		$where = array('$or' => array(
						array("target.id" => $elementId),
						array("object.id" => $elementId)
					));
		
		/*$where = array('$and' => array(
						array("target.id" => $elementId),
						array("target.type" => $elementType)
					));*/
		$news2delete = PHDB::find(self::COLLECTION, $where);
		$nbNews = 0;		
		
		foreach ($news2delete as $id => $aNews) {
			$res = self::delete($id, $userId, true,true);
			if ($res["result"] == false) return $res;
			$nbNews++;
		}

		return array("result" => true, "msg" => $nbNews." news of the element ".$elementId." of type ".$elementType." have been removed with succes.");
	}

	/**
	 * delete a news in database from communevent with imageId
	 * @param String $id : imageId in media.content to delete
	*/


	public static function share($verb, $targetId, $targetType, $comment=null, $activityValue=null){

		$share = PHDB::findOne( News::COLLECTION , 
								array(	"verb"=>$verb, 
										"object.id"=>@$activityValue["id"], 
										"object.type"=>@$activityValue["type"]
										)
								);
		
		if($share!=null){
			
			$allShare = array();
			//regarde tous les sharedBy
			foreach ($share["sharedBy"] as $key => $value) {
			 	if($value["id"] != Yii::app()->session["userId"]){ //si ce n'est pas moi je garde ce partage
			 		$allShare[] = $value;
			 	}
			} 
			
			//je me rajoute à la liste des allShare
			$share["sharedBy"] = array_merge($allShare, 
								 array(array( 	"id" => Yii::app()->session["userId"],
												"type"=> Person::COLLECTION,
												"comment"=>@$comment,
												"updated" => new MongoDate(time())),
        						));
		
			PHDB::update ( News::COLLECTION , 
							array( "_id" => $share["_id"]), 
                            $share);
			$idNews=(string)$share["_id"];
			
		}else{
			$buildArray = array(
				"type" => ActivityStream::COLLECTION,
				"verb" => $verb,
				"target" => array("id" => $targetId,
								  "type"=> $targetType),
				"author" => Yii::app()->session["userId"],
				"object" => $activityValue,
				"scope" => array("type"=>"restricted"),
			    "created" => new MongoDate(time()),
				"sharedBy" => array(array(	"id" => Yii::app()->session["userId"],
											"type"=> Person::COLLECTION,
											"comment"=>@$comment,
											"updated" => new MongoDate(time()))),
			);

			//$params=ActivityStream::buildEntry($buildArray);
			$newsShared=ActivityStream::addEntry($buildArray);
			$idNews=(string)$newsShared["_id"];
			//error_log("share new");
		}
		$newsShared=News::getById($idNews);
		return array("result"=>true, "msg"=> Yii::t("common", "News has been shared with your network"), "data"=>$newsShared);
	}

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
	 * update a mentionsComent array of a news in database
	 * @param String $newsId : 
	 * @param array $mentionsComment to push news on timeline
	 */
	
	public static function updateCommentMentions($mentionsComment,$id){
		$news = PHDB::findOneById( self::COLLECTION , $id);
		if(@$news["commentMentions"]){
			foreach ($news["commentMentions"] as $value) {
				array_push($mentionsComment, $value);
			}
		}
		PHDB::update ( self::COLLECTION , 
							array( "_id" => new MongoId($id)), 
                            array('$set'=>array("commentMentions"=>$mentionsComment)));
		return true;
	}
	/**
	 * update a news in database
	 * @param String $newsId : 
	 * @param string $name fields to update
	 * @param String $value : new value of the field
	 * @return array of result (result => boolean, msg => string)
	 */
	public static function update($params){
		if((isset($_POST["text"]) && !empty($_POST["text"])) || (isset($_POST["media"]) && !empty($_POST["media"])))
	 	{
			$set = array(
				 "text" => $_POST["text"],
				 "updated"=>new MongoDate(time()),
			);
			$unset=array();
			if (@$_POST["media"]){
				if($_POST["media"]=="unset"){
					$unset["media"]="";
				}else{
					$set["media"] = $_POST["media"];
					if(@$_POST["media"]["content"] && @$_POST["media"]["content"]["image"] && !@$_POST["media"]["content"]["imageId"] 
							&& strpos($_POST["media"]["content"]["image"], Yii::app()->baseUrl) === false){
						//echo Yii::app()->baseUrl; 
						//echo strpos($_POST["media"]["content"]["image"], Yii::app()->baseUrl);
						$urlImage = self::uploadNewsImage($_POST["media"]["content"]["image"],$_POST["media"]["content"]["imageSize"],Yii::app()->session["userId"]);
						$set["media"]["content"]["image"]=	 Yii::app()->baseUrl."/".$urlImage;
					}
				}
			}
			if(@$_POST["tags"])
				$set["tags"] = $_POST["tags"];
		 	if(@$_POST["mentions"])
				$set["mentions"] = $_POST["mentions"];
			else
				$unset["mentions"]="";
			$modify=array('$set'=>$set);
			if(@$unset && !empty($unset))
				$modify['$unset']=$unset;
		//update the project
		PHDB::update( self::COLLECTION, array("_id" => new MongoId($_POST["idNews"])), 
		                          $modify);
		$news=self::getById($_POST["idNews"]);
	    return array("result"=>true, "msg"=>Yii::t("common","News well updated"), "object"=>$news);
	}
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
	        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower(@$row[$col]); }
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
	            $ret[$k][$col] = @$array[$k][$col];
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
	public static function uploadNewsImage($urlImage,$size,$authorId,$actionUpload=true){
		$allowed_ext = array('jpg','jpeg','png','gif'); 
    	$ext = strtolower(pathinfo($urlImage, PATHINFO_EXTENSION));
    	if(empty($ext))
    		$ext="png";
    	if(strstr($ext,"?")){
    		$ext = explode( "?", $ext );
    		$ext = $ext[0];
    	}
		$dir="communecter";
		$folder="news";
		$upload_dir = Yii::app()->params['uploadDir'].$dir.'/'.$folder; 
		$returnUrl= Yii::app()->params['uploadUrl'].$dir.'/'.$folder;
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
		$returnUrl=$returnUrl."/".$name;
		$imageUtils->resizePropertionalyImage($maxWidth,$maxHeight)->save($destPathThumb,$quality);
		return $returnUrl;
	}

	public static function getStrucChannelRss($elementName) {

		$xmlElement = new SimpleXMLElement(
			'<?xml version="1.0" encoding="UTF-8"?><rss version="2.0">
				<channel></channel>
				<title> ' . $elementName . ' </title>
				<description>Communecter, un site fait par les communs pour les communs </description>
					<image>
      					<url>http://127.0.0.1/ph/assets/7d331fe5/images/Communecter-32x32.svg</url>
      					</image>
				</rss>');

		//var_dump($xml_element);

		return $xmlElement;

	}

	public static function getStrucKml() {

		$kmlElement = new SimpleXMLElement(
		'<?xml version="1.0" encoding="UTF-8"?>
			<kml xmlns="http://www.opengis.net/kml/2.2">
			

			</kml>');

		//var_dump($xml_element);

		return $kmlElement;

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
	public static function canAdministrate($userId, $id,$deleteProcess=false) {
        $news = self::getById($id, false);
        
        if (empty($news)) return false;
        if (@$news["author"]["id"] == $userId && (!@$news["verb"] || $news["verb"]!="share")) return true;
        if (@$news["sharedBy"] && in_array($userId,array_column($news["sharedBy"],"id"))) return "share";
        if (Authorisation::isUserSuperAdmin($userId)) return true;
        $what = (@$news["verb"] == "create" ) ? "object" : "target" ;
	    $parentId = @$news[$what]["id"];
	    $parentType = @$news[$what]["type"];
	    if(@$deleteProcess) return Authorisation::isOpenEdition($parentId, $parentType);
        return Authorisation::isElementAdmin($parentId, $parentType, $userId);
    }

}
?>