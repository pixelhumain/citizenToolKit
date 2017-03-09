<?php
class RemoveMemberAction extends CAction
{
    public function run($memberId, $memberType, $memberOfId, $memberOfType)
    {
        $res = array( "result" => false , "msg" => Yii::t("common","Something went wrong!" ));
		try {
			$res = ActivityStream::removeObject($memberId, $memberType, $memberOfId, $memberOfType, "join");
			$res = Link::removeMember($memberOfId, Organization::COLLECTION, $memberId, $memberType, Yii::app()->session['userId']);
			
		} catch (CTKException $e) {
			$res = array( "result" => false , "msg" => $e->getMessage() );
		}

		return Rest::json($res);
    }
}