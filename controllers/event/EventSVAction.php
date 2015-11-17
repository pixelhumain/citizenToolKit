<?php
class EventSVAction extends CAction
{
    public function run($id=null, $type=null){

    	$controller = $this->getController();
    	$params = array();
    	if(@$_GET["contextType"]){
	    	if( $_GET["contextType"] == "organization" ){
	    		$params["organizationId"] = $_GET["contextId"];
	    		$params["organization"] = Organization::getPublicData($_GET["contextId"]);
			}
	    	else if( $_GET["contextType"] == "project" ){
	    		$params["projectId"] = $_GET["contextId"];
	    		$params["project"] = Project::getPublicData($_GET["contextId"]);
			}
		}
		else {
			$params["person"] = Person::getPublicData(Yii::app() -> session['userId']);
		}
    	$lists = Lists::get(array("eventTypes"));
    	$params["lists"] = $lists;
        if( isset($_GET["isNotSV"])) 
            $params["isNotSV"] = true;
    	if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial("eventSV", $params,true);
    }
}