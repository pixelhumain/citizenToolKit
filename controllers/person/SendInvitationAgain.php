<?php
class SendInvitationAgainAction extends CTKAction
{
    public function run()
    {
        $controller=$this->getController();
		$msg = $_POST["text"];
		$person=Person::getById($_POST["id"]);
		$invitorName=Yii::app()->session["name"];
		$invitorId=Yii::app()->session["userId"];
		$subject=Yii::app()->session["userId"]."vous relance pour finir votre inscription sur communecter";
   		Mail::invitePerson($res["person"], $msg);
   		$res=array("result"=> true)
		Rest::json($res);
        if(Yii::app()->request->isAjaxRequest)
    		$controller->renderPartial("invite", $params, $invitorName,"#person.detail.id.".$invitorId,$subject);
    }
}

