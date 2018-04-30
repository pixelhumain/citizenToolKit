<?php

class InviteAction extends CAction {
    
    public function run() {
    	
        $controller=$this->getController();
        $params = array(
			"parentType" => ( empty($_POST["parentType"]) ? Person::COLLECTION : $_POST["parentType"] ) ,
			"parentId" => ( empty($_POST["parentId"]) ? Yii::app()->session["userId"] : $_POST["parentId"] ) ,
			"search" => true
		);

		if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("invite",$params,true);
        else 
            $controller->render( "invite" ,$params);   
    }
}