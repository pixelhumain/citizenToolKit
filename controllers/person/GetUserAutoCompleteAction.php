<?php
class GetUserAutoCompleteAction extends CAction
{
    public function run()
    {
        $query = array( '$or' => array( array("email" => new MongoRegex("/".$_POST['search']."/i")),
                                        array( "name" => new MongoRegex("/".$_POST['search']."/i"))));
        $allCitoyens = PHDB::find ( PHType::TYPE_CITOYEN , $query);
        $allOrganization = PHDB::find( Organization::COLLECTION, $query, array("_id", "name", "type", "address", "email", "links", "imagePath"));
        $all = array(
          "citoyens" => $allCitoyens,
          "organizations" => $allOrganization,
        );       
        Rest::json( $all );
        Yii::app()->end(); 
    }
}