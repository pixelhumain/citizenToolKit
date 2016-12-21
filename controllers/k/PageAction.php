<?php
/**
* retreive dynamically 
*/
class PageAction extends CAction
{
    public function run($type, $id) {
    	$controller=$this->getController();
        

        //$indexMin = isset($_POST['indexMin']) ? $_POST['indexMin'] : 0;
        //$indexMax = isset($_POST['indexMax']) ? $_POST['indexMax'] : 10;

        //$indexStep = $indexMax - $indexMin;
       
        //$query = array('srcMedia' => array('$in' => array("NCI", "NC1", "CALEDOSPHERE", "NCTV")));
    	//$medias = PHDB::findAndSortAndLimitAndIndex("media", $query, array("date"=>-1) , $indexStep, $indexMin);
    	
        $params = array("id" => @$id,
                        "type" => @$type,
                        "subdomain" => "page",
                        "mainTitle" => "Page perso",
                        "placeholderMainSearch" => "");

    	//if(@$_POST['renderPartial'] == true)
    	//echo $controller->renderPartial("liveStream", $params, true);
    	//else
    	echo $controller->renderPartial("page", $params, true);
    }
}