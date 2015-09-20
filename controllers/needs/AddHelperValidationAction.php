<?php
/**
* Update an information field for a person
*/
class AddHelperValidationAction extends CAction
{
    public function run($needId = null, $helperId= null, $booleanState = null)
    {
        $controller=$this->getController();
        if (!empty($needId)) {
	        if($booleanState==0){
				$helperId = Yii::app()->session["userId"];
				$msg = Yii::t("need","Succesfully add !! Wait for validation");
			}
			else{
				$helperId = $helperId;
				$msg = Yii::t("need","Congrats, help is succesfully validated");
			}
			$helperType = "citoyen";
			$helper=Person::getById($helperId);
			if($helper){
				try {
					Link::addHelper($needId, $helperId, $helperType, $booleanState);
				} catch (CTKException $e) {
					return Rest::json(array("result"=>false, "msg"=>$e->getMessage()));
				}
			}
			else{
				return Rest::json(array("result"=>false,"msg"=>Yii::t("need","You are not a person")));
			}
		} else {
          return Rest::json(array("result"=>false,"msg"=>Yii::t("need","Requête incorrecte")));
        }
        return Rest::json(array("result"=>true, "msg"=>$msg, "helper"=>$helper));
    }
}