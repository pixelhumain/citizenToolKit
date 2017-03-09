<?php
/**
* Check if the username is unique in the db.
*/
class GetUserIdByMailAction extends CAction {
    
    public function run() {
      $params = array();
        $res = Person::getPersonIdByEmail($_POST['mail']);
        
      if($res == false){
        $params["userId"] = "";
      }else
        {
        $params["userId"] = $res;
      }
      
        Rest::json($params);
    }
}