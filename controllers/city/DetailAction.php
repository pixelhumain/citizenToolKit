<?php
class DetailAction extends CAction
{
    public function run( $id, $type=null )
    {
        $controller = $this->getController();
        
        if(empty($type) || $type == City::COLLECTION ){
            $collection = City::COLLECTION ;
            $zone = City::getDetail(City::COLLECTION, $id);
        }else{
            $collection = Zone::COLLECTION ;
            $zone = Zone::getDetailById($id);
        }

        //Get Projects
        $whereElement = array( "address.codeInsee" => $zone["insee"] ) ;

        $countProject       = PHDB::count( Project::COLLECTION, $whereElement );
        $countEvent         = PHDB::count( Event::COLLECTION, $whereElement );
        $countOrganization  = PHDB::count( Organization::COLLECTION, $whereElement );
        $countPerson        = PHDB::count( Person::COLLECTION, $whereElement );
        /*$projectsBd = PHDB::find(Project::COLLECTION, $whereElement);
        $projects = array();
        foreach ($projectsBd as $key => $project) {
            $project = Project::getPublicData((string)$project["_id"]);
            $project["typeSig"] = PHType::TYPE_PROJECTS;
            array_push($projects, $project);
        }*/
        
        //Get the Events
        /*$eventsBd = PHDB::find(Event::COLLECTION, $whereElement);
        $events = array();
        foreach ($eventsBd as $key => $event) {
            $event = Event::getPublicData((string)$event["_id"]);
            $event["typeSig"] = PHType::TYPE_EVENTS;
            array_push($events, $event);
        }
        
        $organizationsBd = PHDB::find(Organization::COLLECTION, $whereElement);
        $organizations = array();
        foreach ($organizationsBd as $key => $orga) {
            $orga = Organization::getPublicData((string)$orga["_id"]);
            $orga["typeSig"] = PHType::TYPE_ORGANIZATIONS;
            array_push($organizations, $orga);
        }
        
        
        $peoples = array();
        $people = PHDB::find(Person::COLLECTION, $whereElement);
        foreach ($people as $key => $onePerson) {
            $citoyen = Person::getPublicData( $key );
            $citoyen["typeSig"] = PHType::TYPE_CITOYEN;
            array_push($peoples, $citoyen);
            
        }*/
        $tags = PHDB::findOne( PHType::TYPE_LISTS,array("name"=>"tags"), array('list'));
        $contextMap = array();
        /*if(isset($organizations))       $contextMap = array_merge($contextMap, $organizations);
        if(isset($peoples))             $contextMap = array_merge($contextMap, $peoples);
        if(isset($events))              $contextMap = array_merge($contextMap, $events);
        if(isset($projects))            $contextMap = array_merge($contextMap, $projects);*/
        $params["tags"] = $tags;
        /*$params["organizations"] = $organizations;
        $params["projects"] = $projects;
        $params["events"] = $events;
        $params["peoples"] = $peoples;*/
        $params["countOrganization"] = $countOrganization;
        $params["countProject"] = $countProject;
        $params["countEvent"] = $countEvent;
        $params["countPerson"] = $countPerson;
        $params["zone"] = $zone;
        $params["collection"] = $collection;
        $params["contextMap"] = $contextMap;
        
        $page = "detail";
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial($page,$params,true);
        else 
            $controller->render($page, $params );
    }
}