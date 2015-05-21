<?php
	class CalendarViewAction extends CAction{

		public function run(){
		  	$controller=$this->getController();
		  	$params = array();
		  	$events = Event::getWhere($params);
		  	$controller->render("calendarView", array("events" => $events));
		}
	}
?>