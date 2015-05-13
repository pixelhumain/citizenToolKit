<?php
class InviteAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $controller->renderPartial("invite");
    }
}