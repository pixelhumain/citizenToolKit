<?php
class ViewerAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $controller->renderPartial("viewer");
    }
}