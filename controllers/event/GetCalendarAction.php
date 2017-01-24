<?php
class GetCalendarAction extends CAction
{
    public function run()
    {
        $events = array();
        $organization = Organization::getPublicData($id);
        if(isset($organization["links"]["events"])){
            foreach ($organization["links"]["events"] as $key => $value) {
                $event = Event::getPublicData($key);
                $events[$key] = $event;
            }
        }
        foreach ($organization["links"]["members"] as $newId => $e) 
        {
            if( $e["type"] == Organization::COLLECTION ){
                $member = Organization::getPublicData($newId);
            } else {
                $member = Person::getPublicData($newId);
            }

            if(isset($member["links"]["events"])){
                foreach ($member["links"]["events"] as $key => $value) {
                    $event = Event::getPublicData($key);
                    $events[$key] = $event; 
                }
                
            }
        }
        Rest::json($events);
    }
}