<?php

class GetCityJsonDataAction extends CAction
{
    public function run( )
    {
        $insee = $_POST["insee"];
        error_log("recherche des données de la ville : ".$insee);
        $controller = $this->getController();

        $projectsBd = PHDB::find(Project::COLLECTION, array( "address.codeInsee" => $insee ) );
        $projects = array();
        foreach ($projectsBd as $key => $project) {
            $project = Project::getPublicData((string)$project["_id"]);
            array_push($projects, $project);
        }
        
        //Get the Events
        $eventsBd = PHDB::find(Event::COLLECTION, array( "address.codeInsee" => $insee ) );
        $events = array();
        foreach ($eventsBd as $key => $event) {
            //$event = Event::getPublicData((string)$event["_id"]);
            array_push($events, $event);
        }
        
        
        $organizationsBd = PHDB::find(Organization::COLLECTION, array( "address.codeInsee" => $insee ) );
        $organizations = array();
        foreach ($organizationsBd as $key => $orga) {
            $orga = Organization::getPublicData((string)$orga["_id"]);
            $profil = Document::getLastImageByKey((string)$orga["_id"], Organization::COLLECTION, Document::IMG_PROFIL);
                if($profil !="")
                    $orga["imagePath"]= $profil;
            array_push($organizations, $orga);
        }
        
        
        $allPeople = array();
        $people = PHDB::find(Person::COLLECTION, array( "address.codeInsee" => $insee ) );
        
       // if( isset($person["links"]) && isset($person["links"]["knows"])) {
            foreach ($people as $key => $onePerson) {
                $citoyen = Person::getPublicData( $key );
                $profil = Document::getLastImageByKey($key, Person::COLLECTION, Document::IMG_PROFIL);
                if($profil !="")
                   $citoyen["imagePath"]= $profil;
                array_push($allPeople, $citoyen);
                
            }
        //}

        $params["organizations"] = $organizations;
        $params["projects"] = $projects;
        $params["events"] = $events;
        $params["people"] = $allPeople;
        
        Rest::json( $params );
    }
}
