<?php

/**

 * a notification has been read by a user

 * remove it's entry in the notify node on an activity Stream for the current user

 * @return [json] 

 */

class UpdateAction extends CAction

{

    public function run()

    {
        $res = array();
        if( Yii::app()->session["userId"] )
        {
            if(@$_POST["action"] && $_POST["action"]=="seen")
                $action="isUnseen";
            else
                $action="isUnread";
            if(@$_POST["all"])
                $res = ActivityStream::updateNotificationsByUser($action);
            else
                $res = ActivityStream::updateNotificationById($_POST["id"],$action);
        } else
            $res = array('result' => false , 'msg'=>'something somewhere went terribly wrong');
        Rest::json($res);  
        Yii::app()->end();
    }
}
