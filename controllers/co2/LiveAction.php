<?php
/**
* retreive dynamically 
*/
class LiveAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        

        $indexMin = isset($_POST['indexMin']) ? $_POST['indexMin'] : 0;
        $indexMax = isset($_POST['indexMax']) ? $_POST['indexMax'] : 10;

        $indexStep = $indexMax - $indexMin;
       
        $query = array('srcMedia' => array('$in' => array("NCI", "NC1", "CALEDOSPHERE", "NCTV")));
    	$medias = PHDB::findAndSortAndLimitAndIndex("media", $query, array("date"=>-1) , $indexStep, $indexMin);
    	//$medias = array();
    	$params = array("medias" => $medias,
    					// "subdomain" => "live",
         //                "subdomainName" => "Media",
         //                "icon" => "newspaper-o", 
         //                "mainTitle" => "Toute l'actu <span class='letter-green'>des médias alternatifs</span>",
         //                "placeholderMainSearch" => "rechercher dans l'actualité ..."
                        );

    	if(@$_POST['renderPartial'] == true)
    	echo $controller->renderPartial("liveStream", $params, true);
    	else
    	echo $controller->renderPartial("live", $params, true);
    }
}