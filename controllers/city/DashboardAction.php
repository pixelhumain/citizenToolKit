<?php 

class DashboardAction extends CAction
{
    public function run( $id=null )
    {
        $controller=$this->getController();
        $this->redirect(Yii::app()->createUrl("/".$this->module->id."/city/index/insee/".$id));
    }
}