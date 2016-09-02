<?php
class EventSVAction extends CAction
{
    public function run($id=null, $type=null){

    	$controller = $this->getController();
    	$params = array();
    	if(@$_GET["contextType"]){
	    	$params["parentType"] = $_GET["contextType"];
	    	if( $_GET["contextType"] == "organization" ){
	    		$params["parentId"] = $_GET["contextId"];
	    		$params["parent"] = Organization::getSimpleOrganizationById($_GET["contextId"]);
			}
	    	else if( $_GET["contextType"] == "project" ){
	    		$params["parentId"] = $_GET["contextId"];
	    		$params["parent"] = Project::getSimpleProjectById($_GET["contextId"]);
			}
			else if( $_GET["contextType"] == "event" ){
	    		$params["parentId"] = $_GET["contextId"];
	    		$params["parent"] = Event::getSimpleEventById($_GET["contextId"]);
			}

		}
		else {
			$params["person"] = Person::getSimpleUserById(Yii::app() -> session['userId']);
		}

		$params["tags"] = json_encode(Tags::getActiveTags());
    	$lists = Lists::get(array("eventTypes"));
    	$params["lists"] = $lists;
    	if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial("eventSV", $params,true);
    }
}