<?php

class AssignDataAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
        $params = Import::parsing($_POST);
        return Rest::json($params);
    }
}

?>