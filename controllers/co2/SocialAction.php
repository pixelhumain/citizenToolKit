<?php
/**
* retreive dynamically 
*/
class SocialAction extends CAction
{
    public function run($type=null) {
    	$controller=$this->getController();
        

        //$indexMin = isset($_POST['indexMin']) ? $_POST['indexMin'] : 0;
        //$indexMax = isset($_POST['indexMax']) ? $_POST['indexMax'] : 10;

        //$indexStep = $indexMax - $indexMin;
       
        //$query = array('srcMedia' => array('$in' => array("NCI", "NC1", "CALEDOSPHERE", "NCTV")));
    	//$medias = PHDB::findAndSortAndLimitAndIndex("media", $query, array("date"=>-1) , $indexStep, $indexMin);
    	
        $params = array("type" => @$type,
                        "subdomain" => "social",
                        "subdomainName" => "network",
                        "icon" => "user-circle",
                        "mainTitle" => "Le réseau social <span class='letter-green'>à effet de serre positif</span>",
                        "placeholderMainSearch" => "Rechercher parmis les membres du réseau Communecter");

    	//if(@$_POST['renderPartial'] == true)
    	//echo $controller->renderPartial("liveStream", $params, true);
    	//else
    	echo $controller->renderPartial("social", $params, true);
    }
}