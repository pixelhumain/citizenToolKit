<?php
class AuthenticateAction extends CAction {
    
    public function run() {
		$controller=$this->getController();
		$email = $_POST["email"];

		$res = Person::login( $email , $_POST["pwd"], false); 

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