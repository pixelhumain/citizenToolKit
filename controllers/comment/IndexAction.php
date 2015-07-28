<?php
class IndexAction extends CAction
{
    public function run($type, $id)
    {
        $controller=$this->getController();

        $params = array();

        $res = Comment::buildCommentsTree($id, $type, Yii::app()->session["userId"]);
        $params['comments'] = $res["comments"];
        $params['options'] = $res["options"];
        $params['canComment'] = $res["canComment"];
        $params["contextType"] = "$type";

        if($type == Event::COLLECTION) {
            $params["context"] = Event::getById($id);
        } else if($type == Project::COLLECTION) {
            $params["context"] = Project::getById($id);
        } else if($type == Organization::COLLECTION) {
            $params["context"] = Organization::getById($id);
        } else if($type == Person::COLLECTION) {
            $params["context"] = Person::getById($id);
        } else if($type == News::COLLECTION) {
            $params["context"] = News::getById($id);
        } else if($type == Survey::COLLECTION) {
            $params["context"] = Survey::getById($id);
        } else if($type == ActionRoom::COLLECTION) {
            $params["context"] = ActionRoom::getById($id);
        } else {
        	throw new CTKException("Error : the type is unknown ".$type);
        	
        }
        
		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("index" , $params, true);
	    else
  			$controller->render( "index" , $params );
    }

 
}