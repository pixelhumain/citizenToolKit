
<?php
class UpdateAction extends CAction
{
    public function run()
    {
    	$controller=$this->getController();
    	$result=News::update($_POST);
    	if(@$_GET["tpl"]=="co2"){
    		$params=array(
    			"news"=>array((string)$result["object"]["_id"]=>$result["object"]), 
    			"actionController"=>"save",
    			"canManageNews"=>true,
    			"canPostNews"=>true,
                "nbCol" => 1,
                "pair" => false);
			echo $controller->renderPartial("newsPartialCO2", $params,true);
    	}
		else
        	return Rest::json($result);
    }
}