<?php
class DetailAction extends CAction
{
    public function run($id)
    {
	    $controller=$this->getController();
        $news = News::getById($id);
        $params=array("news" => $news,"contextParentType"=> $news["type"],"contextParentId"=> $news["id"]);
        $page = "detail";
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial($page,$params,true);
		else
  		$controller->render($page, $params);
    }
}