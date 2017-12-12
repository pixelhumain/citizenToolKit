<?php

class GetInfoAction extends CAction
{
	 /**
	 * TODO bouboule : La PHPDOC
	 */
    public function run($key=null) {
        $clearSlug = explode(".", $key); //remove les param GET (#myslug?tpl=kkchose)
        $clearSlug = $clearSlug[0];

    	$res=Slug::getBySlug($clearSlug);
    	if(!empty($res))
    		Rest::json(array("result"=>true,"contextId"=>$res["id"],"contextType"=>$res["type"]));
    	else
    		Rest::json(array("result"=>false, "res" => $res, "clearSlug" => $clearSlug));
    }
}