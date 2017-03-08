<?php
/**
* retreive dynamically 
*/
class SaveReferencementAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        
        $newRefValide = true;
        if(!@$_POST["url"]) 		$newRefValide = false;
        if(!@$_POST["hostname"]) 	$newRefValide = false;
        if(!@$_POST["title"]) 		$newRefValide = false;
        if(!@$_POST["description"]) $newRefValide = false;
		if(!@$_POST["keywords"]) 	$newRefValide = false;
		if(!@$_POST["categories"]) 	$newRefValide = false;

		$result = array("status"=> "error");

		$query = array("url"=>@$_POST["url"]);
        $siteurl = PHDB::findOne("url", $query);

        if(!isset($siteurl["_id"])){

			$address = @$_POST["address"] ? @$_POST["address"] : false;
			$geo = @$_POST["geo"] ? @$_POST["geo"] : false;
			$geoPosition = @$_POST["geoPosition"] ? @$_POST["geoPosition"] : false;

			$authorId = @$_POST["authorId"] ? @$_POST["authorId"] : "anonymous";
			$status = @$_POST["authorId"] ? "active" : "locked";

	    	$newSiteurl = array("url" 			=> @$_POST["url"],
		                        "hostname" 		=> @$_POST["hostname"],
		                        "title" 		=> @$_POST["title"],
		                        "description" 	=> @$_POST["description"],
		                        "tags" 			=> @$_POST["keywords"],
		                        "categories" 	=> @$_POST["categories"],
		                        "authorId" 		=> $authorId,
		                        "status" 		=> $status,
		                        "dateRef"		=> new MongoDate(time()),
		                        "nbClick"		=> 0,
		                        "typeSig"		=> "url"
	    						);

	    	if($address != false) 		$newSiteurl["address"] = $address;
	    	if($geo != false) 			$newSiteurl["geo"] = $geo;
			if($geoPosition != false) 	$newSiteurl["geoPosition"] = $geoPosition;

	    	if($newRefValide){
	    		PHDB::insert("url", $newSiteurl);
	    		$result = array("valid"=> $newRefValide);
	    	}else{
	    		$result = array("status"=> "URL_NOT_VALIDE");
	    	}

	    	
	    
	    }else{
	    	$result = array("status"=> "URL_EXISTS");
	    }


    	Rest::json($result);
		Yii::app()->end();
    }
}