<?php

class CheckAction extends CAction
{
	 /**
	 * TODO bouboule : La PHPDOC
	 */
    public function run() {
    	$res=Slug::check($_POST);
    	Rest::json(array("result"=>$res));
    }
}