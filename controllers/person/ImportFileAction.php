<?php
class ImportFileAction extends CAction
{
    public function run()
    {
       $controller=$this->getController();
       $controller->render("importfile");
    }
}
?>