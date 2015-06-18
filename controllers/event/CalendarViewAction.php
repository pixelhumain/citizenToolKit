<?php
	class CalendarViewAction extends CAction{

		public function run($id=null, $type=null){
		  	$controller=$this->getController();
		  	$params = array();
		  	$events = Event::getWhere($params);
		  	if(isset($id) && $id!=null){
		  		if(isset($type) && $type!=null){
		  			if(strcmp($type, "person")==0){
		  				$events = Event::getListCurrentEventsByPeopleId($id);

		  			}else if (strcmp($type, "organization") == 0){
		  				$events = Event::getListCurrentEventsByOrganizationId($id);
		  			}
		  		}
		  	}
		  	$controller->render("calendarView", array("events" => $events));
		}
	}
?>