<?php
class GetMemberAutocompleteAction extends CAction
{
    public function run()
    {
        $query = array( "name" => new MongoRegex("/".$_POST['name']."/i"));
  		$allCitoyen = PHDB::find ( PHType::TYPE_CITOYEN ,$query ,array("name"));
  		$allOrganizations = PHDB::find ( Organization::COLLECTION ,$query ,array("name", "type"));
  		$allEvents = PHDB::find(PHType::TYPE_EVENTS, $query, array("name"));
  		$res= array("citoyen" => $allCitoyen,
  					"organization" => $allOrganizations,
  					"event" => $allEvents,
  					);

  		Rest::json($res);
		Yii::app()->end();
    }
}