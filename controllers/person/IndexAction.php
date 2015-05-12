<?php
class IndexAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $controller->redirect(Yii::app()->createUrl("/".Yii::app()->controller->module->id."/person/dashboard"));
    }
}