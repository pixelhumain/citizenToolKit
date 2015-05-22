<?php
class LatestAction extends CAction
{
    public function run($type=null, $id= null,$count=1)
    {
        $controller=$this->getController();
        $where = array("created"=>array('$exists'=>1),"text"=>array('$exists'=>1) ) ;
        if(isset($type))
        	$where["type"] = $type;
        if(isset($id))
        	$where["id"] = $id;
		$news = News::getWhereSortLimit( $where, array("created"=>-1) ,$count);

		if(Yii::app()->request->isAjaxRequest){
	        if(!empty($news))
	        	echo $controller->renderPartial("one" , array( "news"=>$news, "userCP"=>Yii::app()->session['userCP'] ),true);
	    } else
  			$controller->render( "one" , array( "news"=>$news ) ); 		

    }
}