<?php
/**
* Update an information field for a element
*/
class UpdateBlockAction extends CAction
{
    public function run($type)
    {
        $controller=$this->getController();
        if(!empty($_POST["block"])) {
			try {
				$res = Element::updateBlock($_POST);
				return Rest::json($res);
			} catch (CTKException $e) {
				return Rest::json(array("result"=>false, "msg"=>$e->getMessage(), "data"=>$_POST));
			}
		}
		return Rest::json(array("result"=>false,"msg"=>Yii::t("common","Invalid request")));
        
    }
}