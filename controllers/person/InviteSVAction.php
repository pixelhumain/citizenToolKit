<?php
class InviteSVAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();

        $params = array();
        if( isset($_GET["isNotSV"])) 
            $params["isNotSV"] = true;
    	if(Yii::app()->request->isAjaxRequest)
    		Rest::json(array("result"=>true, "content" => $controller->renderPartial("inviteSV", $params,true)));	
    }
}