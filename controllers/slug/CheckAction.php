<?php

class CheckAction extends CAction
{
	 /**
	 * TODO bouboule : La PHPDOC
	 */
    public function run() {
    	$res=Slug::check($_POST["slug"],$_POST["type"],$_POST["id"]);
    	Rest::json(array("result"=>$res));
    }
}