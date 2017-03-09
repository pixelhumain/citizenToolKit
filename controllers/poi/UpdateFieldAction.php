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

		if ($id != "" && Yii::app()->session['userId'] ) {
			$el = PHDB::findOne($_POST["type"], array( "_id" => new MongoId($id) ) );
			$auth = false;
			if( $_POST["type"] == ActionRoom::COLLECTION )
				$auth = Authorisation::canParticipate( Yii::app()->session['userId'], $el["parentType"], $el["parentId"] );

			if ( $auth && ! empty($_POST["name"]) ) 
			{
				$name = @$_POST["name"];
				$value = @$_POST["value"];
				try {
					Element::updateField($_POST["type"], $id, $name, $value );
									
					$res = array("result"=>true, "msg"=>Yii::t("common", "Element has been updated"), $name=>$value);
				} catch (CTKException $e) {
					$res = array("result"=>false, "msg"=>$e->getMessage(), $name=>$value);
				}
			}
		} 
		Rest::json($res);
    }
}