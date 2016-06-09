<?php
class ActivityListAction extends CAction
{
    public function run( $type=null, $id= null)
    {
	    $controller=$this->getController();
	    $params["activities"]=ActivityStream::activityHistory($id,$type);
		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("activitylist", $params,true);
	    else
  			$controller->render( "activitylist" , $params );
    }
    
    
}