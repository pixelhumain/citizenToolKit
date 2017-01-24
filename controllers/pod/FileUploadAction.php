<?php
class FileUploadAction extends CAction
{
    public function run($itemId, $type, $resize = false, $edit=false, $contentId, $podId="",$image="")
    {
        $controller=$this->getController();
        $params = array();
		$params["type"] = $type;
		$params["itemId"] = $itemId;
		$params["resize"] = $resize;
		$params["contentId"] = $contentId;
		$params["podId"] = $podId;
		$params["editMode"] = $edit;
		$params["image"] = $image;
		if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial('fileupload', $params, true);
		else
			echo $controller->render("fileupload");
    }
}