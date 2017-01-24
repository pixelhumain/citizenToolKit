<?php
class GetDataAction extends CAction
{
    public function run($id, $type)
    {
        $item = PHDB::findOne( $type ,array("_id"=>new MongoId($id)));
  		echo Rest::json($item);
    }
}