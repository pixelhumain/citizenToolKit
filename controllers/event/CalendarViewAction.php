<?php
	class CalendarViewAction extends CAction{

		public function run($id=null, $type=null, $pod=null){
		  	$controller=$this->getController();

		  	$params = array(
		  		"events" => array()
		  	);
		  	if( @$id )
		  	{
		  		if( @$type )
		  		{
		  			if(strcmp($type, "person")==0){
		  				$params['events'] = Event::getListCurrentEventsByPeopleId($id);
		  				$params['person'] = Person::getById($id);
		  			}
		  			else if (strcmp($type, "organization") == 0){
		  				$params['events'] = Event::getListCurrentEventsByOrganizationId($id);
		  				$params['organization'] = Organization::getById($id);
		  			}
		  		}else{
		  			//means we are showing details of an events
		  			$event = Event::getById($id);
		  			$params['event'] = $event;
		  			$params['events'] = Event::getListEventsById($id);
		  			if( @$event['startDate'] ){
		  				//focus on the start date of the event 
		  				$params['defaultDate'] = $event["startDate"];
		  				//if last onl y one day then apply day view 
		  				$params['defaultView'] = "agendaDay";
		  				if( @$event['endDate'] )
		  				{
			  				$datetime1 = new DateTime($event['startDate']);
							$datetime2 = new DateTime($event['endDate']);
							$diff = $datetime1->diff($datetime2)->days;
							if( $diff > 1 ){
								if($diff < 7) 
									$params['defaultView'] = "agendaWeek";
								else 
									$params['defaultView'] = "month";
							}
		  				}
		  			}
		  		}
		  	}
		  	$params["edit"] = Authorisation::canEditItem(@Yii::app()->session["userId"], $type, $id);
		  	$params["openEdition"] = Authorisation::isOpenEdition($id, $type, @$event["preferences"]);
		  	$tpl = ( $pod ) ? "../pod/calendarPod" : "calendarView";
		  	if(Yii::app()->request->isAjaxRequest)
	            echo $controller->renderPartial($tpl, $params);
	        else if(@$_GET["format"] && $_GET["format"] == "json")
	        	echo Rest::json($params);
	        else 
		  		$controller->render( $tpl , $params);
		}
	}
?>