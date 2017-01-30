<?php

class ChooseInviteContactAction extends CAction
{
    public function run(){
        $controller = $this->getController();

        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("chooseinviteContact",$params,true);
        else 
            $controller->render("chooseinviteContact",$params);
    }
}

?>