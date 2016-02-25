
<?php
class GetDataByUrlAction extends CAction
{
    public function run(){

        $controller = $this->getController();
        $params = array();
        try{
        	$params["data"] = Import::getDataByUrl($_POST['url']);
        }catch (CTKException $e){
            $params["error"][] = $e->getMessage();
        }
        

    	return Rest::json($params);   
    }
}

?>