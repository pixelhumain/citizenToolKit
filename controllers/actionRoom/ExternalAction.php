<?php
class ExternalAction extends CAction
{
    public function run($id)
    {
      $controller=$this->getController();

      $room = PHDB::findOne (ActionRoom::COLLECTION, array("_id"=>new MongoId ( $id ) ),array('name','url','parentId','parentType') );
      if(!isset($room)) 
          throw new CTKException("Impossible to find this room");

      $controller->renderPartial( "iframe", array( "name" => @$room['name'],
                                  "room"=>$room,
          												"parentId" => @$room['parentId'],
          												"parentType" => @$room['parentType'],
          												 "url" => @$room['url'] )  );
    }
}
