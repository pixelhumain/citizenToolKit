<?php
class IndexAction extends CAction
{
    public function run($type=null, $id= null)
    {
        $controller=$this->getController();
        //mongo search cmd : db.news.find({created:{'$exists':1}})	
        $where = array("created"=>array('$exists'=>1),"text"=>array('$exists'=>1) ) ;
        if(isset($type))
        	$where["type"] = $type;
        if(isset($id))
        	$where["id"] = $id;
        //var_dump($where);
		$news = News::getWhereSortLimit( $where, array("created"=>-1) ,30);

		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("index" , array( "news"=>$news, "userCP"=>Yii::app()->session['userCP'] ),true);
	    else
  			$controller->render( "index" , array( "news"=>$news, "userCP"=>Yii::app()->session['userCP'] ) );
    }
}