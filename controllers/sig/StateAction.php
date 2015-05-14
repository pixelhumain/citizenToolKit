<?php
class StateAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $controller->renderPartial("state");
    }
}