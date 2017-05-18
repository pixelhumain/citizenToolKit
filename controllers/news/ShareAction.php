<?php

class ShareAction extends CAction
{
	 /**
	 * TODO Clement : La PHPDOC
	 */
    public function run() {
	    assert('!empty($_POST["shareAuthorType"]); //The child type is mandatory');
	    assert('!empty($_POST["parentId"]); //The parent id is mandatory');
	    assert('!empty($_POST["parentType"]); //The parent type is mandatory');

	    $result = array("result"=>false, "msg"=>Yii::t("common", "Incorrect request"));
		
		if ( ! Person::logguedAndValid() ) {
			return array("result"=>false, "msg"=>Yii::t("common", "You are not loggued or do not have acces to this feature "));
		}


	    $parentId = $_POST["parentId"];
    	$parentType = $_POST["parentType"];
    	if(@$_POST["authorIdNews"])
    		$authorNews=array("id"=>$_POST["authorIdNews"],"type"=>$_POST["authorTypeNews"]);
    	$text=$_POST["text"];
	    $sharedContent= array(
			"id" => @$parentId,
	    	"type" => $parentType
	    );
    	
    	//le cas du partage de news
    	//on rajoute l'identité de l'auteur de la news
	    if($parentType == "news"){
			$object=News::getById($parentId);
			$authorNews=Person::getById(@$object["author"]["id"]);
			$sharedContent["authorName"] = @$authorNews["name"];
			$sharedContent["authorId"] = @$object["author"]["id"];
		}
		
    	$result = News::saveActivityShare(ActStr::VERB_SHARE,@$_POST["shareAuthorId"],@$_POST["shareAuthorType"], $sharedContent, $text, $authorNews);
		Rest::json($result);
    }

}