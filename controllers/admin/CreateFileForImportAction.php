<?php

class CreateFileForImportAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
       

        $params = Import::previewData($_POST);

        $paramsCollection = array("_id"=>new MongoId($_POST['idCollection']));
        $fieldsCollection = array("key");
        $infoCollection = Import::getMicroFormats($paramsCollection, $fieldsCollection);
        
        if($infoCollection[$_POST['idCollection']]["key"] == "Organizations")
        	$collection = Organization::COLLECTION;

        $paramsForJson = array("jsonImport"=> $params["jsonImport"],
                            "jsonError"=> $params["jsonError"],
                            "nameFile" => $_POST["nameFile"],
                            "collection" => $collection);
        if($params["result"] == true)
        	Import::createOrUpdateJsonForImport($paramsForJson);


        return Rest::json($params);
    }
}

?>