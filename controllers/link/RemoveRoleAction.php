<?php
class RemoveRoleAction extends CAction
{
    public function run()
    {
        $res = array( "result" => false , "msg" => Yii::t("common","Something went wrong!") );
		try {
			 $res = Link::removeRole($_POST["contextId"], $_POST["contextType"], $_POST["childId"], $_POST["childType"], @$_POST["roles"], Yii::app()->session['userId'], $_POST["connectType"]); 
		} catch (CTKException $e) {
			$res = array( "result" => false , "msg" => $e->getMessage() );
		}

		return Rest::json($res);
    }
}