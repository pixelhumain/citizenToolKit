<?php

class PreviewDataAction extends CAction
{
    public function run()
    {
        $params = Import::previewData($_POST);
        return Rest::json($params);
    }
}

?>