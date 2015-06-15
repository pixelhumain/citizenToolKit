<?php
class SaveAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();

        if(!Event::checkExistingEvents($_POST))
        { 
            try {
                $res = Event::saveEvent($_POST);
            } catch (CTKException $e) {
                $res = array("result"=>false, "msg"=>$e->getMessage());
            }
            Rest::json($res);
        } else
            Rest::json(array("result"=>false, "msg"=>Yii::t("event","Event already exist")));
        exit;
    }
}