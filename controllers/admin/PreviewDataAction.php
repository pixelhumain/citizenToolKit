<?php

class PreviewDataAction extends CAction
{
    public function run(){
    	header('Content-Type: application/json');
        $params = Import::previewData($_POST);
        //$params = Import::setWikiDataID($_POST);
        return Rest::json($params);
    }
}

?>