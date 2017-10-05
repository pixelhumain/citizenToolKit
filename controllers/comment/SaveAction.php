<?php
class SaveAction extends CAction
{
    public function run() {
        $controller=$this->getController();

        if (isset(Yii::app()->session["userId"])) {
            try {
                $res = Comment::insert($_POST, Yii::app()->session["userId"]);
                if(@$_POST["orderId"]){
                    Order::actionRating($_POST,$res["id"]);
                }
            } catch (CTKException $e) {
                $res = array("result"=>false, "msg"=>$e->getMessage());
            }

            Rest::json($res);
        } else {
            $res = array("result"=>false, "msg"=>"You must be loggued to create a comment");
        }
    }
}