<?php

class ExportcsvAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
        $res = Import::exportcsv();
        Rest::json($res);
    }
}

?>