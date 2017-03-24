<?php
/**
* retreive dynamically 
*/
class CheckUrlExistsAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        
        $result = array("status"=> "error");

		$query = array("url"=>@$_POST["url"]);
        $siteurl = PHDB::findOne("url", $query);

        if(!isset($siteurl["url"])){
			$query = array("url"=>@$_POST["url"]."/");
	        $siteurl = PHDB::findOne("url", $query);

	        if(!isset($siteurl["url"])){
				$result = array("status"=> "URL_NOT_EXISTS");
		    }else{
		    	$result = array("status"=> "URL_EXISTS");
		    }
	    }else{
	    	$result = array("status"=> "URL_EXISTS");
	    }
	    
    	Rest::json($result);
		Yii::app()->end();
    }
}