<?php
class IndexAction extends CAction
{
    public function run($type=null, $id=null)
    {
        $controller=$this->getController();

        $params = array();

        $discussList = Discuss::getByParentIdAndType($id, $type, true);
        foreach ($discussList as $discussId => $discuss) {
            $params["discuss"] = $discuss;
        }
        
        
        
        if($type == Event::COLLECTION) {
            $params["parentType"] = "Evénement";
            $name = @Event::getById($id)["name"];
        } else if($type == Project::COLLECTION) {
            $params["parentType"] = "Projet";
            $name = @Project::getById($id)["name"];
        } else if($type == Organization::COLLECTION) {
            $params["parentType"] = "Organisation";
            $name = @Organization::getById($id)["name"];
        } else if($type == Person::COLLECTION) {
            $params["parentType"] = "Personne";
            $name = @Person::getById($id)["name"];
        } else if($type == News::COLLECTION) {
            $params["parentType"] = "News";
            $name = @News::getById($id)["name"];
        }

        $params["parentName"] = @$name;

		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("index" , $params, true);
	    else
  			$controller->render( "index" , $params );
    }
}