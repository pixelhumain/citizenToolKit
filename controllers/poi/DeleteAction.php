<?php

class DeleteAction extends CAction {
    
    public function run($type, $id) {
    	
        $controller=$this->getController();
        
       

        $res = Element::delete($type,$id, Yii::app()->session["userId"]);
        Rest::json($res);
    }
}