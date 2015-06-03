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

			$listEvent = Event::getListCurrentEventsByOrganizationId($id, 3);
			
			//Add information for events
        	foreach ($listEvent as $key => $value) {
	        	$profil = Document::getLastImageByKey($key, Event::COLLECTION, Document::IMG_PROFIL);
	        	if($profil!="")
	        		$value['imageUrl']=$profil;
	        	
	        	//TODO SBAR : add localization display ?
	        	//@See http://php.net/manual/en/function.strftime.php
	        	$value["startDate"] = date('Y-m-d h:i:s', $value["startDate"]->sec);
				$value["endDate"] = date('Y-m-d h:i:s', $value["endDate"]->sec);
				$events[$key] = $value;
        	}

			$params["organizationId"] = $id;
		}

		else if($type == Person::COLLECTION){
			$listEvent = Event::getListCurrentEventsByPeopleId($id, 3);
			
			//Add information for events
        	foreach ($listEvent as $key => $value) {
	        	$profil = Document::getLastImageByKey($key, Event::COLLECTION, Document::IMG_PROFIL);
	        	if($profil!="")
	        		$value['imageUrl']=$profil;
	        	
	        	//TODO SBAR : add localization display ?
	        	//@See http://php.net/manual/en/function.strftime.php
	        	$value["startDate"] = date('Y-m-d h:i:s', $value["startDate"]->sec);
				$value["endDate"] = date('Y-m-d h:i:s', $value["endDate"]->sec);
				$events[$key] = $value;
        	}

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