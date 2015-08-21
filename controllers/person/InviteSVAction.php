<?php
class InviteSVAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();

        if( isset($_GET["isNotSV"])) 
            $params["isNotSV"] = true;
    	if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial("inviteSV", $params,true);
    }
}