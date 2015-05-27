<?php
class SliderAgendaAction extends CAction
{
    public function run($id, $type)
    {
        $controller=$this->getController();
        $params= array();
        if($type == Organization::COLLECTION){
			$events = Organization::listEventsPublicAgenda($id);
			$params["organizationId"] = $id;
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
		  	$params["itemId"] = $id;
		}

		$params["eventTest"] = Event::getLastEvents($id, $type, 3);
		foreach ($events as $key => $value) {
			$limit = array(Document::IMG_PROFIL => 1, Document::IMG_MEDIA => 5);
			$imageUrlTab = Document::getListDocumentsURLByContentKey($key, "event", Document::DOC_TYPE_IMAGE, $limit);
			if(isset($imageUrlTab[Document::IMG_PROFIL])){
				$imageUrl = $imageUrlTab[Document::IMG_PROFIL][0];
				$events[$key]["imageUrl"] = $imageUrl;
			}
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