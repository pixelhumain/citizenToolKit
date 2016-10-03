<?php
class SendInvitationAgainAction extends CTKAction
{
    public function run()
    {
        $controller=$this->getController();
		$msg = $_POST["text"];
		$person=Person::getById($_POST["id"]);
		if(@isset($person["lastInvitationDate"]) && date('d', $person["lastInvitationDate"]) == date('d', time()))		{
			$res=array("result"=> false, "msg"=>Yii::t("common","You already invited")." ".$person["name"]." ".Yii::t("common","today"));
		}else{
			$invitorName=Yii::app()->session["name"];
			$invitorId=Yii::app()->session["userId"];
			$subject=Yii::app()->session["name"]." vous relance pour finir votre inscription sur communecter";
			Element::updateTimeElementByLabel(Person::COLLECTION, $person["_id"],"lastInvitationDate");
	   		Mail::invitePerson($person, $msg, $invitorName,"#person.detail.id.".$invitorId,$subject);
	   		$res=array("result"=> true);
	   	}	
	   	Rest::json($res);
	   	
        //if(Yii::app()->request->isAjaxRequest)
    	//	$controller->renderPartial("invite", $params);
    }
}

