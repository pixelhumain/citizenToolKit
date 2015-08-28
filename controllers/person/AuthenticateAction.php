<?php
class AuthenticateAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $email = $_POST["email"];
  		
  		$publicPage = false;
  		if (! empty(Yii::app()->session["requestedUrl"])) {
  			$pageArray = $this->getControllerAndActionFromUrl(Yii::app()->session["requestedUrl"]);
  			$publicPage = @$controller->pages[$pageArray["controllerId"]][$pageArray["actionId"]]["public"];
  		} 

        $res = Person::login( $email , $_POST["pwd"], $publicPage); 
        if( isset( $_POST["app"] ) )
			$res = array_merge($res, Citoyen::applicationRegistered($_POST["app"],$email));

        Rest::json($res);
        Yii::app()->end();
    }
    
	
	public function getControllerAndActionFromUrl($url) {
    $res = array("controllerId" => "", "actionId" => "");
    if ($url) {
  		$controller = $this->getController();
  		$res = array();
  		$url2 = str_replace(Yii::app()->baseUrl ."/".$controller->moduleId."/", "", $url);
  		
      if (substr_count($url2, '/') == 2) {
        list($controller,$action) = explode("/", $url2);
  		  $res["controllerId"] = $controller;
  		  $res["actionId"] = $action;
      }
    }
    
		return $res;
    }
}