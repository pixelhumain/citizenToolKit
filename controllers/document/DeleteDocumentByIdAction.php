<?php
class DeleteDocumentByIdAction extends CAction {
	

	public function run($id) {
		return Rest::json( Document::removeDocumentById($id));
	}

}