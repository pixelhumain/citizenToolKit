<?php
require_once __DIR__ . "/../../../../pixelhumain/ph/vendor/facebook/autoload.php"; //include autoload from SDK folder

//import required class to the current scope
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRedirectLoginHelper;
class FacebookAction extends CAction
{
    public function run()
    {
       	$controller=$this->getController();
       	FacebookSession::setDefaultApplication(Yii::app()->params["facebook"]['idAPP'] ,
    											Yii::app()->params["facebook"]['secretAPP']);
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

		if ($sessionFB) //if we have the FB session	
		{ 
			
			try 
			{
				$user_profile = (new FacebookRequest($sessionFB, 'GET', '/me'))->execute()->getGraphObject(GraphUser::className());
				$list_groups = (new FacebookRequest($sessionFB, 'GET', '/me/groups'))->execute()->getGraphObject(GraphUser::className());

				$list_pages = (new FacebookRequest($sessionFB, 'GET', '/me/likes'))->execute()->getGraphObject(GraphUser::className());
				
				Yii::app()->session["FB_user_detail"] = $user_profile->asArray();
				Yii::app()->session["FB_user_groups"] = current($list_groups->asArray());
				$array_list_pages = current($list_pages->asArray());
				$list_pages = array();

				foreach($array_list_pages as $page)
		        {
		               $page_detail = (new FacebookRequest($sessionFB, 'GET', '/'.$page->id))->execute()->getGraphObject(GraphUser::className());
		               $list_pages[] = $page_detail; 
		        }
		        Yii::app()->session["FB_user_pages"] = $list_pages;

				$controller->render("facebook",array(
										"user_detail"=>Yii::app()->session["FB_user_detail"],
										"groups"=>Yii::app()->session["FB_user_groups"],
										"pages"=>Yii::app()->session["FB_user_pages"],
										"post"=>false
										));

			} 
			catch(FacebookRequestException $ex) 
			{
				die(" Error : " . $ex->getMessage());
			} 
			catch(\Exception $ex)
			{
				die(" Error : " . $ex->getMessage());
			}

			
		}
		else
		{
			$login_url = $helper->getLoginUrl(array( 'scope' => Yii::app()->params["facebook"]['required_scope']) );
			$controller->redirect($login_url);
		
		}
    }
}
?>