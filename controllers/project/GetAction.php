<?php

class GetAction extends CAction
{
    public function run($id=null) {
		$controller=$this->getController();
		$organization = Project::getById($id);
		Rest::json($organization);
    }
}