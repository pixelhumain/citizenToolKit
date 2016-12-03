<?php

class PreviewDataAction extends CAction
{
    public function run()
    {
        $params = NewImport::previewData($_POST);
        //$params = NewImport::setCedex($_POST);
        //$params = Import::previewData($_POST);
        //$params = Import::belgique($_POST);
        return Rest::json($params);
    }
}

?>