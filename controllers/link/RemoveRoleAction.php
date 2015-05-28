<?php
class RemoveRoleAction extends CAction
{
    public function run($memberId, $memberType, $memberOfId, $memberOfType, $role)
    {
        $res = array( "result" => false , "msg" => Yii::t("common","Something went wrong!") );
		try {
			$res = Link::removeRole($memberOfId, Organization::COLLECTION, $memberId, $memberType, $role, Yii::app()->session['userId']);
		} catch (CTKException $e) {
			$res = array( "result" => false , "msg" => $e->getMessage() );
		}

		return Rest::json($res);
    }
}