<?php
class SaveAction extends CAction
{
    public function run($type=null, $id= null)
    {
        return Rest::json( News::save($_POST) );
    }
}