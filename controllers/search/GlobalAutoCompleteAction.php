<?php
class GlobalAutoCompleteAction extends CAction
{
    public function run($filter = null)
    {
        $search = trim(urldecode($_POST['name']));
        
        $query = array( "name" => new MongoRegex("/".$search."/i"));
  		
        $res = array();


        if(strcmp($filter, Person::COLLECTION) != 0){

	  		$allCitoyen = PHDB::find ( Person::COLLECTION ,$query ,array("name", "address"));

	  		foreach ($allCitoyen as $key => $value) {
	  			$person = Person::getSimpleUserById($key);
				$allCitoyen[$key] = $person;
	  		}

	  		$res["citoyen"] = $allCitoyen;

	  	}

	  	if(strcmp($filter, Organization::COLLECTION) != 0){

	  		$allOrganizations = PHDB::find ( Organization::COLLECTION ,$query ,array("name", "type", "address"));
	  		foreach ($allOrganizations as $key => $value) {
	  			$orga = Organization::getSimpleOrganizationById($key);
				$allOrganizations[$key] = $orga;
	  		}

	  		$res["organization"] = $allOrganizations;
	  	}

	  	if(strcmp($filter, Event::COLLECTION) != 0){
	  		$allEvents = PHDB::find(PHType::TYPE_EVENTS, $query, array("name", "address"));
	  		foreach ($allEvents as $key => $value) {
	  			$event = Event::getSimpleEventById($key);
				$allEvents[$key] = $event;
	  		}
	  		
	 
	  		$res["event"] = $allEvents;
	  	}

	  	if(strcmp($filter, Project::COLLECTION) != 0){
	  		$allProject = PHDB::find(Project::COLLECTION, $query, array("name", "address"));
	  		foreach ($allProject as $key => $value) {
	  			$project = Project::getSimpleProjectById($key);
				$allProject[$key] = $project;
	  		}
	  		
	 
	  		$res["project"] = $allProject;
	  	}

  		Rest::json($res);
		Yii::app()->end();
    }
}