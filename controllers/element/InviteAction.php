<?php

class InviteAction extends CAction {
    
    public function run($id=null, $type=null) {
    	
        $controller=$this->getController();
        $params = array(
			"parentType" => ( empty($type) ? Person::COLLECTION : $type ) ,
			"parentId" => ( empty($id) ? Yii::app()->session["userId"] : $id ) ,
			"search" => true
		);

        if($params["parentType"] != Person::COLLECTION){
            $parent = Element::getElementById($id, $type, null, array("links"));
            $params["parentLinks"] = $parent["links"];
        }
        
		if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("invite",$params,true);
        else 
            $controller->render( "invite" ,$params);   
    }
}