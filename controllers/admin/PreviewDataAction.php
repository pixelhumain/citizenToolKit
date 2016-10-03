<?php

class PreviewDataAction extends CAction
{
    public function run()
    {
        $params = NewImport::previewData($_POST);
        return Rest::json($params);
    }
}

?>