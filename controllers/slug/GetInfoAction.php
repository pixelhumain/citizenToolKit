<?php

class GetInfoAction extends CAction
{
	 /**
	 * TODO bouboule : La PHPDOC
	 */
    public function run($key=null) {
    	$res=Slug::getBySlug($key);
    	if(!empty($res))
    		Rest::json(array("result"=>true,"contextId"=>$res["id"],"contextType"=>$res["type"]));
    	else
    		Rest::json(array("result"=>false));
    }
}