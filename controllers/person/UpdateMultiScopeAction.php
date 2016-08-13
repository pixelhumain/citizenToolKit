<?php
/**
* Update an information field for a person
*/
class UpdateMultiScopeAction extends CAction
{
    public function run()
    {
        if(isset(Yii::app()->session["userId"]) && !empty(Yii::app()->session["userId"])){
            $controller=$this->getController();
            
            foreach ($_POST["multiscopes"] as $key => $value) {
            	$_POST["multiscopes"][$key]["active"] = $_POST["multiscopes"][$key]["active"] == "true" ? true : false;
            }
            $res = Person::updatePersonField(Yii::app()->session['userId'], "multiscopes", 
                                            $_POST["multiscopes"], Yii::app()->session["userId"]);
            
            //var_dump(Yii::app()->session['userId']);
            //$res = array();
            return Rest::json($res);
        }else{
            return Rest::json(array("msg"=>"You are not connected"));
        }
    }
}

?>