<?php
class DeleteAction extends CAction
{
    public function run($eventId)
    {
    	if (isset(Yii::app()->session["userId"])) {
    		Event::delete($eventId, Yii::app()->session["userId"]);
    	}
    	
    	Rest::json(array('result' => true, "msg" => Yii::t("event","Event removed")));
    }
}