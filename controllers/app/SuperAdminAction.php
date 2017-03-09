<?php

include('protected/components/simple_html_dom.php');

class SuperAdminAction extends CAction
{
    public function run($action)
    {
        $controller = $this->getController();
        $params = array();
    	
        if(Role::isSuperAdmin(Role::getRolesUserId(@Yii::app()->session["userId"]) ) ) {
        	if($action == "main")		{ $this->main(); 	      return; }
            if($action == "web")        { $this->web();           return; }
            if($action == "live")       { $this->live();          return; }
            if($action == "power")      { $this->power();         return; }
            if($action == "scanlinks")  { $this->scanLinks();     return; }
            if($action == "deleteUrl")  { $this->deleteUrl($_POST); return; }
        }

        if($action == "updateurlmetadata")  { $this->updateUrlMetaData($_POST);     return; }

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

    private function live(){
        $controller = $this->getController();
        $params = array();
        $controller->renderPartial("admin/live", $params);
    }

    private function power(){
        $controller = $this->getController();
        $params = array();
        $controller->renderPartial("admin/power", $params);
    }

    private function web(){
        $controller = $this->getController();


        $urlsAll = PHDB::find("url");
        $urlsLocked         = PHDB::find("url", array('status' => "locked"));        
        $urlsUnreachable    = PHDB::find("url", array('status' => "unreachable"));        
        $urlsUncomplet      = PHDB::find("url", array('status' => "uncomplet"));
        $urlsValidated      = PHDB::find("url", array('status' => "validated"));
        $urlsUncategorized  = PHDB::find("url", array('categories' => "") );        
        $urlsNoFavicon      = PHDB::find("url", array('favicon' => array('$exists' => false) ) );
        $urlsEdited         = PHDB::find("urlEdit");        
        
        // foreach ($urlsLocked as $key => $value) {
        //     $resAddToSet = PHDB::update( "url", array("_id" => new MongoId($key)), 
        //                                 array('$set' => array("status"=>"locked")));
        // }
        $params = array("urlsAllNb"=>sizeof($urlsAll),
                        "urlsLockedNb"=>sizeof($urlsLocked),
                        "urlsUnreachableNb"=>sizeof($urlsUnreachable),
                        "urlsUncompletNb"=>sizeof($urlsUncomplet),
                        "urlsValidatedNb"=>sizeof($urlsValidated),
                        
                        "urlsUncategorizedNb"=>sizeof($urlsUncategorized),
                        "urlsEditedNb"=>sizeof($urlsEdited),

                        "urlsLocked"=>$urlsLocked,
                        "urlsNoFavicon"=>$urlsNoFavicon,
                        );
    	$controller->renderPartial("admin/web", $params);
    }

    private function scanLinks(){
        echo "PAR SECURITÉ, MERCI D'ACTIVER CETTE FONCTION DANS LE CODE ;)";
        return;

        //$url = "http://www.la-nouvelle-caledonie.com/liens-utiles/";
        $url = "http://caledoweb.com/?page_id=14";
        //$url = "/home/tango/Bureau/Skazy - Nouvelle-Calédonie.html";
        $html = file_get_html($url);
        error_log("CRAWLING ".$url);
        $res = "- ";
        //$links = $html->find(".entry-content");
        echo "ok loaded";
        //var_dump($links);
        //exit;
        $n=0;
        foreach($html->find(".entry-content .wp-caption a") as $element) {
            //$res .= "FOUND<br>";
            // foreach($element->find(".module-type-text p a") as $element2) {
            $url = mb_convert_encoding(@$element->href, "HTML-ENTITIES", "UTF-8");
            //$res .= "search ".$url."<br>";

            //$site = file_get_html($url);
            //echo "site : ".$url.$site; //exit;
            //$thisSiteUrl = "ddd";
            //foreach($site->find(".see-more a") as $element2)
            //    $thisSiteUrl = mb_convert_encoding($element2->href, "HTML-ENTITIES", "UTF-8");
            //$site->find(".see-more a");    
           
            //$res .= "scan : ".$thisSiteUrl."<br>";

            $n++;
            // $url = $thisSiteUrl; //mb_convert_encoding(@$thisSiteUrl->href, "HTML-ENTITIES", "UTF-8");
                
            $host = parse_url($url);
            $hostname = @$host["host"];

            if(strpos($url, "caledoweb")==0){
                $newSiteurl = array("url"           => @$url,
                                    "hostname"      => @$hostname,
                                    "title"         => "",
                                    "description"   => "",
                                    "tags"          => "",
                                    "categories"    => array("Logement", "Entreprises"),
                                    "status"        => "locked",
                                    "dateRef"       => new MongoDate(time()),
                                    "nbClick"       => 0,
                                    "typeSig"       => "url"
                                    );
                $res .= $n." ".$url." == ".$hostname." - ".strpos($url, "caledoweb")."<br>";
                //error_log("INSERT-URL ".$url);
                PHDB::insert("url", $newSiteurl);
            }
        }

        echo $res;
    }

    

    private function updateUrlMetaData($post){
        //un super admin enregistre une modification dans la collection url
        if(Role::isSuperAdmin(Role::getRolesUserId(@Yii::app()->session["userId"]) ) ) {
            $res = PHDB::update( "url", array("_id" => new MongoId($post["id"])), 
                                  array('$set' => $post["values"]));
            PHDB::remove( "urlEdit", array("url" => @$post["values"]["url"]) );
        }else{
            //une demande de modification (non-admin) est enregistré dans la collection urlEdit
            //et doit être validée par un admin pour être appliqué dans la collection url

            //on vérifie si l'url a déjà été édité
            $urledit      = PHDB::find("urlEdit", array("_id" => new MongoId($post["id"]) ) );
            if(!empty($urledit)){
                $res = PHDB::update( "urlEdit", array("_id" => new MongoId($post["id"])), 
                                    array('$set' => $post["values"]));
            }else{
                $values = $post["values"];
                $values["_id"] = new MongoId($post["id"]);
                $res = PHDB::insert( "urlEdit", $values);
            }

            
        }

        $result = array("valid"=> $res);
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