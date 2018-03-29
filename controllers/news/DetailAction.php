<?php
class DetailAction extends CAction
{
    public function run($id)
    {
	    $controller=$this->getController();
        $news = News::getById($id);
        if (@$news["target"]){
	        $targetId=$news["target"]["id"];
	        $targetType=$news["target"]["type"];
        }else{
	        $targetId=$news["author"]["id"];
	        $targetType=Person::COLLECTION;
        }
        $params=array("news" => array($news),"contextParentType"=> $targetType,"contextParentId"=> $targetId);
        $page = "timeline-tree";
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial($page,$params,true);
		else
  		$controller->render($page, $params);
    }
}