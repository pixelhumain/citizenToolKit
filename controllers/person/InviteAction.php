<?php
class LoginAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $controller->renderPartial("invite");
    }
}