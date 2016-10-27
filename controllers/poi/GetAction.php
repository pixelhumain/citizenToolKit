<?php

class GetAction extends CAction {
/**
* Dashboard Organization
*/
    public function run($type,$id) { 

        if(Yii::app()->request->isAjaxRequest && isset(Yii::app()->session["userId"]))
        {
        	 try {
                  $res = array("result" => true, "map" =>Element::getByTypeAndId($type,$id) );
             } catch (CTKException $e) {
                  $res = array("result"=>false, "msg"=>$e->getMessage());
             }
            echo json_encode( $res );  
        }
    }
}

?>