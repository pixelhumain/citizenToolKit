<?php
class ProjectSVAction extends CAction
{
    public function run(){
    	$controller=$this->getController();
    	$params = array();
    	if(@$_GET["type"]){
	    	if( $_GET["type"] == Organization::COLLECTION ){
	    		$params["parentId"] = $_GET["id"];
	    		$params["parentType"] = Organization::COLLECTION;
			}
	   	}
		else {
			$params["parentId"] = Yii::app() -> session['userId'];
	    	$params["parentType"] = Person::COLLECTION;
		}
    	$params["countries"] = OpenData::getCountriesList();
    	$params["tags"] = json_encode(Tags::getActiveTags());
        if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial("projectSV", $params, true);
    }
}