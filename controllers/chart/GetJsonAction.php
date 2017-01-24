<?php
/**
 * Retrieve all the Countries 
 * @return [json] {value : "codeinsee", text : "the Text"}
 */
class GetJsonAction extends CAction
{
    public function run()
    {
	    $name=$_POST["json"];
        //header('Access-Control-Allow-Origin: *');
        $docsJSON = file_get_contents("../../modules/communecter/views/chart/json/".$name.".json", FILE_USE_INCLUDE_PATH);
        //echo $docsJSON;
        $docs = json_decode($docsJSON,true);       
        //print_r($docs);
      //  return $docs;
	  	Rest::json($docs); 
    }
}