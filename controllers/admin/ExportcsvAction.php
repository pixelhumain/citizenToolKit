<?php

class ExportcsvAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
        $res = Import::exportcsv($_POST);
        Rest::json($res);
    }
}

?>