<?php
class EventsAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $controller->renderPartial("events");
    }
}