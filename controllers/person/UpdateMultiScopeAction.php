<?php
/**
* Update an information field for a person
*/
class UpdateMultiScopeAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        //var_dump($_POST); 
        //var_dump(json_decode($_POST["multitag"], true));
        //return;
        foreach ($_POST["multiscopes"] as $key => $value) {
        	$_POST["multiscopes"][$key]["active"] = $_POST["multiscopes"][$key]["active"] == "true" ? true : false;
        }
        $res = Person::updatePersonField(Yii::app()->session['userId'], "multiscopes", $_POST["multiscopes"], Yii::app()->session["userId"]);
        //$res = array();
        return Rest::json($res);
    }
}

?>