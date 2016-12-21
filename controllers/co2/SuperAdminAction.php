<?php

include('protected/components/simple_html_dom.php');

class SuperAdminAction extends CAction
{
    public function run($action)
    {
        $controller = $this->getController();
        $params = array();
    	
    	if($action == "main")		{ $this->main(); 	      return; }
        if($action == "web")        { $this->web();           return; }
        if($action == "scanlinks")  { $this->scanLinks();     return; }

        if($action == "updateurlmetadata")  { $this->updateUrlMetaData($_POST);     return; }
        if($action == "deleteUrl")          { $this->deleteUrl($_POST);             return; }



        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("admin/main",$params,true);
        else 
            $controller->render("admin/main",$params);
    }


    private function main(){
    	$controller = $this->getController();
        $params = array();
    	$controller->renderPartial("admin/main", $params);
    }

    private function web(){
        $controller = $this->getController();

        $query = array('status' => "locked");
        $urlsLocked = PHDB::find("url", $query);
        // foreach ($urlsLocked as $key => $value) {
        //     $resAddToSet = PHDB::update( "url", array("_id" => new MongoId($key)), 
        //                                 array('$set' => array("status"=>"locked")));
        // }
        $params = array("urlsLocked"=>$urlsLocked);
    	$controller->renderPartial("admin/web", $params);
    }

    private function scanLinks(){
        echo "PAR SECURITÉ, MERCI D'ACTIVER CETTE FONCTION DANS LE CODE ;)";
        return;

        $url = "http://www.la-nouvelle-caledonie.com/liens-utiles/";
        $html = file_get_html($url);
        error_log("CRAWLING ".$url);
        $res = "- ";

        foreach($html->find("#matrix_1026082780") as $element) {
            $res .= "FOUND<br>";
            $n=0;
            foreach($element->find(".module-type-text p a") as $element2) {
                $n++;
                $url = mb_convert_encoding(@$element2->href, "HTML-ENTITIES", "UTF-8");
                
                $host = parse_url($url);
                $hostname = @$host["host"];

                $res .= $n." ".$url." == ".$hostname."<br>";
                
                $newSiteurl = array("url"           => @$url,
                                    "hostname"      => @$hostname,
                                    "title"         => "",
                                    "description"   => "",
                                    "tags"          => "",
                                    "categories"    => "",
                                    "status"        => "locked",
                                    "dateRef"       => new MongoDate(time()),
                                    "nbClick"       => 0,
                                    "typeSig"       => "url"
                                    );
                //error_log("INSERT-URL ".$url);
                //PHDB::insert("url", $newSiteurl);
            }
        }

        echo $res;
    }

    private function updateUrlMetaData($post){
        $resAddToSet = PHDB::update( "url", array("_id" => new MongoId($post["id"])), 
                              array('$set' => $post["values"]));

        $result = array("valid"=> $resAddToSet);
        Rest::json($result);
        Yii::app()->end();
    }
    private function deleteUrl($post){
        $res = PHDB::remove( "url", array("url" => $post["url"]) );

        $result = array("valid"=> $res);
        Rest::json($result);
        Yii::app()->end();
    }
}

?>