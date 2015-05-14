<?php
class NetworkAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $controller->renderPartial("network");
    }
}