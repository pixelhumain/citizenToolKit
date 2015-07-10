<?php
class EditRoomAction extends CAction
{
    public function run( )
    {
        $controller=$this->getController();

        $listRoomTypes = Lists::getListByName("listRoomTypes");
        $tagsList =  Lists::getListByName("tags");
        $params = array(
            "listRoomTypes" => $listRoomTypes,
            "tagsList" => $tagsList
        );
		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("editRoomSV" , $params,true);
	    else
  			$controller->render( "editRoomSV" , $params );
    }
}