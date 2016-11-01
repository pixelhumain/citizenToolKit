<?php

class DetailAction extends CAction
{
	/**
	* Dashboard Organization
	*/
    public function run($id) { 
    	$controller=$this->getController();
		
        //get The person Id
        if (empty($id)) {
            if (empty(Yii::app()->session["userId"])) {
                $controller->redirect(Yii::app()->homeUrl);
            } else {
                $id = Yii::app()->session["userId"];
            }
        }

        //We update the points of the user 
        Gamification::updateUser($id);
        
        $me = ( $id == Yii::app()->session["userId"] ) ? true : false;
        $person = Person::getPublicData($id);
        $params = array( "person" => $person, "me" => $me);

        //TODO SBAR : L'image de profil est maintenant stocké dans l'entité. L'appel peut être supprimé
        $limit = array(Document::IMG_PROFIL => 1);
        $images = Document::getImagesByKey($id, Person::COLLECTION, $limit);
        $params['images'] = $images;

        $params["countries"] = OpenData::getCountriesList();
        $params["listCodeOrga"] = Lists::get(array("organisationTypes"));
        $params["tags"] = Tags::getActiveTags();
        //$params["preferences"] =  Preference::getPreferencesByTypeId($id, Person::COLLECTION);
		$page = "detail";
		if(Yii::app()->request->isAjaxRequest) {
            echo $controller->renderPartial($page,$params,true);
        } else 
			$controller->render( $page , $params );
    }

    private function comparePeople($person1, $person2) {
        return strcmp($person1["name"], $person2["name"]);
    }
}
