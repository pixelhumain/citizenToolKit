<?php
class SliderAgendaAction extends CAction
{
    public function run($id, $type)
    {
        $controller=$this->getController();
        if($type == Organization::COLLECTION){
				$events = Organization::listEventsPublicAgenda($id);
			}
			else if($type == Person::COLLECTION){
				$events = Authorisation::listEventsIamAdminOf($id);
			  	$eventsAttending = Event::listEventAttending($id);
			  	foreach ($eventsAttending as $key => $value) {
			  		$eventId = (string)$value["_id"];
			  		if(!isset($events[$eventId])){
			  			$events[$eventId] = $value;
			  		}
			  	}
			}
			$params= array();
			$params["events"] = $events;
			$params["itemId"] = $id;
			if(Yii::app()->request->isAjaxRequest)
		        echo $controller->renderPartial("sliderAgenda", $params,true);
		    else
		        $controller->render("sliderAgenda",$params);
    }
}