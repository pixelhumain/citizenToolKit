<?php

class DeleteMappingAction extends CAction{
    public function run(){

        $controller = $this->getController();
        $params = Import::deleteMapping($_POST);
        Rest::json($params);exit;
    }
}
?>