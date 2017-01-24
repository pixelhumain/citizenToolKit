<?php
class MainMapAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        //$controller->layout = "//layouts/mainSearch";
        $controller->renderPartial("/default/mainMap");
    }
}