<?php

class PreviewDataAction extends CAction
{
    public function run()
    {
        $params = Import::previewData($_POST);
        //$params = Import::setWikiDataID($_POST);
        return Rest::json($params);
    }
}

?>