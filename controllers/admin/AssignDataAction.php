<?php

class AssignDataAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();

        if($_POST['typeFile'] == "json" || $_POST['typeFile'] == "js" || $_POST['typeFile'] == "geojson")
        	$params = Import::parsingJSON2($_POST);
        else if($_POST['typeFile'] == "csv")
            //$params = Import::belgique($_POST);
        	$params = Import::parsingCSV2($_POST);
        	

        return Rest::json($params);
    }
}

?>