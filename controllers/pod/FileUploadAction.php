<?php
class FileUploadAction extends CAction
{
    public function run($itemId, $type, $resize = false, $edit=false, $contentId, $podId="")
    {
        $controller=$this->getController();
        $params = array();
		$params["type"] = $type;
		$params["itemId"] = $itemId;
		$params["resize"] = $resize;
		$params["contentId"] = $contentId;
		$params["podId"] = $podId;
		$params["editMode"] = $edit;
		if(Yii::app()->request->isAjaxRequest)
			echo $this->renderPartial('fileupload', $params, true);
		else
			$this->render("fileupload",$params);
    }
}