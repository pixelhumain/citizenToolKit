<?php
class ActivityListAction extends CAction
{
    public function run( $type=null, $id= null)
    {
	    $controller=$this->getController();
	    $params["activities"]=ActivityStream::activityHistory($id,$type);
		$params["contextType"]=$type;
		$params["contextId"]=$id;
		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("activityList", $params,true);
	    else
  			$controller->render( "activityList" , $params );
    }
    
    
}