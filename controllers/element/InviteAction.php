<?php

class InviteAction extends CAction {
    
    public function run($id=null, $type=null) {
    	
        $controller=$this->getController();
        $params = array(
			"parentType" => ( empty($type) ? Person::COLLECTION : $type ) ,
			"parentId" => ( empty($id) ? Yii::app()->session["userId"] : $id ) ,
			"search" => true
		);
        
		if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("invite",$params,true);
        else 
            $controller->render( "invite" ,$params);   
    }
}