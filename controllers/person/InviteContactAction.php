<?php

class InviteContactAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
    	$params = array();

        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("inviteContact",$params,true);
        else 
            $controller->render("inviteContact",$params);
    }
}

?>