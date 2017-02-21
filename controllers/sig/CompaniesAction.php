<?php
class CompaniesAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $controller->renderPartial("companies");
    }
}