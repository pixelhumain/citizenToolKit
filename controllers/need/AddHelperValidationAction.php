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
	        $need = Need::getById($needId);
	        if($booleanState==0){
				$helperId = Yii::app()->session["userId"];
				if(isset($need) && isset($need["links"]["helpers"][$helperId])){
					return Rest::json(array("result"=>false, "msg"=> Yii::t("need","You are already helpers for this need !",null,Yii::app()->controller->module->id)));
				}
				else
					$msg = Yii::t("need","Succesfully add !! Wait for validation",null,Yii::app()->controller->module->id);
			}
			else{
				$helperId = $helperId;
				$msg = Yii::t("need","Congrats, help is succesfully validated",null,Yii::app()->controller->module->id);
			}
			$helperType = "citoyen";
			$helper = Person::getById($helperId);
			if($helper){
				try {
					Link::addHelper($needId, $helperId, $helperType, $booleanState);
				} catch (CTKException $e) {
					return Rest::json(array("result"=>false, "msg"=>$e->getMessage()));
				}
			}
			else{
				return Rest::json(array("result"=>false,"msg"=>Yii::t("need","You are not a person",null,Yii::app()->controller->module->id)));
			}
		} else {
          return Rest::json(array("result"=>false,"msg"=>Yii::t("common","Uncorrect request")));
        }
        return Rest::json(array("result"=>true, "msg"=>$msg, "helper"=>$helper));
    }
}