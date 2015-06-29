<?php
class SaisirAction extends CAction
{
    public function run()
    {
       $controller=$this->getController();
       $controller->render("saisir");
    }
}
?>