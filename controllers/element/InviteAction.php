<?php

class InviteAction extends CAction {
    
    public function run() {
    	
        $controller=$this->getController();
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("invite",["search"=>true],true);
        else 
            $controller->render( "invite" , ["search"=>true]);   
    }
}