
<?php
class GetDataByUrlAction extends CAction
{
    public function run(){

        $controller = $this->getController();
        $params = array();
        try{
        	$params = json_decode(file_get_contents($_POST['url']), true);
        }catch (CTKException $e){
            $params["error"][] = $e->getMessage();
        }
    	return Rest::json($params);   
    }
}

?>