<?php
class SaveAction extends CAction
{
    public function run($type=null, $id= null)
    {
    	$controller=$this->getController();
    	$result=News::save($_POST);
    	if(@$_GET["tpl"]=="co2"){
    		$params=array(
    			"news"=>array( (string)$result["id"]=>$result["object"]), 
    			"actionController"=>"save",
    			"canManageNews"=>true,
    			"canPostNews"=>true,
                "nbCol" => 1,
                "pair" => false);
			echo $controller->renderPartial("timeline-tree", $params,true);
    	}
		else
        	return Rest::json($result);
    }
}