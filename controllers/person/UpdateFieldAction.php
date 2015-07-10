<?php
/**
* Update an information field for a person
*/
class UpdateFieldAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        if (!empty($_POST["pk"])) 
        {
            if (! empty($_POST["name"]) && ! empty($_POST["value"])) 
                $res = Person::updatePersonField($_POST["pk"], $_POST["name"], $_POST["value"], Yii::app()->session["userId"] );
        } else {
                $res = array("result"=>false, "error"=>"Something went wrong", $_POST["name"]=>$_POST["value"]);
        }
        echo Rest::json($res);
    }
}