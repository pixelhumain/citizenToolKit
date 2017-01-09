<?php

class SaveAction extends CAction {
/**
* Dashboard Organization
*/
    public function run() { 

        if( isset(Yii::app()->session["userId"]) )
        {
            $res = Element::save($_POST);
            echo json_encode( $res );  
        } else 
        	echo json_encode( array("result"=> false, "error"=>"401", "msg" => "Unauthorized Access.") );  
    }
}

?>