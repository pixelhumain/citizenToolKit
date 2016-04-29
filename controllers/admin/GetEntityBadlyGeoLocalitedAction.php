<?php
class GetEntityBadlyGeoLocalitedAction extends CAction
{
    public function run(){

        $controller = $this->getController();
        $params = SIG::getEntityBadlyGeoLocalited();
        /*$organizations = Organization::getOrganizationsBadlyGeoLocalited();
        //$state = Event::getStateEventsOpenAgenda($_POST["OpenAgendaID"], $_POST["modified"], $_POST["location"]);
        //$params['state'] = $state;
		$params["person"] = $citoyens;
        $params["organization"] = $organizations;*/

    	return Rest::json($params);   
    }
}

?>