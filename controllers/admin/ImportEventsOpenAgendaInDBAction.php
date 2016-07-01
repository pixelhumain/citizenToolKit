<?php
class ImportEventsOpenAgendaInDBAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
        $params = array();
        if(!empty($_POST["jsonEventsAdd"])){
            $bindMap = TranslateOpenAgenda::$dataBinding_event;
            $data = Translate::convert(json_decode($_POST["jsonEventsAdd"], true), $bindMap );
        	foreach ($data  as $key => $eventOpenAgenda) {
        		try{
                    //$event = $eventOpenAgenda ;

                    //$event = Event::createEventsFromOpenAgenda($eventOpenAgenda);
                    //$event = Event::createEventsFromOpenAgenda($eventOpenAgenda);
                    //$resEvent = Event::saveEventFromOpenAgenda($event, $controller->moduleId) ;
                    $resEvent = Event::saveEvent($eventOpenAgenda, true, null);
                    $eventGood["name"] = $resEvent["event"]["name"];
                    $eventGood["msg"] = $resEvent["msg"];
                    $params["result"][] = $eventGood;
                    //$params["result"][] = $event;
                    
                }
                catch (CTKException $e){
                    $eventError["name"] = $eventOpenAgenda["name"];
                    $eventError["msg"] = $e->getMessage();
                    $params["error"][] = $eventError;
                }

        	}
        }

        /*if(!empty($_POST["jsonEventsUpdate"])){
            $bindMap = TranslateOpenAgenda::$dataBinding_event;
            $data = Translate::convert(json_decode($_POST["jsonEventsAdd"], true), $bindMap );
            foreach ($data as $key => $eventOpenAgenda){
                try{
                    foreach ($eventOpenAgenda as $key => $value) {
                        Event::updateEvent($value["_id"], $event, Yii::app()->params['idOpenAgenda']);
                    }
                    $eventGood["name"] = $eventOpenAgenda["name"];
                    $eventGood["msg"] = "L'événement a été mis a jours";
                    $params["result"][] = $eventGood;                    
                }
                catch (CTKException $e){
                    $eventError["name"] = $eventOpenAgenda["name"];
                    $eventError["msg"] = $e->getMessage();
                    $params["error"][] = $eventError;
                }
                
            }
        }*/

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