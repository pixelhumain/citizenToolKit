<?php 
class News {

	const COLLECTION = "news";
	/**
	 * get an project By Id
	 * @param type $id : is the mongoId of the project
	 * @return type
	 */
	public static function getById($id) {
	  	return PHDB::findOne( self::COLLECTION,array("_id"=>new MongoId($id)));
	}

	public static function getWhere($params) {
	  	return PHDB::findAndSort( self::COLLECTION,$params);
	}

	public static function getWhereSortLimit($params,$sort,$limit=1) {
	  	$res = PHDB::findAndSort( self::COLLECTION,$params,$sort,$limit);

	  	foreach ($res as $key => $news) {
	  		$res[$key]["author"] = Person::getById($news["author"]);
	  	}
	  	return $res;
	}
	
	public static function save($params)
	{
		//check a user is loggued 
	 	$user = Person::getById( Yii::app()->session["userId"] );
	 	//TODO : if type is Organization check the connected user isAdmin

	 	if(empty($user))
	 		throw new CTKException("You must be loggued in to add a news entry.");

	 	if( isset($_POST["name"]) && isset($_POST["text"]) )
	 	{
			$news = array("name" => $_POST["name"],
						  "text" => $_POST["text"],
						  "author" => Yii::app()->session["userId"],
						  "date"=>time(),
						  "created"=>time());

			if(isset($_POST["date"])){
				$news["date"] = $_POST["date"];//new MongoDate( strptime('$_POST["date"]', '%d/%m/%y') );
			}
			if(isset($_POST["tags"]))
				$news["tags"] = $_POST["tags"];
		 	if(isset($_POST["typeId"]))
				$news["id"] = $_POST["typeId"];
		 	if(isset($_POST["type"]))
		 	{
				$news["type"] = $_POST["type"];

				if($_POST["type"] == Person::COLLECTION ){
					$person = Person::getById($_POST["typeId"]);
					if( isset( $person['geo'] ) )
						$news["from"] = $person['geo'];
				}else if($_POST["type"] == Organization::COLLECTION ){
					$organization = Organization::getById($_POST["typeId"]);
					if( isset( $organization['geo'] ) )
						$news["from"] = $organization['geo'];

					Notification::actionOnPerson ( ActStr::VERB_POST, ActStr::ICON_COMMENT, null , $organization )  ;
				}
				else if($_POST["type"] == Event::COLLECTION ){
					$event = Event::getById($_POST["typeId"]);
					if( isset( $event['geo'] ) )
						$news["from"] = $event['geo'];

					Notification::actionOnPerson ( ActStr::VERB_POST, ActStr::ICON_COMMENT, null , $event )  ;
				}
				else if($_POST["type"] == Project::COLLECTION ){
					$project = Project::getById($_POST["typeId"]);
					if( isset( $project['geo'] ) )
						$news["from"] = $project['geo'];
					$project["type"] = Project::COLLECTION; 
					Notification::actionOnPerson ( ActStr::VERB_POST, ActStr::ICON_COMMENT, null , $project )  ;
				}

				/*if( $_POST["type"] == Organization::COLLECTION && Authorisation::isOrganizationAdmin( Yii::app()->session["userId"], $_POST["typeId"]) )
	 				throw new CTKException("You must be admin of this organization to post.");*/
			}

		 	if( isset($_POST["scope"])) {
		 		$news["scope"] = $_POST["scope"];
			}
			PHDB::insert(self::COLLECTION,$news);
		    $news["author"] = Person::getById($news["author"]);
		  	
		    return array("result"=>true, "msg"=>"Votre news est enregistré.", "id"=>$news["_id"],"object"=>$news);	
		} else {
			return array("result"=>false, "msg"=>"Please Fill required Fields.");	
		}
	}
}
?>