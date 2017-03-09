<?php

class GetNotificationsAction extends CAction {
/**
* Dashboard Notifications By Element and User
*/
    public function run($type,$id) { 
        $res = array();
        if( Yii::app()->session["userId"] )
        {
          if($type != Person::COLLECTION){
            $params = array(
              '$and'=> 
                array(
                  array("notify.id.".Yii::app()->session["userId"] => array('$exists' => true),
                  "verb" => array('$ne' => ActStr::VERB_ASK)),
                  array('$or'=> array(
                    array("target.type"=>$type, "target.id" => $id),
                    array("target.parent.type"=>$type, "target.parent.id" => $id)
                    )
                  ) 
                ) 
              );
          }else
            $params = array("notify.id.".Yii::app()->session["userId"] => array('$exists' => true));
            $res = ActivityStream::getNotificationsByTypeAndId($params);
        } else
            $res = array('result' => false , 'msg'=>'something somewhere went terribly wrong');
            
        Rest::json($res,false);  
        Yii::app()->end();
    }
}

?>