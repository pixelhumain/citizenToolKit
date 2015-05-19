<?php
class GetListByContentKeyAction extends CAction
{
    public function run($id, $contentkey)
    {
		$images = Document::getListDocumentsURLByContentKey($id, $contentkey, Document::DOC_TYPE_IMAGE);

		Rest::json($images);
    }
}