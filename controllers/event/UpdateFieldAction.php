<?php
	class UpdateFieldAction extends CAction {

		public function run() {
			$controller=$this->getController();
			//insert a new event
			if (!empty($_POST["pk"])) {
				$eventId = $_POST["pk"];
				if (! empty($_POST["name"]) && ! empty($_POST["value"])) {
					$eventFieldName = $_POST["name"];
					$eventFieldValue = $_POST["value"];
					Event::updateEventField($eventId, $eventFieldName, $eventFieldValue, Yii::app()->session["userId"] );
				}else{
					return Rest::json(array("result"=>false,"msg"=>"Uncorrect request"));
				}
			}
			return Rest::json(array("result"=>true, "msg"=>"Votre evenement a ete modifié avec succes.", $eventFieldName=>$eventFieldValue));
		}
	}
?>