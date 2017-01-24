<?php
/**
* Update an information field for a person
*/
class UpdateFieldAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        if (!empty($_POST["pk"]) && ! empty($_POST["name"]) && ! empty($_POST["value"])) {
			$newsId = $_POST["pk"];
			$newsFieldName = "text";
			$newsFieldValue = $_POST["value"];
			try {
				Comment::updateField($newsId, $newsFieldName, $newsFieldValue, Yii::app()->session["userId"]);
			} catch (CTKException $e) {
				return Rest::json(array("result"=>false, "msg"=>$e->getMessage(), $_POST["name"]=>$_POST["value"]));
			}
		} else {
          return Rest::json(array("result"=>false,"msg"=>Yii::t("common","Invalid request")));
        }
        return Rest::json(array("result"=>true, "msg"=>Yii::t("common","News well updated"),"text"=> $newsFieldValue,"id"=>$newsId));
    }
}