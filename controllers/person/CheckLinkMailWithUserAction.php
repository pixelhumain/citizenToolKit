<?php
/**
* Check if the username is unique in the db.
*/
class CheckLinkMailWithUserAction extends CAction {
    
    public function run() {
      $params = array();
        $res = Person::isLinkedEmail($_POST['mail'], Yii::app()->session["userId"]);
        //var_dump($res);
      if($res != false){
        $params["result"] = false;
      }else
        $params["result"] = true;
      
        Rest::json($params);
    }
}