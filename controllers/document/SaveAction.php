<?php
class SaveAction extends CAction {
	

	public function run() {
		return Rest::json( Document::save($_POST));
	}

}