<?php

class NewsAction extends CAction
{
	/**
	 * Still used ?
	 */
    public function run() {
    	$controller=$this->getController();
    	$news = News2::getWhere( array( "type" => Organization::COLLECTION , "id" => $id) );
	  	$controller->render("news",array("news"=>$news));
    }

}