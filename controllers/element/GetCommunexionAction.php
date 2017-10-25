<?php

class GetCommunexionAction extends CAction {
	public function run() { 
		$controller=$this->getController();
		$res = CO2::getCommunexionCookies();
		Rest::json($res);
	}
}
