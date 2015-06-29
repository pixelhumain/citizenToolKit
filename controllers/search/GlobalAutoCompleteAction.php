<?php
class GlobalAutoCompleteAction extends CAction
{
    public function run($filter = null)
    {
        $query = array( "name" => new MongoRegex("/".$_POST['name']."/i"));
  		
        $res = array();


        if(strcmp($filter, Person::COLLECTION) != 0){

	  		$allCitoyen = PHDB::find ( Person::COLLECTION ,$query ,array("name"));

	  		foreach ($allCitoyen as $key => $value) {
	  			$profil = Document::getLastImageByKey($key, Person::COLLECTION, Document::IMG_PROFIL);
	  			if($profil !="")
						$value["imagePath"]= $profil;
						$allCitoyen[$key] = $value;
	  		}

	  		$res["citoyen"] = $allCitoyen;

	  	}

	  	if(strcmp($filter, Organization::COLLECTION) != 0){

	  		$allOrganizations = PHDB::find ( Organization::COLLECTION ,$query ,array("name", "type"));
	  		foreach ($allOrganizations as $key => $value) {
	  			$profil = Document::getLastImageByKey($key, Organization::COLLECTION, Document::IMG_PROFIL);
	  			if($profil !="")
						$value["imagePath"]= $profil;
						$allOrganizations[$key] = $value;
	  		}

	  		$res["organization"] = $allOrganizations;
	  	}

	  	if(strcmp($filter, Event::COLLECTION) != 0){
	  		$allEvents = PHDB::find(PHType::TYPE_EVENTS, $query, array("name"));
	  		foreach ($allEvents as $key => $value) {
	  			$profil = Document::getLastImageByKey($key, Event::COLLECTION, Document::IMG_PROFIL);
	  			if($profil !="")
						$value["imagePath"]= $profil;
						$allEvents[$key] = $value;
	  		}
	  		
	 
	  		$res["event"] = $allEvents;
	  	}

	  	if(strcmp($filter, Project::COLLECTION) != 0){
	  		$allProject = PHDB::find(Project::COLLECTION, $query, array("name"));
	  		foreach ($allProject as $key => $value) {
	  			$profil = Document::getLastImageByKey($key, Project::COLLECTION, Document::IMG_PROFIL);
	  			if($profil !="")
						$value["imagePath"]= $profil;
						$allProject[$key] = $value;
	  		}
	  		
	 
	  		$res["project"] = $allProject;
	  	}

  		Rest::json($res);
		Yii::app()->end();
    }
}