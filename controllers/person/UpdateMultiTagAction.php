<?php
/**
* Update an information field for a person
*/
class UpdateMultiTagAction extends CAction
{
    public function run()
    {
        if(isset(Yii::app()->session["userId"]) && !empty(Yii::app()->session["userId"])){
            $controller=$this->getController();
            
            foreach ($_POST["multitags"] as $key => $value) {
            	$_POST["multitags"][$key]["active"] = $_POST["multitags"][$key]["active"] == "true" ? true : false;
            }
            $res = Person::updatePersonField(Yii::app()->session['userId'], "multitags", $_POST["multitags"], Yii::app()->session["userId"]);
            //$res = array();
            return Rest::json($res);
        }
    }
}

?>