<?php
/**
* Update an information field for a person
*/
class UpdateFieldsAction extends CAction
{
    public function run($type)
    {
        $controller=$this->getController();
        if (!empty($_POST["pk"]) && ! empty($_POST["name"]) && ! empty($_POST["value"])) {
			$elementId = $_POST["pk"];
			$elementFieldName = $_POST["name"];
			$elementFieldValue = $_POST["value"];
			try {
				if($type==Organization::COLLECTION)
					Organization::updateOrganizationField($elementId, $elementFieldName, $elementFieldValue, Yii::app()->session["userId"] );
				else if ($type==Project::COLLECTION)
					Project::updateProjectField($elementId, $elementFieldName, $elementFieldValue, Yii::app()->session["userId"] );
				else if ($type==Event::COLLECTION)
					Event::updateEventField($elementId, $elementFieldName, $elementFieldValue, Yii::app()->session["userId"]);
				else if ($type==Person::COLLECTION){
					$res = Person::updatePersonField($elementId, $elementFieldName, $elementFieldValue, Yii::app()->session["userId"] );
                    if( @$elementFieldValue == "bgCustom" && isset( $_POST["url"] ))
                        Person::updatePersonField($_POST["pk"], "bgUrl", $_POST["url"], Yii::app()->session["userId"] );

				}
				if(Import::isUncomplete($elementId, $type)){
					Import::checkWarning($elementId, $type, Yii::app()->session['userId'] );
				}

			} catch (CTKException $e) {
				return Rest::json(array("result"=>false, "msg"=>$e->getMessage(), $elementFieldName=>$elementFieldValue));
			}
		} else {
          return Rest::json(array("result"=>false,"msg"=>Yii::t("common","Invalid request")));
        }
        
        return Rest::json(array("result"=>true, "msg"=>Yii::t("project","Project well updated"), $elementFieldName=>$elementFieldValue));
    }
}