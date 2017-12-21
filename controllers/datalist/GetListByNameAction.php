<?php
class GetListByNameAction extends CAction
{
    public function run( $name )
    {
        if ($name) 
			$list = Lists::getListByName($name);
		Rest::json(array("result"=>true, "list"=>$list));
    }
}