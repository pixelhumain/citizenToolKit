<?php
	class CalendarViewAction extends CAction{

		public function run($id=null, $type=null, $pod=null){
		  	$controller=$this->getController();

		  	$person = Person::getPublicData( Yii::app()->session['userId'] );

		  	$controller->title = ((isset($person["name"])) ? $person["name"] : "")."'s Calendar";
	   		$controller->subTitle = "All events and important dates ";
	    	$controller->pageTitle = ucfirst($controller->module->id)." - ".$controller->title;

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
		  	$tpl = ( $pod ) ? "../pod/calendarPod" : "calendarView";
		  	if(Yii::app()->request->isAjaxRequest){
	            echo $controller->renderPartial($tpl, array("events" => $events));
	        }
	        else 
		  		$controller->render( $tpl , array("events" => $events));
		}
	}
?>