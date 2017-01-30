<?php
class GetListByIdAction extends CAction {
	

	public function run($id, $type) {
		$result = Document::getWhere(array("id" => $id,
											"type" => $type));
		Rest::json($result);
	}

}