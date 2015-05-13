<?php
class LatestAction extends CAction
{
    public function run($type=null, $id= null)
    {
        $item = PHDB::findOne( $type ,array("_id"=>new MongoId($id)));
  		Rest::json($item);
    }
}