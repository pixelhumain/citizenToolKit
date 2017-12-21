<?php
/**
* retreive dynamically 
*/
class WebAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        
        CO2Stat::incNbLoad("co2-web");

        $cookiesFav = isset( Yii::app()->request->cookies['webFavorites'] ) && Yii::app()->request->cookies['webFavorites'] != "" ? 
		   			    		 explode(",", Yii::app()->request->cookies['webFavorites']->value) : array();
    	
    	$myWebFavorites = array();
    	foreach ($cookiesFav as $key => $urlId) { //var_dump($web); exit;
    		$url = PHDB::findOne(Url::COLLECTION,array("_id"=>new MongoId($urlId)));
    		$myWebFavorites[] = $url;
    	}
		//var_dump($myWebFavorites); exit;
		//$query = array('_id' => array('$in' => $mongoIds));
    	//$urls = PHDB::find("url", $query);

    	//var_dump($urls); exit;


    	$params = array("myWebFavorites"=>$myWebFavorites);
    	echo $controller->renderPartial("web", $params, true);
    }
}