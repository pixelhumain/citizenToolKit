<?php
class SearchMembersAutoCompleteAction extends CAction
{
    public function run()
    {
        $query = array( '$or' => array( array("email" => new MongoRegex("/".$_POST['search']."/i")),
                                        array( "name" => new MongoRegex("/".$_POST['search']."/i"))));
        $allCitoyens = PHDB::findAndSort( Person::COLLECTION , $query, array("name" => 1), 6);
        $limitOrganization = 12 - count($allCitoyens);
        $allOrganization = PHDB::findAndSort( Organization::COLLECTION, $query, array("name" => 1), $limitOrganization, array("_id", "name", "type", "address", "email", "links", "imagePath"));
        
        foreach ($allCitoyens as $key => $value) {
  			$logo = Document::getLastImageByKey($key, Person::COLLECTION, Document::IMG_LOGO);
  			if($logo !="")
					$value["logo"]= $logo;
					$allCitoyens[$key] = $value;
  		}

  		foreach ($allOrganization as $key => $value) {
  			$logo = Document::getLastImageByKey($key, Organization::COLLECTION, Document::IMG_LOGO);
  			if($logo !="")
					$value["logo"]= $logo;
					$allOrganization[$key] = $value;
  		}

        $all = array(
          "citoyens" => $allCitoyens,
          "organizations" => $allOrganization,
        );       
        Rest::json( $all );
        Yii::app()->end(); 
    }
}