<?php

class CheckAction extends CAction
{
	 /**
	 * TODO bouboule : La PHPDOC
	 */
    public function run() {
    	$res=Slug::check($_POST["slug"],@$_POST["type"],@$_POST["id"]);

    	$paramsApp = CO2::getThemeParams();
    	Rest::json(array("result"=>$res, "domaineName"=>@$paramsApp["domaineName"]));
    }
}