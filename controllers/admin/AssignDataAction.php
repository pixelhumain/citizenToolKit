<?php

class AssignDataAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
        $params = NewImport::parsing($_POST);
        return Rest::json($params);
    }
}

?>