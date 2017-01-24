<?php

class DownloadFileAction extends CAction
{
    public function run()
    {
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=import.zip');
        header('Content-Length: ' . filesize(sys_get_temp_dir()."/import.zip"));
        readfile(sys_get_temp_dir()."/import.zip", true);
    }
}

?>