<?php
class CheckCedexAction extends CAction{ 
    public function run()
    {
        $controller = $this->getController();
        $params = Import::checkCedex($_POST);
        return Rest::json($params);
    }
}

?>