<?php

class InviteContactAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();


        //get The person Id
        if (empty($id)) {
            if (empty(Yii::app()->session["userId"])) {
                $controller->redirect(Yii::app()->homeUrl);
            } else {
                $id = Yii::app()->session["userId"];
            }
        }

        $person = Person::getPublicData($id);
        $limit = array(Document::IMG_PROFIL => 1);
        $images = Document::getImagesByKey($id, Person::COLLECTION, $limit);

    	$params = array( "person" => $person);
    	$params["imagesD"] = $images ;

        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("inviteContact",$params,true);
        else 
            $controller->render("inviteContact",$params);
    }
}

?>