<?php
/**
* Update an information field for a person
*/
class UpdateAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        if (!empty($_POST["id"]) && ! empty($_POST["params"])) {
			try {
				$res=Comment::update($_POST["id"], $_POST["params"]);
			} catch (CTKException $e) {
				return Rest::json(array("result"=>false, "msg"=>$e->getMessage()));
			}
		} else {
          return Rest::json(array("result"=>false,"msg"=>Yii::t("common","Invalid request")));
        }
        return Rest::json($res);
    }
}