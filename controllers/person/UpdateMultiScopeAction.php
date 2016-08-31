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
            
            $multiscope = isset($_POST["multiscopes"]) ? $_POST["multiscopes"] : array();
            foreach ($multiscope as $key => $value) {
            	$multiscope[$key]["active"] = $multiscope[$key]["active"] == "true" ? true : false;
            }
            $res = Person::updatePersonField(Yii::app()->session['userId'], "multiscopes", 
                                            $multiscope, Yii::app()->session["userId"]);
            
            //var_dump(Yii::app()->session['userId']);
            //$res = array();
            return Rest::json($res);
        }else{
            return Rest::json(array("msg"=>"You are not connected"));
        }
    }
}

?>