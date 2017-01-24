<?php
class SliderAgendaAction extends CAction
{
    //Get the three last events that are not passed on time
    public function run($id, $type)
    {
        $controller=$this->getController();
        $params= array();
        $events=array();

        if($type == Organization::COLLECTION) {
			$events = Event::getListCurrentEventsByOrganizationId($id, 3);
			$params["organizationId"] = $id;
		}

		else if($type == Person::COLLECTION){
			$events = Event::getListCurrentEventsByPeopleId($id, 3);
		  	$params["itemId"] = $id;
		}


		$params["eventsAgenda"] = $events;
		if(isset(Yii::app()->session["userId"]))
			$params["canEdit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
		
		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("sliderAgenda", $params,true);
	    else
	        $controller->render("sliderAgenda",$params);
    }
}