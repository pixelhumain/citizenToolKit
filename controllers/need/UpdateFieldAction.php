<?php
/**
* Update an information field for a person
*/
class UpdateFieldAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        if (!empty($_POST["pk"]) && ! empty($_POST["name"]) && ! empty($_POST["value"])) {
			$needId = $_POST["pk"];
			$needFieldName = $_POST["name"];
			$needFieldValue = $_POST["value"];
			try {
				Need::updateNeedField($needId, $needFieldName, $needFieldValue, Yii::app()->session["userId"] );
			} catch (CTKException $e) {
				return Rest::json(array("result"=>false, "msg"=>$e->getMessage(), $needFieldName=>$needFieldValue));
			}
		} else {
          return Rest::json(array("result"=>false,"msg"=>Yii::t("need","Requête incorrecte"),"pk"=>$_POST["pk"]));
        }
        
        return Rest::json(array("result"=>true, "msg"=>Yii::t("need","Need well updated"), $needFieldName=>$needFieldValue));
    }
}