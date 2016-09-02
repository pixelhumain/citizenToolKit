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
            
            $multitags = isset($multitags) ? $multitags : array();
            foreach ($multitags as $key => $value) {
            	$multitags[$key]["active"] = $multitags[$key]["active"] == "true" ? true : false;
            }
            $res = Person::updatePersonField(Yii::app()->session['userId'], "multitags", $multitags, Yii::app()->session["userId"]);
            //$res = array();
            return Rest::json($res);
        }
    }
}

?>