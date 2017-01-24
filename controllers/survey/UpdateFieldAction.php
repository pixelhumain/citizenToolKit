<?php

class UpdateFieldAction extends CAction
{
	/**
	* Update an information field for an organization
	*/
    public function run() {
		$id = "";
		$res = array("result"=>false, "msg"=>Yii::t("common", "Something went wrong!"));
		 if (!empty($_POST["id"])) {
			$id = $_POST["id"];
		}

		if ($id != "" ) {
			$el = PHDB::findOne($_POST["type"],$id);
			if (! empty($_POST["name"])) {
				$name = @$_POST["name"];
				$value = @$_POST["value"];
				try {
					//Element::updateField(Actio, $name, $value, Yii::app()->session["userId"] );
									
					$res = array("result"=>true, "msg"=>Yii::t("organization", "Element has been updated"), $name=>$value);
				} catch (CTKException $e) {
					$res = array("result"=>false, "msg"=>$e->getMessage(), $name=>$value);
				}
			}
		} 
		Rest::json($res);
    }
}