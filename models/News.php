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
	}*/
	
	/**
	 * get news 
	 * @param type $params : is the condition of news generated
	 * @param type sort
	 * News limited to 15
	 */
	public static function getNewsForObjectId($param,$sort=array("created"=>-1),$type)
	{
	    $res = PHDB::findAndSort(self::COLLECTION, $param,$sort,15);
	    foreach ($res as $key => $news) {
		    if(@$news["type"]){
				$res[$key]=NewsTranslator::convertParamsForNews($news);			  		
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
	 		throw new CTKException("You must be loggued in to add a news entry.");

	 	if((isset($_POST["text"]) && !empty($_POST["text"])) || (isset($_POST["media"]) && !empty($_POST["media"])))
	 	{
		 	$codeInsee=$user["address"]["codeInsee"];
		 	$postalCode=$user["address"]["postalCode"];
			$news = array("type" => "news",
							"text" => $_POST["text"],
						  "author" => Yii::app()->session["userId"],
						  "date"=>new MongoDate(time()),
						  "created"=>new MongoDate(time()));

			if(isset($_POST["date"])){
				$news["date"] = new MongoDate(strtotime(str_replace('/', '-', $_POST["date"])));
			}
			if (isset($_POST["media"])){
				$news["media"] = $_POST["media"];
			}
			if(isset($_POST["tags"]))
				$news["tags"] = $_POST["tags"];
		 	if(isset($_POST["parentId"]))
				$news["target"]["id"] = $_POST["parentId"];
		 	if(isset($_POST["parentType"]))
		 	{
				$type=$_POST["parentType"];
				$news["target"]["type"] = $type;
				$from="";
				if($type == Person::COLLECTION ){
					$person = Person::getById($_POST["parentId"]);
					if( isset( $person['geo'] ) )
						$from = $person['geo'];
					$codeInsee=$person["address"]["codeInsee"];
					$postalCode=$person["address"]["postalCode"];
				}else if($type == Organization::COLLECTION ){
					$organization = Organization::getById($_POST["parentId"]);
					if( isset( $organization['geo'] ) )
						$from = $organization['geo'];
					$codeInsee=$organization["address"]["codeInsee"];
					$postalCode=$organization["address"]["postalCode"];
						$organization["type"]=Organization::COLLECTION;
					Notification::actionOnPerson ( ActStr::VERB_POST, ActStr::ICON_COMMENT, null , $organization )  ;
				}
				else if($type == Event::COLLECTION ){
					$event = Event::getById($_POST["parentId"]);
					if( isset( $event['geo'] ) )
						$from = $event['geo'];
					$codeInsee=$event["address"]["codeInsee"];
					$postalCode=$event["address"]["postalCode"];
					//Notification::actionOnEvent ( ActStr::VERB_POST, ActStr::ICON_COMMENT, null , $event )  ;
				}
				else if($type == Project::COLLECTION ){
					$project = Project::getById($_POST["parentId"]);
					if( isset( $project['geo'] ) )
						$from = $project['geo'];
					$codeInsee=$project["address"]["codeInsee"];
					$postalCode=$project["address"]["postalCode"];
					$project["type"] = Project::COLLECTION; 
					Notification::actionOnPerson ( ActStr::VERB_POST, ActStr::ICON_COMMENT, null , $project )  ;
				}
				if( isset($_POST["scope"])) {
					if(@$_POST["codeInsee"]){
						$news["scope"]["type"]="public";
						$news["scope"]["cities"][] = array("codeInsee"=>$_POST["codeInsee"], "postalCode"=>$_POST["postalCode"]);
					}
					else {
						$scope = $_POST["scope"];
						$news["scope"]["type"]=$scope;
						if($scope== "public"){
							$news["scope"]["cities"][] = array("codeInsee"=>$codeInsee,
																"postalCode"=>$postalCode,
																"geo" => $from
															);
						}
					}		
				}
			}
		 	
			PHDB::insert(self::COLLECTION,$news);
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
	 * delete a news in database
	 * @param String $id : id to delete
	*/
	public static function delete($id) {
		return PHDB::remove(self::COLLECTION,array("_id"=>new MongoId($id)));
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
}
?>