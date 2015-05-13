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
          $personId = $_POST["pk"];
        if (! empty($_POST["name"]) && ! empty($_POST["value"])) {
          $personFieldName = $_POST["name"];
          $personFieldValue = $_POST["value"];
          Person::updatePersonField($personId, $personFieldName, $personFieldValue, Yii::app()->session["userId"] );
        }
        }else{
          $res = Rest::json(array("result"=>false, "error"=>"Something went wrong", $jobFieldName=>$jobFieldValue));
        }
    }
}