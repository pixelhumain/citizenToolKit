<?php
class IndexAction extends CAction
{
    public function run($contexttype, $contextid)
    {
        $controller=$this->getController();

        $params = array();

        $comments = Comment::buildCommentsTree($contextid, $contexttype);
        $params['comments'] = $comments;

        $params["contextType"] = "$contexttype";

        if($contexttype == Event::COLLECTION) {
            $params["context"] = Event::getById($contextid);
        } else if($contexttype == Project::COLLECTION) {
            $params["context"] = Project::getById($contextid);
        } else if($contexttype == Organization::COLLECTION) {
            $params["context"] = Organization::getById($contextid);
        } else if($contexttype == Person::COLLECTION) {
            $params["context"] = Person::getById($contextid);
        } else if($contexttype == News::COLLECTION) {
            $params["context"] = News::getById($contextid);
        }
        
		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("index" , $params, true);
	    else
  			$controller->render( "index" , $params );
    }

 
}