<?php
class UpdateAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();

        $eventId = $_POST["eventId"];
        unset($_POST["eventId"]);
        $res = array("result"=>false, "msg"=>Yii::t("common", "Something went wrong!"));
        try {
            $event = Event::getAndCheckEvent($_POST,true);
            $res = Event::update($eventId , $event ,Yii::app()->session["userId"] );
        } catch (CTKException $e) {
            $res = array("result"=>false, "msg"=>$e->getMessage());
        }
        Rest::json($res);
        
        exit;
    }
}