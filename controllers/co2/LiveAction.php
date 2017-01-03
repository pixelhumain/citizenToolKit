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
    	
        $params = array("medias" => $medias );

        CO2Stat::incNbLoad("co2-live");

    	if(@$_POST['renderPartial'] == true)
    	echo $controller->renderPartial("liveStream", $params, true);
    	else
    	echo $controller->renderPartial("live", $params, true);
    }
}