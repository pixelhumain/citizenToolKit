<?php
class CheckEventsOpenAgendaInDBAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
        $params = array();

        $state = Event::getStateEventsOpenAgenda($_POST["OpenAgendaID"], $_POST["modified"], $_POST["location"]);

        $params['state'] = $state;

    	return Rest::json($params);   
    }
}

?>