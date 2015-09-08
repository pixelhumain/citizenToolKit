<?php
/**
* Update an information field for a person
*/
class UpdateFieldAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $res = array("result"=>false, "error"=>"Something went wrong");
        if (!empty($_POST["pk"])) 
        {
            if (! empty($_POST["name"])) 
            {
                $res = Person::updatePersonField($_POST["pk"], $_POST["name"], @$_POST["value"], Yii::app()->session["userId"] );
                if( @$_POST["value"] == "bgCustom" && isset( $_POST["url"] ))
                    Person::updatePersonField($_POST["pk"], "bgUrl", $_POST["url"], Yii::app()->session["userId"] );
            }
        } 
        echo Rest::json($res);
    }
}