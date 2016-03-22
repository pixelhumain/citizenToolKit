<?php
class ImportEventsOpenAgendaInDBAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
        $params = array();
        if(!empty($_POST["jsonEventsAdd"])){
        	foreach (json_decode($_POST["jsonEventsAdd"], true) as $key => $eventOpenAgenda) {
        		try{
                    $event = Event::createEventsFromOpenAgenda($eventOpenAgenda);
                    $params["result"][] = Event::saveEventFromOpenAgenda($event);
                }
                catch (CTKException $e){
                    $params["error"][] = $e->getMessage();
                }

        	}
        }

        if(!empty($_POST["jsonEventsUpdate"])){
            foreach (json_decode($_POST["jsonEventsUpdate"], true) as $key => $eventOpenAgenda) {
                try{
                    $event = Event::getEventsOpenAgenda($eventOpenAgenda["uid"]);
                    $eventOpenAgenda = Event::createEventsFromOpenAgenda($eventOpenAgenda);
                    foreach ($event as $key => $value) {
                        $params["result"][] = Event::updateEvent($value["_id"], $event, "5694ea2a94ef47ad1c8b456d");
                    }
                    
                }
                catch (CTKException $e){
                    $params["error"][] = $e->getMessage();
                }
                
            }
        }

        /*if(!empty($_POST["jsonEventsDelete"])){
            foreach (json_decode($_POST["jsonEventsDelete"], true) as $key => $eventOpenAgenda) {
                try{
                    $event = Event::getEventsOpenAgenda($eventOpenAgenda["uid"]);
                    foreach ($event as $key => $value) {
                        var_dump($value["_id"]);
                        $params["result"][] = Event::delete($value["_id"], "5694ea2a94ef47ad1c8b456d");
                    }
                }
                catch (CTKException $e){
                    $params["error"][] = $e->getMessage();
                }
                
            }
        }*/

        //var_dump($params) ;
    
    	return Rest::json($params);   
    }
}

?>