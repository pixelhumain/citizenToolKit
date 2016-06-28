<?php
class CheckEventsOpenAgendaInDBAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
        $params["Add"] = array();
        $params["Update"] = array();
        $params["Delete"] = array();
        $bindMap = TranslateOpenAgenda::$dataBinding_event;
        $data = Translate::convert($_POST["events"]["data"], $bindMap );
        var_dump($data);
        /*if(!empty($_POST["events"]["data"])){
            foreach ($_POST["events"]["data"] as $key => $value) {
        
                if(!empty($value["locations"]) && !empty($value["uid"])){
                    $state = Event::getStateEventsOpenAgenda($value["uid"], (empty($value["updatedAt"])?null:$value["updatedAt"]), $value["locations"]);
                    $params[$state][] = $value; 
                }
            }
        }*/
		return Rest::json($params);   
    }
}

?>