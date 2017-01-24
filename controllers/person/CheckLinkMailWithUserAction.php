<?php
/**
* Check if the username is unique in the db.
*/
class CheckLinkMailWithUserAction extends CAction {
    
    public function run() {
      $params = array();
      $res = Person::getPersonFollowsByUser(Yii::app()->session["userId"]);

      if($res != false){
        $params["result"] = false;
      }else
        $params["result"] = true;

        $params["follows"] = $res ;
      
      Rest::json($params);
    }
}