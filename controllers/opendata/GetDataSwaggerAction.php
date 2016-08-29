<?php
/**
 * Retrieve all the Countries 
 * @return [json] {value : "codeinsee", text : "the Text"}
 */
class GetDataSwaggerAction extends CAction
{
    public function run()
    {
        header('Access-Control-Allow-Origin: *');
        $docsJSON = file_get_contents("../../modules/api/data/api.json", FILE_USE_INCLUDE_PATH);
        $docs = json_decode($docsJSON,true);
        $docs["host"] = $_SERVER['HTTP_HOST'];
        $docs["basePath"] = Yii::app()->baseUrl."/api" ;
        Rest::json($docs); 
        //Yii::app()->end();
        //echo file_get_contents("../../modules/api/data/docs.json");
    }
}