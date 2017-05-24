<?php

class ShareAction extends CAction
{
	 /**
	 * TODO Clement : La PHPDOC
	 */
    public function run() {
	    assert('!empty($_POST["childType"]); //The child type is mandatory');
	    assert('!empty($_POST["parentId"]); //The parent id is mandatory');
	    assert('!empty($_POST["parentType"]); //The parent type is mandatory');

	    $result = array("result"=>false, "msg"=>Yii::t("common", "Incorrect request"));
		
		if ( ! Person::logguedAndValid() ) {
			return array("result"=>false, "msg"=>Yii::t("common", "You are not loggued or do not have acces to this feature "));
		}


	    $parentId = $_POST["parentId"];
    	$parentType = $_POST["parentType"];
    	
	    $child = array(
			"id" => @$parentId,
	    	"type" => $parentType
	    );
    	
    	//le cas du partage de news
    	//on rajoute l'identité de l'auteur de la news
	    if($parentType == "news"){
			$object=News::getById($parentId);
			$authorNews=Person::getById(@$object["author"]["id"]);
			$child["authorName"] = @$authorNews["name"];
			$child["authorId"] = @$object["author"]["id"];
		}
		
    	$result = News::share(ActStr::VERB_SHARE,@$_POST["childId"],@$_POST["childType"], @$_POST["comment"], $child);
		Rest::json($result);
    }

}