<?php
class SaveAction extends CAction
{
    public function run() {
        $controller=$this->getController();

        if (isset(Yii::app()->session["userId"])) {
            try {
                $res = City::insert($_POST["city"], Yii::app()->session["userId"]);
            } catch (CTKException $e) {
                $res = array("result"=>false, "msg"=>$e->getMessage());
            }
            Rest::json($res);
        } else {
            $res = array("result"=>false, "msg"=>"You must be loggued to create a city");
        }
    }
}