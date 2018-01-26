<?php

class GetCommunexionAction extends CAction {
	public function run() { 
		$controller=$this->getController();
		$res = CO2::getCommunexionUser();
		Rest::json($res);
	}
}
