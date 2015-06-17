<?php
/**
* Update an information field for a person
*/
class UpdateFieldAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        if (!empty($_POST["pk"])) {
          $projectId = $_POST["pk"];
        if (! empty($_POST["name"]) && ! empty($_POST["value"])) {
          $projectFieldName = $_POST["name"];
          $projectFieldValue = $_POST["value"];
          Project::updateProjectField($projectId, $projectFieldName, $projectFieldValue, Yii::app()->session["userId"] );
        }
        return Rest::json(array("result"=>true, "msg"=>Yii::t("project","Project well updated"), $projectFieldName=>$projectFieldValue));
        }else{
          $res = Rest::json(array("result"=>false, "error"=>"Something went wrong", $jobFieldName=>$jobFieldValue));
        }
    }
}