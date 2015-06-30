<?php
class GoogleAction extends CAction
{
    public function run()
    {
       $controller=$this->getController();
       $controller->render("google");
    }
}
?>