<?php
require_once __DIR__ . "/../../../../pixelhumain/ph/vendor/facebook/autoload.php"; //include autoload from SDK folder

//import required class to the current scope
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRedirectLoginHelper;
class PostFacebookAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();

        if(isset($_POST["message"]))
        {
  			Yii::app()->session["message"] = $_POST["message"];
  			Yii::app()->session["profils"] = $_POST["profils"];
  		}

        FacebookSession::setDefaultApplication(Yii::app()->params["facebook"]['idAPP'] ,
    											Yii::app()->params["facebook"]['secretAPP']);

		$helper = new FacebookRedirectLoginHelper(Yii::app()->getRequest()->getBaseUrl(true).'/communecter/person/postfacebook');
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
				foreach (Yii::app()->session["profils"] as $value) 
				{
					$request = new FacebookRequest($sessionFB,'POST',
												'/'.$value.'/feed', 
												array ('message' => Yii::app()->session["message"] ,
														'link' => 'http://www.pixelhumain.com/',));
			  		$response = $request->execute();
					$graphObject = $response->getGraphObject();
				}

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
										"post"=>true
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
			//$controller->render("network",array("login_url_FB"=>$login_url));
			return Rest::json(array('url'=> $login_url));
			//$controller->redirect($login_url);
			//header('Location: '.$login_url);
		
		}
    }
}

?>