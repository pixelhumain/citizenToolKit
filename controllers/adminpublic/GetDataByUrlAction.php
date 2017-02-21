
<?php
class GetDataByUrlAction extends CAction
{
    public function run(){

        $controller = $this->getController();
        $params = array();
        try{
        	//$params["data"] = SIG::getUrl($_POST['url']);
            $params["data"] = file_get_contents($_POST['url']);
        }catch (CTKException $e){
            $params["error"][] = $e->getMessage();
        }
    	return Rest::json($params);   
    }
}

?>