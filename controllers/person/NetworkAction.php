<?php
/*require_once __DIR__ . "/../../../../pixelhumain/ph/vendor/facebook/autoload.php"; //include autoload from SDK folder

//import required class to the current scope
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRedirectLoginHelper;*/
class NetworkAction extends CAction
{
	

    public function run()
    {
    	$controller=$this->getController();
    	/*$required_scope = 'public_profile, publish_actions, read_custom_friendlists, user_groups, user_likes, publish_pages';
    	
    	FacebookSession::setDefaultApplication("" , "");
		$helper = new FacebookRedirectLoginHelper(Yii::app()->getRequest()->getBaseUrl(true).'/communecter/person/facebook');
		try 
		{
			$sessionFB = $helper->getSessionFromRedirect();	
	  	} 
		catch(FacebookRequestException $ex) 
		{
			die(" Error : " . $ex->getMessage());
		} 
		catch(\Exception $ex)
		{
			die(" Error : " . $ex->getMessage());
		}
		$login_url = $helper->getLoginUrl(array('scope' => $required_scope) );
		$controller->render("network",array("login_url_FB"=>$login_url));*/
		$controller->render("network");
    }
}