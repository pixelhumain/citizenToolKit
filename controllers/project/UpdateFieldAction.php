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
			$projectId = $_POST["pk"];
			$projectFieldName = $_POST["name"];
			$projectFieldValue = $_POST["value"];
			try {
				Project::updateProjectField($projectId, $projectFieldName, $projectFieldValue, Yii::app()->session["userId"] );
				if(Import::isUncomplete($projectId, Project::COLLECTION)){
					Import::checkWarning($projectId, Project::COLLECTION, Yii::app()->session['userId'] );
				}

			} catch (CTKException $e) {
				return Rest::json(array("result"=>false, "msg"=>$e->getMessage(), $projectFieldName=>$projectFieldValue));
			}
		} else {
          return Rest::json(array("result"=>false,"msg"=>Yii::t("common","Invalid request")));
        }
        
        return Rest::json(array("result"=>true, "msg"=>Yii::t("project","Project well updated"), $projectFieldName=>$projectFieldValue));
    }
}