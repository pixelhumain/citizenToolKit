<?php
class DeleteAction extends CAction
{
    public function run($id= null)
    {
        return Rest::json( News::delete($id));
    }
}