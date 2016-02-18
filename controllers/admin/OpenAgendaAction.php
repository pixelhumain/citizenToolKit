<?php
class OpenAgendaAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
    	$params = array();

        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("openAgenda",$params,true);
        else 
            $controller->render("openAgenda",$params);
    }
}

?>