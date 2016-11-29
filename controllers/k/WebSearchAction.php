<?php
/**
* retreive dynamically 
*/
class WebSearchAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        
        $query = array();
        if(isset($_POST["category"]))
        	$query = array("categories"=>array('$in' => array($_POST["category"])));

    	$siteurls = PHDB::find("siteurl", $query);
    	foreach ($siteurls as $key => $siteurl) {
    		$siteurls[$key]["typeSig"] = "siteurl";
    	}
    	$params = array("siteurls"=>$siteurls,
    					"search"=>@$_POST["search"],
    					"category"=>@$_POST["category"]);

    	echo $controller->renderPartial("webSearch", $params, true);
    }
}