<?php
class GlobalAutoCompleteAction extends CAction
{
    public function run()
    {
        $query = array( "name" => new MongoRegex("/".$_POST['name']."/i"));
  		$allCitoyen = PHDB::find ( PHType::TYPE_CITOYEN ,$query ,array("name"));
  		$allOrganizations = PHDB::find ( Organization::COLLECTION ,$query ,array("name", "type"));
  		$allEvents = PHDB::find(PHType::TYPE_EVENTS, $query, array("name"));

  		//profil images

  		foreach ($allCitoyen as $key => $value) {
  			$profil = Document::getLastImageByKey($key, Person::COLLECTION, Document::IMG_PROFIL);
  			if($profil !="")
					$value["imagePath"]= $profil;
					$allCitoyen[$key] = $value;
  		}

  		foreach ($allOrganizations as $key => $value) {
  			$profil = Document::getLastImageByKey($key, Organization::COLLECTION, Document::IMG_PROFIL);
  			if($profil !="")
					$value["imagePath"]= $profil;
					$allOrganizations[$key] = $value;
  		}

  		foreach ($allEvents as $key => $value) {
  			$profil = Document::getLastImageByKey($key, Event::COLLECTION, Document::IMG_PROFIL);
  			if($profil !="")
					$value["imagePath"]= $profil;
					$allEvents[$key] = $value;
  		}
  		$res= array("citoyen" => $allCitoyen,
  					"organization" => $allOrganizations,
  					"event" => $allEvents,
  					);

  		Rest::json($res);
		Yii::app()->end();
    }
}