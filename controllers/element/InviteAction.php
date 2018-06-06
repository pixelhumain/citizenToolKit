<?php

class InviteAction extends CAction {
    
    public function run($id=null, $type=null) {
    	
        $controller=$this->getController();

        if(!empty(Yii::app()->session["userId"])){
            $params = array(
                "parentType" => ( empty($type) ? Person::COLLECTION : $type ) ,
                "parentId" => ( empty($id) ? Yii::app()->session["userId"] : $id ) ,
                "search" => true
            );

            if(!empty($params["parentType"]) && $params["parentType"] != Person::COLLECTION){
                $parent = Element::getElementById($id, $type, null, array("links"));
                $params["parentLinks"] = ( !empty($parent["links"]) ? $parent["links"] : array() );
            }
            
            if(Yii::app()->request->isAjaxRequest)
                echo $controller->renderPartial("invite",$params,true);
            else 
                $controller->render( "invite" ,$params);
        }
         
    }
}