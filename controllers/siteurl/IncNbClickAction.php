<?php
/**
* retreive dynamically 
*/
class IncNbClickAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        
        $result = array();
        $query = array();
        if(isset($_POST["url"])){
            $url = $_POST["url"];
        	$query = array("url"=>$url);
            $siteurl = PHDB::findOne("url", $query);

            if(isset($siteurl["_id"])){
                $id = $siteurl["_id"];
                $nbClick = isset($siteurl["nbClick"]) ? $siteurl["nbClick"] : 0;
                $nbClick++;
                $set = array("nbClick"=>$nbClick);
                $resUpdate = PHDB::update( "url", array("_id" => new MongoId($id)), 
                                          array('$set' => $set));

                $result = array("resUpdate"=>$resUpdate, "nbClick"=>$nbClick);
            }
        }

    	Rest::json($result);
        Yii::app()->end();
    }
}