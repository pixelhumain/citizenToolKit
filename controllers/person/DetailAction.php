<?php

class DetailAction extends CAction
{
	/**
	* Dashboard Organization
	*/
    public function run($id) { 
    	$controller=$this->getController();
		
        //get The person Id
        if (empty($id)) {
            if (empty(Yii::app()->session["userId"])) {
                $controller->redirect(Yii::app()->homeUrl);
            } else {
                $id = Yii::app()->session["userId"];
            }
        }

        //We update the points of the user 
        Gamification::updateUser($id);
        
        $me = ( $id == Yii::app()->session["userId"] ) ? true : false;
        $person = Person::getPublicData($id);
        $params = array( "person" => $person, "me" => $me);

        //TODO SBAR : L'image de profil est maintenant stocké dans l'entité. L'appel peut être supprimé
        $limit = array(Document::IMG_PROFIL => 1);
        $images = Document::getImagesByKey($id, Person::COLLECTION, $limit);
        $params['images'] = $images;

        $controller->title = ((isset($person["name"])) ? $person["name"] : "")."'s Dashboard";
        $controller->subTitle = (isset($person["description"])) ? $person["description"] : "";
        $controller->pageTitle = ucfirst($controller->module->id)." - Informations publiques de ".$controller->title;

        //Get Projects
        $projects = array();
        if(isset($person["links"]["projects"])){
            foreach ($person["links"]["projects"] as $key => $value) {
                $project = Project::getPublicData($key);
                if (! empty($project)) {
                    array_push($projects, $project);
                }
            }
        }


        //Get the Events
        $events = Authorisation::listEventsIamAdminOf($id);
        $eventsAttending = Event::listEventAttending($id);
        foreach ($eventsAttending as $key => $value) {
            $eventId = (string)$value["_id"];
            if(!isset($events[$eventId])){
                $events[$eventId] = $value;
            }
        }
        
        //TODO - SBAR : Pour le dashboard person, affiche t-on les événements des associations dont je suis memebre ?
        //Get the organization where i am member of;
        $organizations = array();
        if( isset($person["links"]) && isset($person["links"]["memberOf"])) {

            foreach ($person["links"]["memberOf"] as $key => $member) {
                $organization;
                if( $member['type'] == Organization::COLLECTION )
                {
                    $organization = Organization::getPublicData( $key );
                    if (!empty($organization) && !isset($organization["disabled"])) {
                        array_push($organizations, $organization);
                    }
                }

                if(isset($organization["links"]["events"])){
                    foreach ($organization["links"]["events"] as $keyEv => $valueEv) {
                        $event = Event::getPublicData($keyEv);
                        $events[$keyEv] = $event;
                        //array_push($events, $event);
                    }
                }
            }
        }
        $people = array();
        if( isset($person["links"]) && isset($person["links"]["follows"])) {
            foreach ($person["links"]["follows"] as $key => $member) {
                $citoyen;
                if( $member['type'] == Person::COLLECTION )
                {
                    $citoyen = Person::getPublicData( $key );
                    if (!empty($citoyen)) {
                        array_push($people, $citoyen);
                    }
                }
            }
            uasort($people, array($this, 'comparePeople'));
        }

        $cleanEvents = array();
        foreach($events as $key => $event){
            array_push($cleanEvents, $event);
        }

        $params["countries"] = OpenData::getCountriesList();
        $params["listCodeOrga"] = Lists::get(array("organisationTypes"));
        $params["tags"] = Tags::getActiveTags();
        $params["preferences"] =  Preference::getPreferencesByTypeId($id, Person::COLLECTION);
        $params["organizations"] = $organizations;
        $params["projects"] = $projects;
        $params["events"] = $cleanEvents;
        $params["people"] = $people;
        
		$page = "detail";
		if(Yii::app()->request->isAjaxRequest) {
            echo $controller->renderPartial($page,$params,true);
        } else 
			$controller->render( $page , $params );
    }

    private function comparePeople($person1, $person2) {
        return strcmp($person1["name"], $person2["name"]);
    }
}
