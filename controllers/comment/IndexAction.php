<?php
class IndexAction extends CAction
{
    public function run($type, $id)
    {
        $controller=$this->getController();

        $params = array();

        $comments = Comment::buildCommentsTree($id, $type);
        $params['comments'] = $comments;

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
        }
        
		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("index" , $params, true);
	    else
  			$controller->render( "index" , $params );
    }

 
}