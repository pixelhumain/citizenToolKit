<?php

class GetByIdAction extends CAction
{
    public function run($id=null) {
		$controller=$this->getController();
		$organization = Organization::getById($id);
		Rest::json($organization);
    }
}