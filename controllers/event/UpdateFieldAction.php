<?php
	class UpdateFieldAction extends CAction {

		public function run() {
			$controller=$this->getController();
			//insert a new event
			if (!empty($_POST["pk"]) && ! empty($_POST["name"]) && ! empty($_POST["value"])) {
				$eventId = $_POST["pk"];
				$eventFieldName = $_POST["name"];
				$eventFieldValue = $_POST["value"];
				try {
					Event::updateEventField($eventId, $eventFieldName, $eventFieldValue, Yii::app()->session["userId"]);
				} catch (CTKException $e) {
					return Rest::json(array("result"=>false, "msg"=>$e->getMessage(), $eventFieldName=>$eventFieldValue));
				}
			}else{
				return Rest::json(array("result"=>false,"msg"=>Yii::t("event","Requête incorrecte")));
			}
			
			return Rest::json(array("result"=>true, "msg"=>Yii::t("event","Event well updated"), $eventFieldName=>$eventFieldValue));
		}
	}
?>