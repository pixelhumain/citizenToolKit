<?php
class IndexAction extends CAction {

	public function run() {

		// echo 'TEST INDEX ACTION INTEROPERABILITY';

		$controller=$this->getController();
		$controller->layout = "//layouts/mainSearch";
		$controller->render( "index" );
	}
}

?>
